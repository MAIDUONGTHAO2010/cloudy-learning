<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;

class CourseReviewController extends Controller
{
    public function index(int $courseId)
    {
        $course = Course::findOrFail($courseId);

        $reviews = $course->reviews()
            ->with('user:id,name,email')
            ->latest()
            ->get()
            ->map(function ($review) {
                return [
                    'id'         => $review->id,
                    'rating'     => $review->rating,
                    'comment'    => $review->comment,
                    'created_at' => $review->created_at,
                    'user'       => $review->user ? [
                        'id'    => $review->user->id,
                        'name'  => $review->user->name,
                        'email' => $review->user->email,
                    ] : null,
                ];
            });

        return response()->json([
            'course'       => [
                'id'           => $course->id,
                'title'        => $course->title,
                'avg_rating'   => round($course->reviews()->avg('rating') ?? 0, 1),
                'reviews_count' => $course->reviews()->count(),
            ],
            'reviews' => $reviews,
        ]);
    }
}
