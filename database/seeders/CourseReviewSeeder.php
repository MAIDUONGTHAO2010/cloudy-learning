<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class CourseReviewSeeder extends Seeder
{
    public function run(): void
    {
        $reviews = [
            [
                'rating' => 5,
                'comment' => 'Excellent course, clear and easy to understand. I applied the knowledge directly to a real-world project!',
            ],
            [
                'rating' => 4,
                'comment' => 'Great content and enthusiastic instructor. A few more practice exercises would make it even better.',
            ],
            [
                'rating' => 5,
                'comment' => 'Outstanding! I learned so many useful things from this course.',
            ],
            [
                'rating' => 3,
                'comment' => 'The content is decent but the pace is a bit fast, making it hard to keep up for beginners.',
            ],
            [
                'rating' => 4,
                'comment' => 'Very useful course with comprehensive materials. Recommended for anyone who wants to learn quickly.',
            ],
            [
                'rating' => 5,
                'comment' => 'Extremely in-depth and practical. The instructor explains every step very clearly.',
            ],
            [
                'rating' => 2,
                'comment' => 'Expected more. The content is still shallow and needs more real-world examples.',
            ],
            [
                'rating' => 4,
                'comment' => 'I feel much more confident after finishing this course. Thank you, instructor!',
            ],
        ];

        $courses = Course::all();
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($courses as $course) {
            // Each course receives 3–5 random reviews from different users
            $usedUserIds = [];
            $reviewSample = collect($reviews)->shuffle()->take(rand(3, 5));

            foreach ($reviewSample as $review) {
                // Select a user who hasn't reviewed this course yet
                $user = $users->whereNotIn('id', $usedUserIds)->first();

                if (! $user) {
                    break;
                }

                $usedUserIds[] = $user->id;

                CourseReview::create([
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'rating' => $review['rating'],
                    'comment' => $review['comment'],
                ]);
            }
        }
    }
}
