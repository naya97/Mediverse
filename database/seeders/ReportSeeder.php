<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\Patient;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patients = Patient::all();

        if ($patients->isEmpty()) {
            $this->command->warn('⚠ لا يوجد مرضى لإنشاء تقارير لهم.');
            return;
        }

        $reportTypes = [
            'Technical issue',
            'Offense',
            'Privacy violation',
            'Poor cleanliness',
            'Bad experience',
            'Billing issue',
            'Mismanagement',
            'Misdiagnosis',
            'Unclear instructions',
            'Other'
        ];

        foreach ($patients as $patient) {
            // كل مريض يمكن يكون عنده من 0 إلى 2 تقارير عشوائية
            $numReports = rand(0, 2);

            for ($i = 0; $i < $numReports; $i++) {
                Report::create([
                    'patient_id'  => $patient->id,
                    'type'        => $reportTypes[array_rand($reportTypes)],
                    'description' => fake()->paragraph(rand(1, 3)), // نص عشوائي من 1 إلى 3 جمل
                    'created_at'  => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at'  => Carbon::now(),
                ]);
            }
        }
    }
}
