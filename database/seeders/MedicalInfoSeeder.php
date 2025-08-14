<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalInfo;
use App\Models\Appointment;
use App\Models\Prescription;
use Carbon\Carbon;

class MedicalInfoSeeder extends Seeder
{
    public function run(): void
    {
        $appointments = Appointment::all();

        if ($appointments->isEmpty()) {
            $this->command->warn('⚠ No appointments found to seed medical infos.');
            return;
        }

        $symptomsList = [
            'Mild headache',
            'Low-grade fever',
            'Dry cough',
            'Stomach pain',
            'Dizziness when standing',
            'Shortness of breath',
            'Joint pain',
            'Sore throat',
            'General fatigue',
            'Lower back pain'
        ];

        $diagnosisList = [
            'Common cold',
            'Bacterial sore throat',
            'Seasonal flu',
            'Gastritis',
            'Low blood pressure',
            'Bronchitis',
            'Early-stage arthritis',
            'Respiratory allergy',
            'Fatigue due to lack of sleep',
            'Lower back muscle strain'
        ];

        // Notes for doctors (not visible to patient)
        $doctorNotes = [
            'Monitor patient’s blood pressure in next visit.',
            'Consider lab tests if symptoms persist.',
            'Evaluate patient for possible allergies.',
            'Recommend physical therapy if pain continues.',
            'Check vaccination history during next appointment.'
        ];

        // Notes for patients (visible to them)
        $patientNotes = [
            'Drink plenty of fluids and get enough rest.',
            'Take medicine after meals twice a day.',
            'Avoid cold drinks and keep warm.',
            'Follow the exercise plan provided.',
            'Come back for a follow-up in one week.'
        ];

        foreach ($appointments as $appointment) {
            $prescriptionId = null;

            // Link to an existing prescription if available
            $patientPrescriptions = Prescription::where('patient_id', $appointment->patient_id)->get();
            if ($patientPrescriptions->isNotEmpty() && rand(0, 1)) {
                $prescriptionId = $patientPrescriptions->random()->id;
            }

            MedicalInfo::create([
                'prescription_id' => $prescriptionId,
                'appointment_id'  => $appointment->id,
                'symptoms'        => $symptomsList[array_rand($symptomsList)],
                'diagnosis'       => $diagnosisList[array_rand($diagnosisList)],
                'doctorNote'      => $doctorNotes[array_rand($doctorNotes)],
                'patientNote'     => $patientNotes[array_rand($patientNotes)],
                'created_at'      => Carbon::now()->subDays(rand(0, 30)),
                'updated_at'      => Carbon::now(),
            ]);
        }
    }
}
