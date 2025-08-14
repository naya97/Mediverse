<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $patientUsers = User::where('role', 'patient')->get();
        $allPatients = [];

        $genderMap = [
            'Naya'    => 'female',
            'Maya'    => 'female',
            'Rana'    => 'female',
            'Jessy'   => 'female',
            'Ali'     => 'male',
            'Samer'   => 'male',
            'John'    => 'male',
            'Ibrahim' => 'male',
        ];

        foreach ($patientUsers as $user) {
            $allPatients[] = Patient::create([
                'first_name'     => $user->first_name,
                'last_name'      => $user->last_name,
                'user_id'        => $user->id,
                'birth_date'     => Carbon::now()->subYears(rand(1, 80))->subDays(rand(0, 365)),
                'gender'         => $genderMap[$user->first_name] ?? 'male', // إذا الاسم غير موجود، نعتبره ذكر
                'blood_type'     => collect(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->random(),
                'address'        => 'Unknown Address',
                'wallet'         => rand(0, 1000),
                'parent_id'      => null,
                'discount_points' => rand(0, 100),
            ]);
        }

        if (count($allPatients) >= 4) {
            $parent = collect($allPatients)->random();
            $childrenCandidates = collect($allPatients)->filter(fn($p) => $p->id !== $parent->id && $p->parent_id === null);

            if ($childrenCandidates->count() >= 3) {
                $children = $childrenCandidates->random(3)->values(); // 3 أطفال

                $agesInMonths = [8, 36, 72]; // 8 شهور، 3 سنين، 6 سنين

                foreach ($children as $i => $child) {
                    $child->update([
                        'parent_id'  => $parent->id,
                        'birth_date' => Carbon::now()->subMonths($agesInMonths[$i]),
                        'wallet' => 0,
                    ]);
                }
            }
        }
    }
}
