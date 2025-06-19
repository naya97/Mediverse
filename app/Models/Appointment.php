<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = [
        'patient_id',
        'schedule_id',
        'timeSelected',
        'parent_id',
        'reservation_date',
        'status',
        'payment_intent_id',
        'payment_status',
        'reminder_offset',
        'reminder_sent',
        'price',
    ];

    public function patient() : BelongsTo {
        return $this->belongsTo(Patient::class);
    }

    public function schedule() : BelongsTo {
        return $this->belongsTo(Schedule::class);
    }

    public function MedicalInfo() : HasOne {
        return $this->hasOne(MedicalInfo::class);
    }
    
}
