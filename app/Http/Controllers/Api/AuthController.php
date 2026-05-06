<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Exception;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // 2. Cari User
            // Pastikan kolom di database benar-benar 'email'
            $user = User::where('email', $request->email)->first();

            // 3. Cek User dan Password
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Email atau password salah'
                ], 401);
            }

            // 4. Generate Token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'ok' => true,
                'message' => 'Login berhasil',
                'role' => $user->role,
                'token' => $token,
                'user' => $user
            ], 200);

        } catch (Exception $e) {
            // 5. Tangkap Error tak terduga (Internal Server Error)
            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan pada server',
                'debug_error' => $e->getMessage() // Hapus baris ini jika sudah naik ke produksi
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|unique:users,email',
            'nim'      => 'required|unique:users,nim',
            'nama'     => 'required|string|max:255',
            'password' => 'required|min:6',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique'   => 'NIM ini sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = User::create([
                'role' => 'pegawai',
                'email' => $request->email,
                'nim' => $request->nim,
                'nama' => $request->nama,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'ok' => true,
                'message' => 'Registrasi berhasil',
                'role' => $user->role,
                'token' => $token,
                'user' => $user
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Terjadi kesalahan saat registrasi',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json([
                'ok' => true, 
                'message' => 'Berhasil keluar (Logged out)'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal logout',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }
}