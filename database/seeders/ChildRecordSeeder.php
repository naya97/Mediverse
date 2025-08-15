<?php

namespace Database\Seeders;

use App\Models\ChildRecord;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChildRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $children = Patient::whereNotNull('parent_id')->get();
        $doctors = Doctor::all();
        $doctorIds = $doctors->pluck('id')->toArray();
        
        foreach($children as $child) {
            $record = ChildRecord::create([
                'child_id' => $child->id,
                'doctor_id' => $doctorIds[array_rand($doctorIds)],
                'last_visit_date' => now()->subDays(rand(10, 90)),
                'next_visit_date' => now()->addDays(rand(30, 180)),
                'height_cm' =>  rand(50, 120),
                'weight_kg' => rand(5, 25),
                'head_circumference_cm' => rand(35, 55),
                'growth_notes' => 'Growth is normal.',
                'developmental_observations' => 'Responds to sounds, moves normally, interacts with family.',
                'allergies' => rand(0, 1) ? 'from milk' : 'None',
                'doctor_notes' => 'Recommendation for monthly follow-up and a comprehensive checkup after one month.',
                'feeding_type' => ['natural', 'formula', 'mixed'][array_rand(['natural', 'formula', 'mixed'])],
            ]);
        }
    }
}
