<?php

namespace Database\Seeders;

use App\Models\Clinic;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClinicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clinic::create([
            'name' => 'Heart',
            'numOfDoctors' => 2,
            'location' => 'whatever'
        ]);
        Clinic::create([
            'name' => 'Mental',
            'numOfDoctors' => 2,
            'location' => 'whatever'
        ]);
        Clinic::create([
            'name' => 'Dental',
            'numOfDoctors' => 2,
            'location' => 'whatever'
        ]);
    }
}
