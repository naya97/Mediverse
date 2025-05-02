<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Medicine extends Model
{
    protected $fillable = [
        'prescription_id',
        'name',
        'dose',
        'frequency',
        'strength',
        'until',
        'whenToTake',
        'note',
    ];

    public function prescription() : BelongsTo {
        return $this->belongsTo(Prescription::class);
    }
}
