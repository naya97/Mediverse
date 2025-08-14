<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Doctor;
use Carbon\Carbon;

class PrescriptionSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        $doctors  = Doctor::all();

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->warn('⚠ لا يوجد بيانات كافية لإنشاء الوصفات.');
            return;
        }

        foreach ($patients as $patient) {
            $numPrescriptions = rand(1, 3);

            for ($i = 0; $i < $numPrescriptions; $i++) {
                $doctor = $doctors->random();

                Prescription::create([
                    'patient_id' => $patient->id,
                    'doctor_id'  => $doctor->id,
                    'note'       => fake()->sentence(rand(5, 10)),
                    'created_at' => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}
