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

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
