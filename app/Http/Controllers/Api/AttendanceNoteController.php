<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class AttendanceNoteController extends Controller
{
    public function index()
    {
        try {
            $notes = AttendanceNote::with('user:id,nama,email,nim')->get();
            return response()->json([
                'ok' => true,
                'message' => 'Berhasil mengambil data catatan kehadiran.',
                'data' => $notes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil data catatan kehadiran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'type' => 'required|string',
            'keterangan' => 'nullable|string',
            'bukti' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $note = AttendanceNote::create($validator->validated());
            return response()->json([
                'ok' => true,
                'message' => 'Catatan kehadiran berhasil ditambahkan.',
                'data' => $note
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menambahkan catatan kehadiran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $note = AttendanceNote::with('user:id,nama,email,nim')->find($id);
            if (!$note) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Catatan kehadiran tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'ok' => true,
                'message' => 'Berhasil mengambil data catatan kehadiran.',
                'data' => $note
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil data catatan kehadiran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $note = AttendanceNote::find($id);
            if (!$note) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Catatan kehadiran tidak ditemukan.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|exists:users,id',
                'date' => 'sometimes|date',
                'type' => 'sometimes|string',
                'keterangan' => 'nullable|string',
                'bukti' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $note->update($validator->validated());
            return response()->json([
                'ok' => true,
                'message' => 'Catatan kehadiran berhasil diperbarui.',
                'data' => $note
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal memperbarui catatan kehadiran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $note = AttendanceNote::find($id);
            if (!$note) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Catatan kehadiran tidak ditemukan.'
                ], 404);
            }

            $note->delete();
            return response()->json([
                'ok' => true,
                'message' => 'Catatan kehadiran berhasil dihapus.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menghapus catatan kehadiran.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
