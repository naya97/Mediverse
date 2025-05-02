<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDetails extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'patient_id',
        'allergies',
        'chronic_conditions',
        'marital_status',
        'occupation',
        'smoking_status',
        'alcohol_use',
        'weight_kg',
        'height_cm',
        'last_visit_date',
        'family_medical_history',
        'notes',
    ];

    public function patient() : BelongsTo {
        return $this->belongsTo(Patient::class);
    }

    public function doctor() : BelongsTo {
        return $this->belongsTo(Doctor::class);
    }
}
