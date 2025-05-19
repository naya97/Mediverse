<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class LabTechSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'email' => 'Lina@gmail.com',
            'phone' => '0937559883',
            'password' => Hash::make('Lina1234'),
            'role' => 'labtech',
        ]);
    }
}
