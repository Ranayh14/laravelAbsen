<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use Illuminate\Support\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    use \App\Traits\ImageOptimizer;

    /**
     * Ambil semua data presensi (admin only).
     * Menggunakan pagination untuk menghindari memory exhaustion.
     */
    public function index(Request $request)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized)'], 403);
            }

            $perPage = min((int) $request->get('per_page', 50), 200); // Max 200 per page

            $attendances = Attendance::with(['user' => function ($query) {
                // Hanya ambil kolom minimal — JANGAN ambil foto/embedding
                $query->select('id', 'nama', 'email', 'nim', 'role', 'prodi', 'startup');
            }])
            ->select(
                'id', 'user_id',
                'jam_masuk', 'jam_masuk_iso',
                'jam_pulang', 'jam_pulang_iso',
                'lat_masuk', 'lng_masuk', 'lokasi_masuk',
                'lat_pulang', 'lng_pulang', 'lokasi_pulang',
                'ekspresi_masuk', 'ekspresi_pulang',
                'landmark_masuk', 'landmark_pulang', // Pengganti screenshot (jauh lebih kecil)
                'foto_masuk', 'foto_pulang', // Bukti gambar kompresi (10-20KB)
                'ket', 'status',
                'alasan_wfa', 'alasan_overtime', 'alasan_pulang_awal',
                'is_overtime', 'overtime_bonus',
                'created_at', 'updated_at'
            )
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

            return response()->json([
                'ok'      => true,
                'message' => 'Berhasil mengambil data absensi',
                'data'    => $attendances->items(),
                'meta'    => [
                    'current_page' => $attendances->currentPage(),
                    'last_page'    => $attendances->lastPage(),
                    'per_page'     => $attendances->perPage(),
                    'total'        => $attendances->total(),
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Gagal mengambil data absensi',
                'debug_error' => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
            ], 500);
        }
    }

    /**
     * Ambil detail satu data presensi (termasuk landmark untuk admin).
     */
    public function show(Request $request, $id)
    {
        try {
            $attendance = Attendance::with(['user' => function ($q) {
                $q->select('id', 'nama', 'email', 'nim', 'role', 'prodi', 'startup');
            }])->find($id);

            if (!$attendance) {
                return response()->json(['ok' => false, 'message' => 'Data absensi tidak ditemukan'], 404);
            }

            // Jika bukan admin, pastikan user hanya bisa melihat absensinya sendiri
            if ($request->user()->role !== 'admin' && $attendance->user_id !== $request->user()->id) {
                return response()->json(['ok' => false, 'message' => 'Anda tidak memiliki akses (Unauthorized)'], 403);
            }

            return response()->json([
                'ok'      => true,
                'message' => 'Berhasil mengambil data absensi',
                'data'    => $attendance
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Gagal mengambil data absensi',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Admin buat data presensi manual (izin/sakit/wfo/wfa).
     */
    public function store(Request $request)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Hanya admin yang dapat menambah data'], 403);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'tanggal' => 'required|date',
                'ket'     => 'required|in:izin,sakit,wfo,wfa,overtime',
                'status'  => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['ok' => false, 'errors' => $validator->errors()], 400);
            }

            $data    = $request->all();
            $tanggal = $request->tanggal;

            if (in_array($request->ket, ['izin', 'sakit'])) {
                $data['jam_masuk']     = '08:00';
                $data['jam_pulang']    = '17:00';
                $data['jam_masuk_iso'] = $tanggal . ' 08:00:00';
                $data['jam_pulang_iso'] = $tanggal . ' 17:00:00';
            } else {
                $data['jam_masuk']     = $request->jam_masuk ?? '08:00';
                $data['jam_masuk_iso'] = $request->jam_masuk_iso ?? Carbon::now();
            }

            $attendance = Attendance::create($data);

            return response()->json([
                'ok'      => true,
                'message' => 'Berhasil menambahkan data absensi',
                'data'    => $attendance
            ], 201);
        } catch (Exception $e) {
            return response()->json(['ok' => false, 'debug_error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Hanya admin yang dapat mengubah data absensi'], 403);
            }

            $attendance = Attendance::find($id);
            if (!$attendance) {
                return response()->json(['ok' => false, 'message' => 'Data absensi tidak ditemukan'], 404);
            }

            // Hanya update field yang diperbolehkan (bukan landmark — landmark dikirim dari device)
            $attendance->update($request->only([
                'jam_masuk', 'jam_masuk_iso', 'jam_pulang', 'jam_pulang_iso',
                'lat_masuk', 'lng_masuk', 'lokasi_masuk',
                'lat_pulang', 'lng_pulang', 'lokasi_pulang',
                'ket', 'status', 'alasan_wfa', 'alasan_overtime',
                'alasan_izin_sakit', 'bukti_izin_sakit', 'alasan_pulang_awal',
                'is_overtime', 'overtime_bonus', 'daily_report_id',
            ]));

            return response()->json([
                'ok'      => true,
                'message' => 'Berhasil mengubah data absensi',
                'data'    => $attendance
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Gagal mengubah data absensi',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            if ($request->user()->role !== 'admin') {
                return response()->json(['ok' => false, 'message' => 'Hanya admin yang dapat menghapus data absensi'], 403);
            }

            $attendance = Attendance::find($id);
            if (!$attendance) {
                return response()->json(['ok' => false, 'message' => 'Data absensi tidak ditemukan'], 404);
            }

            $attendance->delete();

            return response()->json(['ok' => true, 'message' => 'Berhasil menghapus data absensi']);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Gagal menghapus data absensi',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clock In — Absen Masuk.
     * Menerima landmark wajah JSON (68 titik) sebagai pengganti screenshot.
     */
    public function clockIn(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lat_masuk'      => 'required|numeric',
                'lng_masuk'      => 'required|numeric',
                'lokasi_masuk'   => 'required|string',
                'ket'            => 'required|in:wfo,wfa,overtime',
                'landmark_masuk' => 'nullable|string',
                'ekspresi_masuk' => 'nullable|string',
                'image'          => 'nullable|string',
            ], [
                'lat_masuk.required'    => 'Latitude lokasi masuk wajib diisi.',
                'lng_masuk.required'    => 'Longitude lokasi masuk wajib diisi.',
                'lokasi_masuk.required' => 'Nama lokasi masuk wajib diisi.',
                'ket.required'         => 'Keterangan presensi (WFO/WFA/Overtime) wajib dipilih.',
                'ket.in'               => 'Keterangan presensi harus berupa wfo, wfa, atau overtime.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Validasi gagal. Silakan periksa kembali data Anda.',
                    'errors'  => $validator->errors()
                ], 400);
            }

            $user = $request->user();

            // Cek apakah sudah absen masuk hari ini
            $existing = Attendance::where('user_id', $user->id)
                ->whereDate('jam_masuk_iso', Carbon::today())
                ->first();

            if ($existing) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Anda sudah melakukan absen masuk untuk hari ini.'
                ], 400);
            }

            $attendance = Attendance::create([
                'user_id'        => $user->id,
                'jam_masuk'      => Carbon::now()->format('H:i'),
                'jam_masuk_iso'  => Carbon::now(),
                'lat_masuk'      => $request->lat_masuk,
                'lng_masuk'      => $request->lng_masuk,
                'lokasi_masuk'   => $request->lokasi_masuk,
                'ket'            => $request->ket,
                'status'         => 'ontime',
                'landmark_masuk' => $request->landmark_masuk,
                'ekspresi_masuk' => $request->ekspresi_masuk,
                'foto_masuk'     => $request->image ? 'attendance/' . $this->optimizeAndSaveBase64($request->image, 'attendance', null, 200, 60) : null,
            ]);

            return response()->json([
                'ok'      => true,
                'message' => 'Absen masuk berhasil dilakukan. Semangat bekerja!',
                'data'    => $attendance
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Terjadi kesalahan sistem saat melakukan absen masuk.',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clock Out — Absen Pulang.
     * Menerima landmark wajah JSON (68 titik) sebagai pengganti screenshot.
     */
    public function clockOut(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'lat_pulang'      => 'required|numeric',
                'lng_pulang'      => 'required|numeric',
                'lokasi_pulang'   => 'required|string',
                'landmark_pulang' => 'nullable|string',
                'ekspresi_pulang' => 'nullable|string',
                'image'           => 'nullable|string',
            ], [
                'lat_pulang.required'    => 'Latitude lokasi pulang wajib diisi.',
                'lng_pulang.required'    => 'Longitude lokasi pulang wajib diisi.',
                'lokasi_pulang.required' => 'Nama lokasi pulang wajib diisi.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Validasi gagal. Silakan periksa kembali data Anda.',
                    'errors'  => $validator->errors()
                ], 400);
            }

            $user = $request->user();

            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('jam_masuk_iso', Carbon::today())
                ->whereNull('jam_pulang')
                ->first();

            if (!$attendance) {
                return response()->json([
                    'ok'      => false,
                    'message' => 'Data absen masuk hari ini tidak ditemukan atau Anda sudah melakukan absen pulang.'
                ], 400);
            }

            $attendance->update([
                'jam_pulang'      => Carbon::now()->format('H:i'),
                'jam_pulang_iso'  => Carbon::now(),
                'lat_pulang'      => $request->lat_pulang,
                'lng_pulang'      => $request->lng_pulang,
                'lokasi_pulang'   => $request->lokasi_pulang,
                'landmark_pulang' => $request->landmark_pulang,
                'ekspresi_pulang' => $request->ekspresi_pulang,
                'foto_pulang'     => $request->image ? 'attendance/' . $this->optimizeAndSaveBase64($request->image, 'attendance', null, 200, 60) : null,
            ]);

            return response()->json([
                'ok'      => true,
                'message' => 'Absen pulang berhasil dilakukan. Hati-hati di jalan!',
                'data'    => $attendance
            ]);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Terjadi kesalahan sistem saat melakukan absen pulang.',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Presensi hari ini milik user yang sedang login.
     */
    public function today(Request $request)
    {
        try {
            $attendance = Attendance::select(
                'id', 'user_id',
                'jam_masuk', 'jam_masuk_iso',
                'jam_pulang', 'jam_pulang_iso',
                'lat_masuk', 'lng_masuk', 'lokasi_masuk',
                'lat_pulang', 'lng_pulang', 'lokasi_pulang',
                'ket', 'status', 'is_overtime',
                'ekspresi_masuk', 'ekspresi_pulang',
                'created_at'
            )
            ->where('user_id', $request->user()->id)
            ->whereDate('jam_masuk_iso', Carbon::today())
            ->first();

            return response()->json(['ok' => true, 'message' => 'Berhasil mengambil absensi hari ini', 'data' => $attendance]);
        } catch (Exception $e) {
            return response()->json([
                'ok'          => false,
                'message'     => 'Gagal mengambil data absensi hari ini',
                'debug_error' => $e->getMessage()
            ], 500);
        }
    }
}
