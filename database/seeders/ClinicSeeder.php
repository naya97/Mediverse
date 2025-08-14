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

        foreach ($clinics as $clinicName) {
            $clinic = Clinic::create([
                'name'  => $clinicName,
                'photo' => '/storage/images/clinics/' . $clinicName . 's.png', 
                'numOfDoctors' => 1,
            ]);

        }
    }
}
