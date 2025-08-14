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
        'expected_price',
        'paid_price',
        'appointment_type',
        'is_referral',
        'referring_doctor',
        'queue_number',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function referring_doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function MedicalInfo(): HasOne
    {
        return $this->hasOne(MedicalInfo::class);
    }

    public function vaccinations(): HasOne
    {
        return $this->hasOne(VaccinationRecord::class);
    }

    
}
