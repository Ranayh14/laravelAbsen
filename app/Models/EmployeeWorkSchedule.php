<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeWorkSchedule extends Model
{
    protected $table = 'employee_work_schedule';
    
    protected $fillable = [
        'user_id',
        'day_of_week',
        'is_working_day',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_working_day' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
