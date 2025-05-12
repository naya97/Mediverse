<?php

namespace Database\Seeders;

use App\Models\Appointment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Appointment::create([
        //     'patient_id' => 1,
        //     'schedule_id' => 1,
        //     'timeSelected' => '10:00',
        //     'reservation_date' => '2025-05-05',
        //     'reservation_hour' => '10:15',
        // ]);

        // Appointment::create([
        //     'patient_id' => 1,
        //     'schedule_id' => 1,
        //     'timeSelected' => '10:00',
        //     'reservation_date' => '2025-05-05',
        //     'reservation_hour' => '10:30',
        // ]);

        Appointment::create([
            'patient_id' => 1,
            'schedule_id' => 2,
            'timeSelected' => '10:00',
            'reservation_date' => '2025-05-05',
            'reservation_hour' => '10:30',
        ]);


    }
}
