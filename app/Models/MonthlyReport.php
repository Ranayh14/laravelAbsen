<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    protected $fillable = [
        'user_id', 
        'month', 
        'year', 
        'summary', 
        'achievements', 
        'obstacles', 
        'status'
    ];

    protected $casts = [
        'achievements' => 'array',
        'obstacles' => 'array',
    ];

    /**
     * Override serialization tanggal agar menggunakan timezone lokal (WIB) bukan UTC.
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
