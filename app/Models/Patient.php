<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;

class Patient extends Model
{
    use Searchable;

    protected $fillable = [
        'first_name',
        'last_name',
        'user_id',
        'birth_date',
        'gender',
        'blood_type',
        'address',
        'parent_id',
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
    public function toSearchableArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
        ];
    }

    public function children() : HasMany {
        return $this->hasMany(Patient::class, 'parent_id');
    }

    public function parent() : BelongsTo {
        return $this->belongsTo(Patient::class, 'parent_id');
    }

    public function vaccinationRecords() : HasMany {
        return $this->hasMany(VaccinationRecord::class, 'child_id');
    }

    public function childRecord() : HasOne {
        return $this->hasOne(ChildRecord::class, 'child_id');
    }
}
