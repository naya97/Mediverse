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
            'numOfDoctors' => 0,
        ]);
        Clinic::create([
            'name' => 'Mental',
            'numOfDoctors' => 0,
        ]);
        Clinic::create([
            'name' => 'Dental',
            'numOfDoctors' => 0,
        ]);
    }
}
