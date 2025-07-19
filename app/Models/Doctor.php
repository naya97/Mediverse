<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class Doctor extends Model
{
    /** @use HasFactory<\Database\Factories\DoctorFactory> */
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'first_name',
        'last_name',
        'user_id',
        'clinic_id',
        'photo',
        'speciality',
        'professional_title',
        'finalRate',
        'average_visit_duration',
        'checkup_duration',
        'visit_fee',
        'sign',
        'status',
        'treated',
        'experience',
        'booking_type',
    ];

    // use Notifiable;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function schedule(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    public function doctorReviews(): HasMany
    {
        return $this->hasMany(PatientReview::class);
    }

    public function patientDetails(): HasMany
    {
        return $this->hasMany(PatientDetails::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? url($this->photo) : null;
    }

    public function getSignUrlAttribute()
    {
        return $this->sign ? url($this->sign) : null;
    }

    public function toSearchableArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
        ];
    }
}
