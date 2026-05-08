<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminHelpRequest extends Model
{
    protected $fillable = [
        'user_id', 
        'request_type', 
        'attendance_type', 
        'attendance_reason', 
        'tanggal', 
        'jam_masuk', 
        'jam_pulang', // Menambahkan jam_pulang jika ada di form
        'jenis_izin', // Menambahkan field alias untuk mempermudah
        'alasan_izin',
        'bukti_izin',
        'bukti_presensi',
        'lokasi_presensi',
        'bug_description',
        'bug_proof',
        'keterangan', 
        'status', 
        'admin_note',
        'is_read_by_user'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
