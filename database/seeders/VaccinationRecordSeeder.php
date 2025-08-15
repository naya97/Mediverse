<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\VaccinationRecord;
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
        $childs = Patient::whereNotNull('parent_id')
            ->where('birth_date', '>=', Carbon::now()->subYears(12))
        ->get();

        foreach ($childs as $child) {
            $this->generateVaccinationRecordsForChild($child);
        }

    }

    private function ageStringToMonths($ageStr)
    {
        $ageStr = strtolower(trim($ageStr));
        if ($ageStr == 'at birth' || $ageStr == 'birth' || $ageStr == 'newborn') return 0;
        $number = (int) filter_var($ageStr, FILTER_SANITIZE_NUMBER_INT);
        return strpos($ageStr, 'year') !== false ? $number * 12 :
            (strpos($ageStr, 'month') !== false ? $number : null);
    }

    private function generateVaccinationRecordsForChild($child)
    {
        $birthDate = Carbon::parse($child->birth_date);
        $now = Carbon::now();
        $ageInMonths = $birthDate->diffInMonths($now);

        $recommendedNow = [];
        $upcomingVaccines = [];

        $vaccines = Vaccine::all();

        foreach ($vaccines as $vaccine) {
            $ageGroups = explode(',', $vaccine->age_group);

            foreach ($ageGroups as $groupAge) {
                $groupAge = trim($groupAge);
                $groupAgeMonths = $this->ageStringToMonths($groupAge);

                if ($groupAgeMonths !== null) {
                    $vaccineDose = [
                        'vaccine_id' => $vaccine->id,
                        'name' => $vaccine->name,
                        'description' => $vaccine->description,
                        'dose_age' => $groupAge,
                        'dose_age_months' => $groupAgeMonths,
                        'price' => $vaccine->price,
                    ];

                    if ($groupAgeMonths == $ageInMonths) {
                        $recommendedNow[] = $vaccineDose;
                    } elseif ($groupAgeMonths > $ageInMonths) {
                        $upcomingVaccines[] = $vaccineDose;
                    }
                }
            }
        }

        foreach ($recommendedNow as $vaccine) {
            VaccinationRecord::create([
                'child_id' => $child->id,
                'vaccine_id' => $vaccine['vaccine_id'],
                'appointment_id' => null,
                'dose_number' => 1,
                'notes' => null,
                'isTaken' => false,
                'recommended' => 'now',
                'when_to_take' => $vaccine['dose_age'],
                'next_vaccine_date' => null,
            ]);
        }

        foreach ($upcomingVaccines as $vaccine) {
            VaccinationRecord::create([
                'child_id' => $child->id,
                'vaccine_id' => $vaccine['vaccine_id'],
                'appointment_id' => null,
                'dose_number' => 1,
                'notes' => null,
                'isTaken' => false,
                'recommended' => 'upcoming',
                'when_to_take' => $vaccine['dose_age'],
                'next_vaccine_date' => null,
            ]);
        }
    }
}
