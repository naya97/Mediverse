<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    /** @use HasFactory<\Database\Factories\ClinicFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'numOfDoctors',
        'photo',
        'money'
    ];

    public function doctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }
    public function analysis(): HasMany
    {
        return $this->hasMany(Analyse::class);
    }
}
