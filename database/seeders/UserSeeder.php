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
                'first_name' => 'Nour',
                'last_name' => 'admin',
                'email' => 'nour@example.com',
                'phone' => '0936820776',
                'password' => Hash::make('Nour1234'),
                'role' => 'admin',
            ],
            // Secretary
            [
                'id' => 2,
                'first_name' => 'Sara',
                'last_name' => 'Secretary',
                'email' => 'sara@example.com',
                'phone' => '0990000002',
                'password' => Hash::make('Sara1234'),
                'role' => 'secretary',
            ],
            // Doctors
            [   //Cardiologist
                'id' => 3,
                'first_name' => 'Ahmed',
                'last_name' => 'Almasri',
                'email' => 'ahmed@example.com',
                'phone' => '0990000003',
                'password' => Hash::make('Ahmed1234'),
                'role' => 'doctor',
            ],
            [   //Cardiologist
                'id' => 4,
                'first_name' => 'Tarek',
                'last_name' => 'Khoury',
                'email' => 'tarek@example.com',
                'phone' => '0990000004',
                'password' => Hash::make('Tarek1234'),
                'role' => 'doctor',
            ],
            [   //Dentist
                'id' => 5,
                'first_name' => 'Lina',
                'last_name' => 'Kanaan',
                'email' => 'lina@example.com',
                'phone' => '0990000005',
                'password' => Hash::make('Lina1234'),
                'role' => 'doctor',
            ],
            [   //Dentist
                'id' => 6,
                'first_name' => 'Hala',
                'last_name' => 'Khatib',
                'email' => 'hala@example.com',
                'phone' => '0990000006',
                'password' => Hash::make('Hala1234'),
                'role' => 'doctor',
            ],
            [   //Hepatologist
                'id' => 7,
                'first_name' => 'Mohamed',
                'last_name' => 'Barakat',
                'email' => 'mohamed@example.com',
                'phone' => '0990000007',
                'password' => Hash::make('Mohamed1234'),
                'role' => 'doctor',
            ],
            [   //Gastroenterologist
                'id' => 8,
                'first_name' => 'Rawan',
                'last_name' => 'Barakat',
                'email' => 'rawan@example.com',
                'phone' => '0990000008',
                'password' => Hash::make('Rawan1234'),
                'role' => 'doctor',
            ],
            [   //Pulmonologist
                'id' => 9,
                'first_name' => 'Youssef',
                'last_name' => 'Fahmy',
                'email' => 'youssef@example.com',
                'phone' => '0990000009',
                'password' => Hash::make('Youssef1234'),
                'role' => 'doctor',
            ],
            [   //Psychiatrist
                'id' => 10,
                'first_name' => 'Amal',
                'last_name' => 'Haddad',
                'email' => 'amal@example.com',
                'phone' => '0990000010',
                'password' => Hash::make('Amal1234'),
                'role' => 'doctor',
            ],
            [   //Psychiatrist
                'id' => 11,
                'first_name' => 'Mona',
                'last_name' => 'Zein',
                'email' => 'mona@example.com',
                'phone' => '0990000011',
                'password' => Hash::make('Mona1234'),
                'role' => 'doctor',
            ],
            [   //Neurologist
                'id' => 12,
                'first_name' => 'Khaled',
                'last_name' => 'Darwish',
                'email' => 'khaled@example.com',
                'phone' => '0990000012',
                'password' => Hash::make('Khaled1234'),
                'role' => 'doctor',
            ],
            [   //Nephrologist
                'id' => 13,
                'first_name' => 'Dana',
                'last_name' => 'Haddad',
                'email' => 'dana@example.com',
                'phone' => '0990000013',
                'password' => Hash::make('Dana1234'),
                'role' => 'doctor',
            ],
            // Patients
            [
                'id' => 14,
                'first_name' => 'Naya',
                'last_name' => 'Taha',
                'email' => 'naya@example.com',
                'phone' => '0930536570',
                'password' => Hash::make('Naya1234'),
                'role' => 'patient',
            ],
            [
                'id' => 15,
                'first_name' => 'Maya',
                'last_name' => 'Jabari',
                'email' => 'maya@example.com',
                'phone' => '0990000014',
                'password' => Hash::make('Maya1234'),
                'role' => 'patient',
            ],
            [
                'id' => 16,
                'first_name' => 'Ali',
                'last_name' => 'Nassar',
                'email' => 'ali@example.com',
                'phone' => '0990000015',
                'password' => Hash::make('Ali1234'),
                'role' => 'patient',
            ],
            [
                'id' => 17,
                'first_name' => 'Rana',
                'last_name' => 'AbouZaid',
                'email' => 'rana@example.com',
                'phone' => '0990000016',
                'password' => Hash::make('Rana1234'),
                'role' => 'patient',
            ],
            [
                'id' => 18,
                'first_name' => 'Samer',
                'last_name' => 'Hammoud',
                'email' => 'samer@example.com',
                'phone' => '0990000017',
                'password' => Hash::make('Samer1234'),
                'role' => 'patient',
            ],
            [
                'id' => 19,
                'first_name' => 'John',
                'last_name' => 'Korn',
                'email' => 'john@example.com',
                'phone' => '0990000018',
                'password' => Hash::make('John1234'),
                'role' => 'patient',
            ],
            [
                'id' => 20,
                'first_name' => 'Ibrahim',
                'last_name' => 'Salha',
                'email' => 'ibrahim@example.com',
                'phone' => '0990000019',
                'password' => Hash::make('Ibrahim1234'),
                'role' => 'patient',
            ],
            [
                'id' => 21,
                'first_name' => 'Jessy',
                'last_name' => 'Abdo',
                'email' => 'jessy@example.com',
                'phone' => '0990000020',
                'password' => Hash::make('Jessy1234'),
                'role' => 'patient',
            ],
            // LabTech
            [
                'id' => 22,
                'first_name' => 'Walid',
                'last_name' => 'LabTech',
                'email' => 'walid@example.com',
                'phone' => '0990000021',
                'password' => Hash::make('Walid1234'),
                'role' => 'labtech',
            ],
        ]);
    }
}
