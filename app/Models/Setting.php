<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    // Use default 'id' as primary key
    public $timestamps = false;

    protected $fillable = ['setting_key', 'setting_value', 'description'];
}
