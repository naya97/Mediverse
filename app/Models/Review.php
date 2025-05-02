<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Review extends Model
{
    protected $fillable = [
        'rate',
        'comment',
    ];

    public function patientReviews() : HasMany {
        return $this->hasMany(PatientReview::class);
    }
}
