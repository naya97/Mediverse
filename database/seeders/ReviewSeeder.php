<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use Carbon\Carbon;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $comments = [
            'The doctor was very attentive and explained everything clearly.',
            'I am satisfied with the treatment and the follow-up care.',
            'Good experience overall, but the waiting time was a bit long.',
            'The doctor listened to my concerns and gave helpful advice.',
            'Very professional and friendly service.',
            'Excellent care and clear instructions for recovery.',
            'The doctor made me feel comfortable and at ease.',
            'Highly knowledgeable and understanding.',
            'Great experience, would recommend to others.',
            'Treatment was effective and well-explained.'
        ];

        foreach (range(1, 50) as $i) {
            Review::create([
                'rate'       => rand(3, 5),
                'comment'    => $comments[array_rand($comments)],
                'created_at' => Carbon::now()->subDays(rand(0, 60)),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
