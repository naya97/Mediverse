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
            'Alia'     => 'female',
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

        $father1 = $allPatients[0]; // أب لـ 3 أطفال
        $father2 = $allPatients[1]; // أب لـ طفل واحد

        $children = [
            ['first_name' => 'Adam',  'gender' => 'male',   'parent_id' => $father1->id],
            ['first_name' => 'Lina',  'gender' => 'female', 'parent_id' => $father1->id],
            ['first_name' => 'Omar',  'gender' => 'male',   'parent_id' => $father1->id],
            ['first_name' => 'Joud',  'gender' => 'female', 'parent_id' => $father2->id],
        ];

        foreach ($children as $child) {
            Patient::create([
                'first_name'      => $child['first_name'],
                'last_name'       => 'Child',
                'user_id'         => null,
                'birth_date'      => Carbon::now()->subMonths(rand(3, 4))->subDays(rand(0, 30)),
                'gender'          => $child['gender'],
                'blood_type'      => collect(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])->random(),
                'address'         => 'Same as parent',
                'wallet'          => 0,
                'parent_id'       => $child['parent_id'],
                'discount_points' => 0,
            ]);
        }
    }
}
