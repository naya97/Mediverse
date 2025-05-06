<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lab_Pharmacy extends Model
{
    protected $fillable = [
        'name',
        'is_lab',
        'location',
        'start_time',
        'finish_time',
        'phone',
        'latitude',
        'longitude'
    ];
}
