<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceNote extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'type',
        'keterangan',
        'bukti',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
