<?php

namespace App\Models;

use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Model;

class Lab_Pharmacy extends Model
{
    use Searchable;
    protected $fillable = [
        'name',
        'is_lab',
        'location',
        'start_time',
        'finish_time',
        'phone',
        'latitude',
        'longitude'
    ];

    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
        ];
    }
}
