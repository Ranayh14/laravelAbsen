<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManualHoliday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class ManualHolidayController extends Controller
{
    public function index()
    {
        try {
            $holidays = ManualHoliday::with('creator:id,nama,email')->get();
            return response()->json([
                'ok' => true,
                'message' => 'Berhasil mengambil data hari libur manual.',
                'data' => $holidays
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil data hari libur manual.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date|unique:manual_holidays,date',
            'name' => 'required|string|max:255',
            'created_by' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $holiday = ManualHoliday::create($validator->validated());
            return response()->json([
                'ok' => true,
                'message' => 'Hari libur manual berhasil ditambahkan.',
                'data' => $holiday
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menambahkan hari libur manual.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $holiday = ManualHoliday::with('creator:id,nama,email')->find($id);
            if (!$holiday) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Hari libur manual tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'ok' => true,
                'message' => 'Berhasil mengambil data hari libur manual.',
                'data' => $holiday
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil data hari libur manual.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $holiday = ManualHoliday::find($id);
            if (!$holiday) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Hari libur manual tidak ditemukan.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'date' => 'sometimes|date|unique:manual_holidays,date,' . $id,
                'name' => 'sometimes|string|max:255',
                'created_by' => 'sometimes|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $holiday->update($validator->validated());
            return response()->json([
                'ok' => true,
                'message' => 'Hari libur manual berhasil diperbarui.',
                'data' => $holiday
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal memperbarui hari libur manual.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $holiday = ManualHoliday::find($id);
            if (!$holiday) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Hari libur manual tidak ditemukan.'
                ], 404);
            }

            $holiday->delete();
            return response()->json([
                'ok' => true,
                'message' => 'Hari libur manual berhasil dihapus.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menghapus hari libur manual.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
