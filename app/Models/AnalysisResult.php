<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalysisResult extends Model
{
    protected $fillable = [
        'medicalInfo_id',
        'name',
        'file',
        'photo',
    ];
}
