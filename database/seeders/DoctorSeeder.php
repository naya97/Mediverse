<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DoctorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'first_name' => 'Judy',
            'last_name' => 'Omran',
            'email' => 'Judy@gmail.com',
            'phone' => '0937582883',
            'password' => Hash::make('Judy1234'),
            'role' => 'doctor',
        ]);
        Doctor::create([
            'first_name' => 'Judy',
            'last_name' => 'Omran',
            'user_id' => $user->id,
            'clinic_id' => 1,
        ]);
    }
}
