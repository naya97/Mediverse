<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vaccine extends Model
{
    protected $fillable = [
        'name',
        'description',
        'age_group',
        'recommended_doses',
        'price'
    ];

    public function vaccinationRecord() : HasMany {
        return $this->hasMany(VaccinationRecord::class);
    }
}
