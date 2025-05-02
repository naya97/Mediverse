<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'parent_id',
        'reservation_date',
        'reservation_hour',
        'status',
    ];

    public function patient() : BelongsTo {
        return $this->belongsTo(Patient::class);
    }

    public function doctor() : BelongsTo {
        return $this->belongsTo(Doctor::class);
    }

    public function MedicalInfo() : HasOne {
        return $this->hasOne(MedicalInfo::class);
    }
    
}
