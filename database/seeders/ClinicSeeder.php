<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ClinicSeeder extends Seeder
{
    public function run(): void
    {
        $clinics = [
            'Cardiologist',
            'Dentist',
            'Hepatologists',
            'Gastroenterologists',
            'Pulmonologist',
            'Psychiatrists',
            'Neurologist',
            'Nephrologist',
        ];

        foreach ($clinics as $clinicName) {
            $clinic = Clinic::create([
                'name'  => $clinicName,
                'photo' => 'images/clinics/' . $clinicName . 's.png', 
                'numOfDoctors' => 1,
            ]);

        }
    }
}
