<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoReviewSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'staging'])) {
            return;
        }

        $parents = User::where('user_type', 'parent')->get();
        if ($parents->isEmpty()) {
            return;
        }

        $schools = School::where('status', 'active')->where('verification_status', 'verified')->get();
        if ($schools->isEmpty()) {
            return;
        }

        $reviewerTypes = ['parent', 'parent', 'parent', 'alumni', 'staff'];

        $sampleReviews = [
            ['title' => 'Excellent school with great teachers', 'body' => 'My child has thrived at this school. The teachers are dedicated and the environment is supportive. Strongly recommended for any parent looking for quality education.'],
            ['title' => 'Good academics, could improve facilities', 'body' => 'The academic standards are high and students perform well in national exams. The facilities could use some upgrades but overall a solid choice.'],
            ['title' => 'Wonderful school community', 'body' => 'The school has a strong sense of community. Both students and parents feel welcomed. The school events are well-organised and engaging.'],
        ];

        $ratings = [3, 4, 4, 5, 5, 4, 5];

        foreach ($schools as $school) {
            // 3 reviews per school
            for ($i = 0; $i < 3; $i++) {
                $parent = $parents->random();
                $template = $sampleReviews[$i % count($sampleReviews)];
                $rating = $ratings[array_rand($ratings)];

                Review::firstOrCreate(
                    ['school_id' => $school->id, 'user_id' => $parent->id],
                    [
                        'reviewer_type' => $reviewerTypes[array_rand($reviewerTypes)],
                        'rating' => $rating,
                        'title' => $template['title'],
                        'body' => $template['body'],
                        'academic_rating' => max(1, $rating - (random_int(0, 1))),
                        'facilities_rating' => max(1, $rating - (random_int(0, 1))),
                        'staff_rating' => min(5, $rating + (random_int(0, 1) ? 0 : 0)),
                        'environment_rating' => $rating,
                        'status' => 'approved',
                        'is_verified' => (bool) random_int(0, 1),
                        'helpful_count' => random_int(0, 25),
                        'created_at' => now()->subDays(random_int(1, 180)),
                    ]
                );
            }
        }
    }
}
