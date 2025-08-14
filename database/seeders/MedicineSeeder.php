<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Prescription;
use App\Models\Medicine;
use Carbon\Carbon;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $prescriptions = Prescription::all();

        if ($prescriptions->isEmpty()) {
            $this->command->warn('⚠ لا توجد وصفات لإضافة الأدوية.');
            return;
        }

        $medicineNames = ['Paracetamol', 'Ibuprofen', 'Amoxicillin', 'Metformin', 'Omeprazole', 'Atorvastatin', 'Amlodipine', 'Cefixime', 'Levothyroxine', 'Azithromycin'];
        $doses = ['1 pill', '2 pills', '1 capsule', '2 capsules', '5 ml'];
        $frequencies = ['Once a day', 'Twice a day', 'Three times a day', 'Before bed'];
        $strengths = ['250mg', '500mg', '100mg', '50mg', '5mg'];
        $untilOptions = ['5 days', '7 days', '10 days', '2 weeks', '1 month'];
        $whenToTake = ['Before food', 'After food', 'With water', 'Empty stomach'];

        foreach ($prescriptions as $prescription) {
            $numMedicines = rand(1, 5);

            for ($i = 0; $i < $numMedicines; $i++) {
                Medicine::create([
                    'prescription_id' => $prescription->id,
                    'name'            => $medicineNames[array_rand($medicineNames)],
                    'dose'            => $doses[array_rand($doses)],
                    'frequency'       => $frequencies[array_rand($frequencies)],
                    'strength'        => $strengths[array_rand($strengths)],
                    'until'           => $untilOptions[array_rand($untilOptions)],
                    'whenToTake'      => $whenToTake[array_rand($whenToTake)],
                    'note'            => fake()->sentence(rand(3, 7)),
                    'created_at'      => Carbon::now()->subDays(rand(0, 30)),
                    'updated_at'      => Carbon::now(),
                ]);
            }
        }
    }
}
