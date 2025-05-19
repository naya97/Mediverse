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
            ClinicSeeder::class,
            DoctorSeeder::class,
            //AppointmentSeeder::class,
            LabTechSeeder::class,
        ]);
        User::factory()->create([
            'first_name' => 'Test User',
            'password' => Hash::make('Nour1234'),
            'phone' => '0936820776',
            'role' => 'admin',
        ]);
    }
}
