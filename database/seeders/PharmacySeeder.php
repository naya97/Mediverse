<?php

namespace Database\Seeders;

use App\Models\Pharmacy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PharmacySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // نولد مثلاً 10 صيدليات
        for ($i = 0; $i < 10; $i++) {
            Pharmacy::create([
                'name'        => $faker->company . ' Pharmacy',
                'location'    => $faker->address,
                'start_time'  => $faker->time('H:i'),    // مثل 08:00
                'finish_time' => $faker->time('H:i'),    // مثل 22:00
                'phone'       => $faker->phoneNumber,
                'latitude'    => $faker->latitude(25, 37),    // نطاق تقريبي لمنطقة الشرق الأوسط
                'longitude'   => $faker->longitude(35, 45),
            ]);
        }
    }
}
