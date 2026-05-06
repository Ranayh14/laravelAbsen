<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function index()
    {
        try {
            $settings = Setting::all();
            $formatted = [];
            foreach ($settings as $s) {
                $formatted[$s->setting_key] = $s->setting_value;
            }
            return response()->json([
                'ok' => true, 
                'message' => 'Berhasil mengambil pengaturan.',
                'data' => $formatted
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal mengambil pengaturan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $key)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized).'], 403);
            }

            $setting = Setting::where('setting_key', $key)->first();
            
            if (!$setting) {
                return response()->json(['ok' => false, 'message' => 'Pengaturan tidak ditemukan.'], 404);
            }

            return response()->json([
                'ok' => true, 
                'message' => 'Berhasil mengambil data pengaturan.',
                'data' => $setting
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal mengambil data pengaturan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Hanya admin yang dapat menambah pengaturan.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'setting_key' => 'required|string|unique:settings,setting_key',
                'setting_value' => 'nullable|string',
            ], [
                'setting_key.required' => 'Kunci pengaturan wajib diisi.',
                'setting_key.unique' => 'Kunci pengaturan sudah ada.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false, 
                    'message' => 'Validasi gagal.', 
                    'errors' => $validator->errors()
                ], 400);
            }

            $setting = Setting::create([
                'setting_key' => $request->setting_key,
                'setting_value' => $request->setting_value ?? ''
            ]);

            return response()->json([
                'ok' => true, 
                'message' => 'Pengaturan berhasil ditambahkan.',
                'data' => $setting
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal menambahkan pengaturan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Hanya admin yang dapat memperbarui pengaturan.'], 403);
            }

            $data = $request->all();
            
            // Hapus key 'ajax' jika ada
            unset($data['ajax']);

            foreach ($data as $key => $value) {
                if ($value === null) continue;

                Setting::updateOrCreate(
                    ['setting_key' => $key],
                    ['setting_value' => (string)$value]
                );
            }

            // --- BAGIAN BARU: Ambil data terbaru setelah update ---
            $allSettings = Setting::all();
            $formatted = [];
            foreach ($allSettings as $s) {
                $formatted[$s->setting_key] = $s->setting_value;
            }

            return response()->json([
                'ok' => true, 
                'message' => 'Pengaturan berhasil diperbarui.',
                'data' => $formatted // Mengembalikan objek settings terbaru
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal memperbarui pengaturan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }
    public function destroy(Request $request, $key)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Hanya admin yang dapat menghapus pengaturan.'], 403);
            }

            $setting = Setting::where('setting_key', $key)->first();
            
            if (!$setting) {
                return response()->json(['ok' => false, 'message' => 'Pengaturan tidak ditemukan.'], 404);
            }

            $setting->delete();

            return response()->json([
                'ok' => true, 
                'message' => 'Pengaturan berhasil dihapus.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false, 
                'message' => 'Gagal menghapus pengaturan.', 
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }
}
