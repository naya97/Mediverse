<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildRecord extends Model
{
    protected $fillable = [
        'child_id',
        'doctor_id',
        'last_visit_date',
        'next_visit_date',
        'height_cm',
        'weight_kg',
        'head_circumference_cm',
        'growth_notes',
        'developmental_observations',
        'allergies',
        'doctor_notes',
        'feeding_type',
    ];

    public function patient() : BelongsTo {
        return $this->belongsTo(Patient::class, 'child_id'); 
    }

    public function doctor(): BelongsTo {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

}
