<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Patient extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'user_id',
        'age',
        'gender',
        'blood_type',
        'address',
    ];

    // use Notifiable;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function medicalInfos(): HasMany
    {
        return $this->hasMany(MedicalInfo::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function patientDetails(): HasMany
    {
        return $this->hasMany(PatientDetails::class);
    }

    public function patientReviews(): HasMany
    {
        return $this->hasMany(PatientReview::class);
    }
    public function analysis(): HasMany
    {
        return $this->hasMany(Analyse::class);
    }
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }
}
