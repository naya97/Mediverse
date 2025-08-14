<?php

namespace Database\Seeders;

use App\Models\Analyse;
use Illuminate\Database\Seeder;
use App\Models\Patient;
use App\Models\Clinic;
use App\Models\Doctor;
use Carbon\Carbon;

class AnalysisSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        $clinics  = Clinic::all();
        $doctors  = Doctor::all();

        if ($patients->isEmpty() || $clinics->isEmpty()) {
            $this->command->warn('there is no enough data to make the analysis');
            return;
        }

        $analysisNames = [
            'Blood Test',
            'Urine Test',
            'X-Ray',
            'MRI',
            'CT Scan',
            'Liver Function Test',
            'Kidney Function Test',
            'Thyroid Function Test'
        ];

        foreach ($patients as $patient) {
            $numAnalyses = rand(1, 3);

            for ($i = 0; $i < $numAnalyses; $i++) {
                $clinic = $clinics->random();
                $doctor = $doctors->where('clinic_id', $clinic->id)->random() ?? null;

                // 0 = بدون نتيجة, 1 = ملف, 2 = صورة
                $resultType = rand(0, 2);
                $resultFile = null;
                $resultPhoto = null;
                $price = 0;
                $status = 'pending';
                $paymentStatus = 'pending';

                if ($resultType === 1) {
                    $resultFile = '/storage/results/file_' . rand(1, 50) . '.pdf';
                    $price = rand(50, 500);
                    $status = 'finished';
                    $paymentStatus = 'paid';
                } elseif ($resultType === 2) {
                    $resultPhoto = '/storage/results/photo_' . rand(1, 50) . '.jpg';
                    $price = rand(50, 500);
                    $status = 'finished';
                    $paymentStatus = 'paid';
                } else {
                    // لا يوجد نتيجة
                    $price = rand(0, 1) ? 0 : rand(50, 500);
                    $paymentStatus = ($price == 0) ? 'pending' : 'paid';
                    $status = 'pending';
                }

                Analyse::create([
                    'patient_id'     => $patient->id,
                    'clinic_id'      => $clinic->id,
                    'doctor_id'      => $doctor?->id,
                    'name'           => $analysisNames[array_rand($analysisNames)],
                    'description'    => fake()->sentence(),
                    'result_file'    => $resultFile,
                    'result_photo'   => $resultPhoto,
                    'status'         => $status,
                    'price'          => $price,
                    'payment_status' => $paymentStatus,
                    'created_at'     => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at'     => Carbon::now(),
                ]);
            }
        }
    }
}
