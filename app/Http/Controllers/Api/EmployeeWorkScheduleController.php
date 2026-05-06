<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployeeWorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Validation\Rule;

class EmployeeWorkScheduleController extends Controller
{
    public function index()
    {
        try {
            $schedules = EmployeeWorkSchedule::with('user:id,nama,email,nim')->get();
            return response()->json([
                'ok' => true,
                'message' => 'Berhasil mengambil data jadwal kerja karyawan.',
                'data' => $schedules
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil data jadwal kerja karyawan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'day_of_week' => [
                'required',
                Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
                Rule::unique('employee_work_schedule')->where(function ($query) use ($request) {
                    return $query->where('user_id', $request->user_id);
                })
            ],
            'is_working_day' => 'required|boolean',
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $schedule = EmployeeWorkSchedule::create($validator->validated());
            return response()->json([
                'ok' => true,
                'message' => 'Jadwal kerja karyawan berhasil ditambahkan.',
                'data' => $schedule
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menambahkan jadwal kerja karyawan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $schedule = EmployeeWorkSchedule::with('user:id,nama,email,nim')->find($id);
            if (!$schedule) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Jadwal kerja karyawan tidak ditemukan.'
                ], 404);
            }
            return response()->json([
                'ok' => true,
                'message' => 'Berhasil mengambil data jadwal kerja karyawan.',
                'data' => $schedule
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal mengambil data jadwal kerja karyawan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $schedule = EmployeeWorkSchedule::find($id);
            if (!$schedule) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Jadwal kerja karyawan tidak ditemukan.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|exists:users,id',
                'day_of_week' => [
                    'sometimes',
                    Rule::in(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']),
                    Rule::unique('employee_work_schedule')->where(function ($query) use ($request, $schedule) {
                        return $query->where('user_id', $request->input('user_id', $schedule->user_id));
                    })->ignore($schedule->id)
                ],
                'is_working_day' => 'sometimes|boolean',
                'start_time' => 'nullable|date_format:H:i:s',
                'end_time' => 'nullable|date_format:H:i:s',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors()
                ], 400);
            }

            $schedule->update($validator->validated());
            return response()->json([
                'ok' => true,
                'message' => 'Jadwal kerja karyawan berhasil diperbarui.',
                'data' => $schedule
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal memperbarui jadwal kerja karyawan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $schedule = EmployeeWorkSchedule::find($id);
            if (!$schedule) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Jadwal kerja karyawan tidak ditemukan.'
                ], 404);
            }

            $schedule->delete();
            return response()->json([
                'ok' => true,
                'message' => 'Jadwal kerja karyawan berhasil dihapus.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Gagal menghapus jadwal kerja karyawan.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
