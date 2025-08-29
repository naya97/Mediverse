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
        $name = ['The Central Pharmacy', 'Soneege Pharmacy', 'Syria Phramcy', 'Alsabe3 Bahrat Pharmacy', 'AlShefaa Pharmacy', 'Alshahabandar Pharmacy', 'Alaabed Pharmcy', 'Alnabesi Pharmacy', 'Mohanad Pharmacy', 'Alzahrawy Pharmacy'];
        $latitude = [33.52080, 33.52030992407996, 33.521138949049394, 33.52348620486537, 33.52651550957011, 33.5240023765805, 33.51986828553535, 33.50837679206576, 33.52982366938459, 33.48134726849079,];
        $longitude = [36.29662, 36.29548171516601, 36.297304764125656, 36.29330658606735, 36.29020932419122, 36.29281738220953, 36.294888982210445, 36.258948812385505, 36.22543218098715, 36.2878479];

        for ($i = 0; $i < 10; $i++) {
            Pharmacy::create([
                'name'        => $name[$i],
                'location'    => $faker->address,
                'start_time'  => $faker->time('H:i'),    // مثل 08:00
                'finish_time' => $faker->time('H:i'),    // مثل 22:00
                'phone'       => $faker->phoneNumber,
                'latitude'    => $latitude[$i],
                'longitude'   => $longitude[$i],
            ]);
        }
    }
}
