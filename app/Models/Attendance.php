<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $table = 'attendance';

    /**
     * Kolom yang boleh diisi secara massal.
     * Explicit fillable lebih aman dan efisien dari $guarded = ['id']
     */
    protected $fillable = [
        'user_id',
        'jam_masuk',
        'jam_masuk_iso',
        'jam_pulang',
        'jam_pulang_iso',
        'lat_masuk',
        'lng_masuk',
        'lat_pulang',
        'lng_pulang',
        'lokasi_masuk',
        'lokasi_pulang',
        'ekspresi_masuk',
        'ekspresi_pulang',
        'landmark_masuk',    // JSON 68 titik wajah (~1-2KB, menggantikan screenshot)
        'landmark_pulang',   // JSON 68 titik wajah (~1-2KB, menggantikan screenshot)
        'foto_masuk',        // Path foto bukti kompresi masuk
        'foto_pulang',       // Path foto bukti kompresi pulang
        'status',
        'ket',
        'alasan_wfa',
        'alasan_overtime',
        'lokasi_overtime',
        'alasan_izin_sakit',
        'bukti_izin_sakit',
        'alasan_pulang_awal',
        'daily_report_id',
        'is_overtime',
        'overtime_bonus',
    ];

    /**
     * Cast otomatis: landmark JSON di-parse menjadi array PHP
     */
    protected $casts = [
        'landmark_masuk'  => 'array',
        'landmark_pulang' => 'array',
        'is_overtime'     => 'boolean',
        'overtime_bonus'  => 'decimal:2',
        'jam_masuk_iso'   => 'datetime',
        'jam_pulang_iso'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
