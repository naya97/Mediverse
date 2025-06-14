<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Analyse extends Model
{
    use Searchable;
    protected $fillable = [
        'name',
        'description',
        'result_file',
        'result_photo',
        'status',
        'patient_id',
        'clinic_id',
        'payment_status',
        'price',
        'doctor_id'
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'patient_id' => $this->patient_id,
        ];
    }
}
