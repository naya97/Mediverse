<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VaccinesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('vaccines')->insert([
            [
                'name' => 'BCG',
                'description' => 'Bacillus Calmette-Guérin vaccine for tuberculosis (TB)',
                'age_group' => 'At birth',
                'recommended_doses' => 1,
                'price' => 15.00, // سعر الجرعة الواحدة
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Hepatitis B',
                'description' => 'Hepatitis B vaccine',
                'age_group' => 'At birth, 2 months, 5 months, 7 months',
                'recommended_doses' => 4,
                'price' => 25.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Polio (OPV/IPV)',
                'description' => 'Oral or Inactivated Polio Vaccine',
                'age_group' => 'At birth, 2 months, 5 months, 7 months, 12 months, 6 years',
                'recommended_doses' => 6,
                'price' => 18.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Pentavalent (DTP + Hib)',
                'description' => 'DTP + Haemophilus influenzae type b vaccine',
                'age_group' => '2 months, 5 months, 7 months, 18 months',
                'recommended_doses' => 4,
                'price' => 35.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'PCV (Pneumococcal Conjugate Vaccine)',
                'description' => 'Pneumococcal vaccine',
                'age_group' => '2 months, 5 months, 7 months',
                'recommended_doses' => 3,
                'price' => 35.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Rotavirus',
                'description' => 'Rotavirus vaccine',
                'age_group' => '2 months, 5 months, 7 months',
                'recommended_doses' => 3,
                'price' => 28.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Influenza',
                'description' => 'Influenza vaccine',
                'age_group' => '7 months, 12 months',
                'recommended_doses' => 2,
                'price' => 30.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'MMR (Measles, Mumps, Rubella)',
                'description' => 'Measles, Mumps, Rubella vaccine',
                'age_group' => '12 months, 18 months',
                'recommended_doses' => 2,
                'price' => 40.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Varicella',
                'description' => 'Chickenpox vaccine',
                'age_group' => '12 months, 18 months',
                'recommended_doses' => 2,
                'price' => 45.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Hepatitis A',
                'description' => 'Hepatitis A vaccine',
                'age_group' => '12 months, 18 months',
                'recommended_doses' => 2,
                'price' => 33.50,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Vitamin A',
                'description' => 'Vitamin A supplement',
                'age_group' => '12 months, 18 months',
                'recommended_doses' => 2,
                'price' => 5.00,   
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'DT (Diphtheria-Tetanus)',
                'description' => 'Diphtheria and Tetanus vaccine booster',
                'age_group' => '6 years, 12 years',
                'recommended_doses' => 2,
                'price' => 20.00,  
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Meningococcal',
                'description' => 'Meningococcal vaccine',
                'age_group' => '6 years',
                'recommended_doses' => 1,
                'price' => 50.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    
    }
}
