<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'company',
        'discount_code',
        'discount_rate',
    ];
}
