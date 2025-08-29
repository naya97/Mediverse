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
            'Cardio',        // Cardiologist
            'Dental',        // Dentist
            'Liver',         // Hepatologists
            'Gastro',        // Gastroenterologists
            'Lungs',         // Pulmonologist
            'Psych',         // Psychiatrists
            'Neuro',         // Neurologist
            'Kidney',        // Nephrologist
        ];
        $numOfDoctors = [2, 2, 1, 1, 1, 2, 1, 1];

        for ($i = 0; $i < 8; $i++) {
            Clinic::create([
                'name'  => $clinics[$i],
                'photo' => '/storage/images/clinics/' . $clinics[$i] . 's.png',
                'numOfDoctors' => $numOfDoctors[$i],
            ]);
        }
    }
}
