<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Vaccine;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VaccinationRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vaccines = Vaccine::all();

        $childs = Patient::whereNotNull('parent_id')
            ->where('birth_date', '>=', Carbon::now()->subYears(12))
        ->get();

        $appointments = Appointment::where('appointment_type', 'vaccination')->get();

    }
}
