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
            ], [
                'image.required' => 'Gambar wajah wajib dikirimkan dalam format base64.',
                'user_id.required' => 'ID Pengguna wajib disertakan.',
                'user_id.exists' => 'Pengguna tidak ditemukan dalam sistem.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Validasi gagal.', 
                    'errors' => $validator->errors()
                ], 400);
            }
            
            $user = User::findOrFail($request->user_id);
            if (!$user->face_embedding) {
                return response()->json(['ok' => false, 'message' => 'Pengguna ini belum mendaftarkan wajah.'], 400);
            }

            // Simpan gambar sementara (Optimized 300px)
            $imageName = 'temp_verify_' . time() . '.jpg';
            $savedFilename = $this->optimizeAndSaveBase64($request->image, 'temp', $imageName, 300, 70);
            
            if (!$savedFilename) {
                return response()->json(['ok' => false, 'message' => 'Gagal memproses gambar.'], 500);
            }
            
            $imagePath = storage_path('app/public/temp/' . $savedFilename);
            
            $facenetCli = base_path('scripts/facenet_cli.py');
            $pythonPath = 'C:\\Python313\\python.exe';
            $sitePackages = 'C:\\Python313\\Lib\\site-packages';
            
            $cmdPython = file_exists($pythonPath) ? $pythonPath : 'python';
            $jsonArgs = json_encode([
                'action' => 'recognize_face',
                'image' => $imagePath,
                'threshold' => 0.5
            ]);

            $process = new Process([$cmdPython, $facenetCli, $jsonArgs]);
            $process->setEnv([
                'PYTHONPATH' => $sitePackages . ';C:\\Users\\Rana\\AppData\\Roaming\\Python\\Python313\\site-packages;' . base_path('scripts') . ';' . base_path('scripts/facenet-master/src'),
                'PATH' => 'C:\\Python313\\;' . getenv('PATH'),
                'USERNAME' => 'Rana',
                'USER' => 'Rana',
                'SystemRoot' => 'C:\\Windows'
            ]);
            
            $process->run();
            $outputStr = $process->getOutput();
            
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            if (!$process->isSuccessful()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Gagal verifikasi.',
                    'debug_error' => $process->getErrorOutput()
                ], 500);
            }
            
            $output = json_decode($outputStr, true);
            if (!$output || !isset($output['success'])) {
                return response()->json(['ok' => false, 'message' => 'Respon AI tidak valid.'], 500);
            }

            if ($output['success']) {
                $matchData = $output['data'];
                $isMatch = (int)$matchData['user_id'] === (int)$request->user_id;

                return response()->json([
                    'ok' => true,
                    'match' => $isMatch,
                    'confidence' => $matchData['confidence'] ?? 0,
                    'distance' => $matchData['distance'] ?? 0,
                    'message' => $isMatch ? 'Verifikasi wajah berhasil.' : 'Wajah tidak cocok dengan ID yang diminta.'
                ]);
            }
            
            return response()->json([
                'ok' => false,
                'message' => 'Wajah tidak dikenali.',
                'debug_error' => $output['error'] ?? 'No match found'
            ], 400);

        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Terjadi kesalahan sistem.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function identify(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|string', // base64
            ], [
                'image.required' => 'Gambar wajah wajib dikirimkan dalam format base64.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Validasi gagal.', 
                    'errors' => $validator->errors()
                ], 400);
            }
            
            // Simpan gambar sementara (Optimized 300px)
            $imageName = 'temp_id_' . time() . '.jpg';
            $savedFilename = $this->optimizeAndSaveBase64($request->image, 'temp', $imageName, 300, 70);
            
            if (!$savedFilename) {
                return response()->json(['ok' => false, 'message' => 'Gagal memproses gambar.'], 500);
            }
            
            $imagePath = storage_path('app/public/temp/' . $savedFilename);
            
            if (!file_exists($imagePath)) {
                return response()->json(['ok' => false, 'message' => 'Gagal menyimpan file gambar sementara.'], 500);
            }
            
            $facenetCli = base_path('scripts/facenet_cli.py');
            $pythonPath = 'C:\\Python313\\python.exe';
            $sitePackages = 'C:\\Python313\\Lib\\site-packages';
            
            $cmdPython = file_exists($pythonPath) ? $pythonPath : 'python';
            $jsonArgs = json_encode([
                'action' => 'recognize_face',
                'image' => $imagePath,
                'threshold' => 0.5
            ]);

            $process = new Process([$cmdPython, $facenetCli, $jsonArgs]);
            $process->setEnv([
                'PYTHONPATH' => $sitePackages . ';C:\\Users\\Rana\\AppData\\Roaming\\Python\\Python313\\site-packages;' . base_path('scripts') . ';' . base_path('scripts/facenet-master/src'),
                'PATH' => 'C:\\Python313\\;' . getenv('PATH'),
                'USERNAME' => 'Rana',
                'USER' => 'Rana',
                'SystemRoot' => 'C:\\Windows'
            ]);
            
            $process->run();
            $outputStr = $process->getOutput();
            
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            if (!$process->isSuccessful()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Gagal menjalankan identifikasi.',
                    'debug_error' => $process->getErrorOutput()
                ], 500);
            }
            
            $output = json_decode($outputStr, true);
            if (!$output) {
                return response()->json(['ok' => false, 'message' => 'Respon AI tidak valid.'], 500);
            }

            if (isset($output['success']) && $output['success']) {
                $matchData = $output['data'];
                $user = User::find($matchData['user_id']);

                if (!$user) {
                    return response()->json(['ok' => false, 'message' => 'User tidak ditemukan.'], 404);
                }

                return response()->json([
                    'ok' => true,
                    'message' => 'Wajah berhasil diidentifikasi.',
                    'user' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'email' => $user->email,
                        'startup' => $user->startup
                    ],
                    'confidence' => $matchData['confidence'] ?? 0,
                    'distance' => $matchData['distance'] ?? 0
                ]);
            }
            
            return response()->json([
                'ok' => false,
                'message' => 'Wajah tidak dikenali atau belum terdaftar.',
                'debug_error' => $output['error'] ?? 'No match found'
            ], 404);

        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Terjadi kesalahan sistem.', 
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
            ], [
                'image.required' => 'Gambar wajah wajib disertakan dalam format base64.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok'     => false,
                    'message' => 'Validasi gagal. Silakan periksa kembali input Anda.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = $request->user();

            // Simpan gambar sebagai file
            $imageName = 'face_' . $user->id . '_' . time() . '.jpg';
            $savedFilename = $this->optimizeAndSaveBase64($request->image, 'users', $imageName, 300, 70);
            
            if (!$savedFilename) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Gagal memproses atau menyimpan gambar wajah.'
                ], 500);
            }

            $facenetCli = base_path('scripts/facenet_cli.py');
            $imagePath  = storage_path('app/public/users/' . $savedFilename);
            $pythonPath = 'C:\\Python313\\python.exe';
            $sitePackages = 'C:\\Python313\\Lib\\site-packages';
            
            // Coba gunakan path absolut, jika tidak ada baru gunakan 'python' biasa
            $cmdPython = file_exists($pythonPath) ? $pythonPath : 'python';

            // Format JSON untuk CLI (Embedding)
            $jsonArgs = json_encode([
                'action' => 'generate_embedding',
                'image' => $imagePath
            ]);
            
            // Generate Embedding menggunakan Python
            $process    = new Process([$cmdPython, $facenetCli, $jsonArgs]);
            
            $process->setEnv([
                'PYTHONPATH' => $sitePackages . ';C:\\Users\\Rana\\AppData\\Roaming\\Python\\Python313\\site-packages;' . base_path('scripts') . ';' . base_path('scripts/facenet-master/src'),
                'PATH' => 'C:\\Python313\\;' . getenv('PATH'),
                'SystemRoot' => getenv('SystemRoot') ?: 'C:\\Windows',
                'SystemDrive' => getenv('SystemDrive') ?: 'C:',
                'USERPROFILE' => 'C:\\Users\\Rana',
                'USERNAME' => 'Rana',
                'USER' => 'Rana',
                'HOME' => 'C:\\Users\\Rana',
                'APPDATA' => 'C:\\Users\\Rana\\AppData\\Roaming',
                'LOCALAPPDATA' => 'C:\\Users\\Rana\\AppData\\Local',
                'TEMP' => getenv('TEMP'),
                'TMP' => getenv('TMP')
            ]);

            $process->run();

            $outputStr = $process->getOutput();
            $errorStr = $process->getErrorOutput();
            $exitCode = $process->getExitCode();

            if (!$process->isSuccessful()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Gagal membuat Face Embedding. Pastikan wajah terlihat jelas.',
                    'debug_error' => $errorStr ?: 'Gagal menjalankan process Python.',
                    'exit_code' => $exitCode,
                    'raw_output' => $outputStr
                ], 500);
            }

            $output = json_decode($outputStr, true);
            
            if (isset($output['success']) && $output['success'] && isset($output['data']['embedding'])) {
                $embedding = $output['data']['embedding'];
                // Hapus foto lama jika ada
                if ($user->foto_base64 && \Illuminate\Support\Facades\Storage::exists('public/users/' . $user->foto_base64)) {
                    \Illuminate\Support\Facades\Storage::delete('public/users/' . $user->foto_base64);
                }

                // Simpan embedding + nama file foto
                $user->face_embedding         = json_encode($embedding);
                $user->foto_base64            = $savedFilename;
                $user->face_embedding_updated = now();

                // Simpan landmarks jika dikirim
                if ($request->landmarks) {
                    $user->face_landmarks = $request->landmarks;
                }

                $user->save();

                return response()->json([
                    'ok' => true, 
                    'message' => 'Wajah Anda berhasil didaftarkan ke sistem.'
                ]);
            }

            return response()->json([
                'ok' => false, 
                'message' => 'Wajah tidak terdeteksi pada gambar. Silakan coba lagi dengan pencahayaan yang baik.'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Terjadi kesalahan sistem saat mendaftarkan wajah.',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }
}
