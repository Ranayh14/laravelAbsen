<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage; // Tambahkan ini
use Exception;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Jika butuh filter berdasarkan role, bisa ditambahkan di sini
            $users = User::select('id', 'nama', 'email', 'nim', 'role', 'prodi', 'startup', 'created_at')
                ->orderBy('created_at', 'desc')
                ->limit(1000)
                ->get();
            return response()->json([
                'ok' => true,
                'message' => 'Berhasil mengambil data pengguna.',
                'data' => $users
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil data pengguna.',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Pengguna tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'ok' => true,
                'message' => 'Berhasil mengambil data pengguna.',
                'data' => $user
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil data pengguna.',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function getPhoto($id)
    {
        try {
            $user = User::find($id);
            if (!$user || !$user->foto_base64) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Foto tidak ditemukan.'
                ], 404);
            }
            
            $path = 'public/users/' . $user->foto_base64;
            if (!Storage::exists($path)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'File foto tidak ditemukan di server.'
                ], 404);
            }

            return Storage::response($path);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil foto.',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|unique:users,email',
            'nim'      => 'required|unique:users,nim',
            'nama'     => 'required|string|max:255',
            'prodi'    => 'required|string|max:255',
            'password' => 'required|min:6',
            'foto_base64'     => 'required|image|mimes:jpeg,png,jpg|max:2048', // Diubah ke image
            'startup'  => 'nullable|string',
        ], [
            'email.unique' => 'Email ini sudah digunakan.',
            'nim.unique'   => 'NIM ini sudah terdaftar.',
            'foto_base64.image'   => 'File harus berupa gambar (jpg, jpeg, png).',
            'foto_base64.max'     => 'Ukuran foto maksimal 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $path = $request->file('foto_base64')->store('public/users');
            $nama_file = basename($path);

            $user = User::create([
                'role' => 'pegawai',
                'email' => $request->email,
                'nim' => $request->nim,
                'nama' => $request->nama,
                'prodi' => $request->prodi,
                'startup' => $request->startup,
                'foto_base64' => $nama_file,
                'password' => Hash::make($request->password),
            ]);

            return response()->json([
                'ok' => true,
                'message' => 'Anggota berhasil ditambahkan.', // Pesan sukses
                'data' => $user
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menyimpan data ke database.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = User::where('id', $id)->where('role', 'pegawai')->first();
            
            if (!$user) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Anggota tidak ditemukan atau Anda tidak memiliki akses.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'email'   => 'nullable|email|unique:users,email,' . $id,
                'nim'     => 'nullable|unique:users,nim,' . $id,
                'nama'    => 'nullable|string|max:255',
                'foto_base64'    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Validasi gagal, silakan cek kembali data Anda.',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Update data teks
            $user->update($request->only(['email', 'nim', 'nama', 'prodi', 'startup']));

            // Proses foto jika ada
            if ($request->hasFile('foto_base64')) {
                // Hapus foto lama jika ada di storage
                if ($user->foto_base64) {
                    Storage::delete('public/users/' . $user->foto_base64);
                }
                
                $path = $request->file('foto_base64')->store('public/users');
                $user->foto_base64 = basename($path);
                $user->save();
            }

            // Kembalikan respon sukses yang jelas
            return response()->json([
                'ok' => true,
                'message' => 'Data anggota berhasil diperbarui.', 
                'data' => $user
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan sistem saat memperbarui data.',
                'error_debug' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Anggota tidak ditemukan.',
                ], 404);
            }

            // Hapus foto dari storage jika ada
            if ($user->foto_base64 && Storage::exists('public/users/' . $user->foto_base64)) {
                Storage::delete('public/users/' . $user->foto_base64);
            }

            // Hapus data dari database
            $user->delete();

            return response()->json([
                'ok' => true,
                'message' => 'Data anggota berhasil dihapus.',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menghapus data anggota.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}