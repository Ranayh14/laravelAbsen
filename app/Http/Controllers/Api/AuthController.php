<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Traits\ImageOptimizer;
use Symfony\Component\Process\Process;
use Exception;

class AuthController extends Controller
{
    use ImageOptimizer;
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
            'foto_base64' => 'required|string', // Wajib sertakan foto saat daftar
            'face_landmarks' => 'nullable|string',
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan.',
            'nim.required' => 'NIM wajib diisi.',
            'nim.unique'   => 'NIM ini sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'foto_base64.required' => 'Foto wajah wajib disertakan untuk pendaftaran.',
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
                'face_landmarks' => $request->face_landmarks,
            ]);

            // ---- PROSES REGISTRASI WAJAH (INSTAN) ----
            $imageName = 'face_' . $user->id . '_' . time() . '.jpg';
            $savedFilename = $this->optimizeAndSaveBase64($request->foto_base64, 'users', $imageName, 300, 70);
            
            if ($savedFilename) {
                $user->foto_base64 = $savedFilename;
                $user->save();

                // Generate Embedding menggunakan Python
                $facenetCli = base_path('scripts/facenet_cli.py');
                $imagePath  = storage_path('app/public/users/' . $savedFilename);
                $pythonPath = 'C:\\Python313\\python.exe';
                $cmdPython  = file_exists($pythonPath) ? $pythonPath : 'python';

                $jsonArgs = json_encode(['action' => 'generate_embedding', 'image' => $imagePath]);
                $process  = new Process([$cmdPython, $facenetCli, $jsonArgs]);
                
                // Set Environment (Samakan dengan FaceNetController)
                $process->setEnv([
                    'PYTHONPATH' => 'C:\\Python313\\Lib\\site-packages;C:\\Users\\Rana\\AppData\\Roaming\\Python\\Python313\\site-packages;' . base_path('scripts'),
                    'PATH' => 'C:\\Python313\\;' . getenv('PATH'),
                    'SystemRoot' => 'C:\\Windows',
                    'USERNAME' => 'Rana',
                    'USER' => 'Rana'
                ]);

                $process->run();

                if ($process->isSuccessful()) {
                    $output = json_decode($process->getOutput(), true);
                    if (isset($output['success']) && $output['success']) {
                        $user->face_embedding = json_encode($output['data']['embedding']);
                        $user->face_embedding_updated = now();
                        $user->save();
                    }
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'ok' => true,
                'message' => 'Registrasi berhasil (Akun & Wajah terdaftar)',
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