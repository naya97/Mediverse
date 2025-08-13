<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        DB::table('users')->insert([
            // Admin
            [
                'id' => 1,
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'phone' => '0936820776',
                'password' => Hash::make('Nour1234'),
                'role' => 'admin',
            ],
            // Secretary
            [
                'id' => 2,
                'first_name' => 'Sara',
                'last_name' => 'Secretary',
                'email' => 'sara.secretary@example.com',
                'phone' => '0990000002',
                'password' => Hash::make('Secretary1234'),
                'role' => 'secretary',
            ],
            // Doctors
            [
                'id' => 3,
                'first_name' => 'Ahmed',
                'last_name' => 'Cardiologist',
                'email' => 'ahmed.cardiologist@example.com',
                'phone' => '0990000003',
                'password' => Hash::make('Doctor1234'),
                'role' => 'doctor',
            ],
            [
                'id' => 4,
                'first_name' => 'Lina',
                'last_name' => 'Dentist',
                'email' => 'lina.dentist@example.com',
                'phone' => '0990000004',
                'password' => Hash::make('Doctor1234'),
                'role' => 'doctor',
            ],
            [
                'id' => 5,
                'first_name' => 'Mohamed',
                'last_name' => 'Hepatologist',
                'email' => 'mohamed.hepatologist@example.com',
                'phone' => '0990000005',
                'password' => Hash::make('Doctor1234'),
                'role' => 'doctor',
            ],
            [
                'id' => 6,
                'first_name' => 'Sara',
                'last_name' => 'Gastroenterologist',
                'email' => 'sara.gastro@example.com',
                'phone' => '0990000006',
                'password' => Hash::make('Doctor1234'),
                'role' => 'doctor',
            ],
            [
                'id' => 7,
                'first_name' => 'Youssef',
                'last_name' => 'Pulmonologist',
                'email' => 'youssef.pulmo@example.com',
                'phone' => '0990000007',
                'password' => Hash::make('Doctor1234'),
                'role' => 'doctor',
            ],
            [
                'id' => 8,
                'first_name' => 'Mona',
                'last_name' => 'Psychiatrist',
                'email' => 'mona.psychiatrist@example.com',
                'phone' => '0990000008',
                'password' => Hash::make('Doctor1234'),
                'role' => 'doctor',
            ],
            [
                'id' => 9,
                'first_name' => 'Khaled',
                'last_name' => 'Neurologist',
                'email' => 'khaled.neuro@example.com',
                'phone' => '0990000009',
                'password' => Hash::make('Doctor1234'),
                'role' => 'doctor',
            ],
            [
                'id' => 10,
                'first_name' => 'Dana',
                'last_name' => 'Nephrologist',
                'email' => 'dana.nephro@example.com',
                'phone' => '0990000010',
                'password' => Hash::make('Doctor1234'),
                'role' => 'doctor',
            ],
            // Patients
            [
                'id' => 11,
                'first_name' => 'Naya',
                'last_name' => 'Patient',
                'email' => 'naya.patient@example.com',
                'phone' => '0930536570',
                'password' => Hash::make('Naya1234'),
                'role' => 'patient',
            ],
            [
                'id' => 12,
                'first_name' => 'Maya',
                'last_name' => 'Patient',
                'email' => 'maya.patient@example.com',
                'phone' => '0990000012',
                'password' => Hash::make('Patient1234'),
                'role' => 'patient',
            ],
            [
                'id' => 13,
                'first_name' => 'Ali',
                'last_name' => 'Patient',
                'email' => 'ali.patient@example.com',
                'phone' => '0990000013',
                'password' => Hash::make('Patient1234'),
                'role' => 'patient',
            ],
        ]);
    }
}
