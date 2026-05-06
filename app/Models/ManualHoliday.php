<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualHoliday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
