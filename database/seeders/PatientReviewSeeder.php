<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PatientReview;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Review;
use Carbon\Carbon;

class PatientReviewSeeder extends Seeder
{
    public function run(): void
    {
        $patients = Patient::all();
        $doctors  = Doctor::all();
        $reviews  = Review::all();

        if ($patients->isEmpty() || $doctors->isEmpty() || $reviews->isEmpty()) {
            $this->command->warn('⚠ Make sure patients, doctors, and reviews exist before seeding patient_reviews.');
            return;
        }

        foreach ($reviews as $review) {
            PatientReview::create([
                'patient_id' => $patients->random()->id,
                'doctor_id'  => $doctors->random()->id,
                'review_id'  => $review->id,
                'created_at' => Carbon::now()->subDays(rand(0, 60)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
