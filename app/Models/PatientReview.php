<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientReview extends Model
{
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'review_id',
    ];

    public function patient() : BelongsTo {
        return $this->belongsTo(Patient::class);
    }

    public function doctor() : BelongsTo {
        return $this->belongsTo(Doctor::class);
    }

    public function review() : BelongsTo {
        return $this->belongsTo(Review::class);
    }
}
