<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            ClinicSeeder::class,
            DoctorSeeder::class,
            PatientSeeder::class,
            ScheduleSeeder::class,
            AppointmentSeeder::class,
            PrescriptionSeeder::class,
            MedicineSeeder::class,
            MedicalInfoSeeder::class,
            ReviewSeeder::class,
            PatientReviewSeeder::class,
            PharmacySeeder::class,
            AnalysisSeeder::class,
            ReportSeeder::class,
            VaccinesSeeder::class,
            VaccinationRecordSeeder::class,
            ChildRecordSeeder::class,
        ]);
        // User::factory()->create([
        //     'first_name' => 'Test User',
        //     'password' => Hash::make('Nour1234'),
        //     'phone' => '0936820776',
        //     'role' => 'admin',
        // ]);
    }
}
