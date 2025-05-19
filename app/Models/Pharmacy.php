<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Pharmacy extends Model
{
    use Searchable;
    protected $fillable = [
        'name',
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
