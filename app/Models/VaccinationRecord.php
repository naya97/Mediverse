<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VaccinationRecord extends Model
{
    protected $fillable = [
        'child_id',
        'vaccine_id',
        'appointment_id',
        'date_given',
        'dose_number',
        'notes',
        'isTaken',
        'next_vaccine_date',
        'when_to_take',
        'recommended',
    ];

    public function vaccine() : BelongsTo {
        return $this->belongsTo(Vaccine::class);
    }

    public function patient() : BelongsTo {
        return $this->belongsTo(Patient::class, 'child_id'); 
    }

    // public function doctor() : BelongsTo {
    //     return $this->belongsTo(Doctor::class, 'doctor_id');
    // }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

}
