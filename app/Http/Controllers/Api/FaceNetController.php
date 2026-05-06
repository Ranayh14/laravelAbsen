<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;

class FaceNetController extends Controller
{
    use \App\Traits\ImageOptimizer;

    public function index(Request $request)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized).'], 403);
            }

            // Tampilkan user yang sudah memiliki face embedding
            // Sertakan status landmark juga (tidak sertakan data besar)
            $users = User::select('id', 'nama', 'email', 'nim', 'role', 'face_embedding_updated')
                ->selectRaw('CASE WHEN face_embedding IS NOT NULL THEN 1 ELSE 0 END AS has_embedding')
                ->selectRaw('CASE WHEN face_landmarks IS NOT NULL THEN 1 ELSE 0 END AS has_landmarks')
                ->whereNotNull('face_embedding')
                ->get();

            return response()->json([
                'ok'      => true,
                'message' => 'Berhasil mengambil data pengguna dengan wajah terdaftar.',
                'data'    => $users
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Gagal mengambil data wajah.',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['ok' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
            }

            if ($request->user()->role !== 'admin' && $user->id !== $request->user()->id) {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized).'], 403);
            }

            return response()->json([
                'ok' => true, 
                'message' => 'Berhasil mengambil status wajah.',
                'has_embedding' => $user->face_embedding !== null,
                'updated_at' => $user->face_embedding_updated
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal mengambil status wajah.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['ok' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
            }

            if ($request->user()->role !== 'admin' && $user->id !== $request->user()->id) {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized).'], 403);
            }

            $user->face_embedding = null;
            $user->face_embedding_updated = null;
            $user->save();

            return response()->json([
                'ok' => true, 
                'message' => 'Data wajah berhasil dihapus.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal menghapus data wajah.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function verify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|string', // base64
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Validasi gagal.', 
                    'errors' => $validator->errors()
                ], 400);
            }
            
            $user = User::findOrFail($request->user_id);
            
            $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $request->image);
            $imageName = 'temp_face_' . time() . '.jpg';
            $imagePath = storage_path('app/private/' . $imageName);
            file_put_contents($imagePath, base64_decode($base64));
            
            $facenetCli = base_path('scripts/facenet_cli.py');
            
            if (!$user->face_embedding) {
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                return response()->json(['ok' => false, 'message' => 'Pengguna belum mendaftarkan wajah.']);
            }
            
            $process = new Process(['python', $facenetCli, '--verify', '--image', $imagePath, '--embedding', $user->face_embedding]);
            $process->run();
            
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            if (!$process->isSuccessful()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Terjadi kesalahan pada sistem verifikasi wajah.',
                    'error' => $process->getErrorOutput()
                ], 500);
            }
            
            $output = json_decode($process->getOutput(), true);
            
            return response()->json([
                'ok' => true,
                'match' => $output['match'] ?? false,
                'confidence' => $output['confidence'] ?? 0,
                'message' => 'Verifikasi selesai.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal melakukan verifikasi wajah.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function registerFace(Request $request) 
    {
        try {
            $validator = Validator::make($request->all(), [
                'image'     => 'required|string',  // base64 gambar
                'landmarks' => 'nullable|string',  // JSON 68 titik landmark (opsional)
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok'     => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = $request->user();

            // Simpan gambar sebagai file (bukan base64 di DB)
            // Kompres gambar referensi wajah agar tidak memberatkan server
            $imageName = 'face_' . $user->id . '_' . time() . '.jpg';
            $savedFilename = $this->optimizeAndSaveBase64($request->image, 'users', $imageName, 300, 70);
            
            if (!$savedFilename) {
                return response()->json(['ok' => false, 'message' => 'Gagal memproses gambar wajah.'], 500);
            }

            $facenetCli = base_path('scripts/facenet_cli.py');
            $imagePath  = storage_path('app/public/users/' . $savedFilename);
            $process    = new Process(['python', $facenetCli, '--embed', '--image', $imagePath]);
            $process->run();

            // KITA TETAP SIMPAN GAMBAR REFERENSI (tapi versi kompresi) agar admin bisa melihat bukti.
            // User lama mungkin menyimpan file di folder berbeda, pastikan konsisten.
            $storagePath = 'public/users/' . $savedFilename;

            // Jangan hapus file asli jika admin masih ingin melihatnya sebagai bukti.
            // (Sebelumnya file dihapus setelah embedding dibuat, sekarang kita biarkan versi kompresi tersimpan)
            /*
            if (file_exists($imagePath)) {
                \Illuminate\Support\Facades\Storage::delete($storagePath);
            }
            */

            if (!$process->isSuccessful()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Gagal membuat Face Embedding.',
                ], 500);
            }

            $output = json_decode($process->getOutput(), true);
            if (isset($output['embedding'])) {
                // Hapus foto lama jika ada
                if ($user->foto_base64 && \Illuminate\Support\Facades\Storage::exists('public/users/' . $user->foto_base64)) {
                    \Illuminate\Support\Facades\Storage::delete('public/users/' . $user->foto_base64);
                }

                // Simpan embedding + nama file foto (bukan base64 string)
                $user->face_embedding         = json_encode($output['embedding']);
                $user->foto_base64            = $savedFilename; // Simpan nama file hasil kompresi
                $user->face_embedding_updated = now();

                // Simpan landmarks jika dikirim dari frontend
                if ($request->landmarks) {
                    $user->face_landmarks = $request->landmarks;
                }

                $user->save();

                return response()->json(['ok' => true, 'message' => 'Wajah berhasil didaftarkan.']);
            }

            return response()->json(['ok' => false, 'message' => 'Tidak ada wajah terdeteksi pada gambar.']);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Gagal mendaftarkan wajah.',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }
}
