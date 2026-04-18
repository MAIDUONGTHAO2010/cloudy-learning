<?php

namespace App\Services;

use App\Repositories\Contracts\CourseReviewRepositoryInterface;

class CourseReviewService
{
    public function __construct(protected CourseReviewRepositoryInterface $courseReviewRepository) {}

    public function listByCourse(int $courseId): array
    {
        $reviews = $this->courseReviewRepository->getReviewsByCourse($courseId);

        $avgRating = $reviews->avg('rating') ?? 0;

        return [
            'reviews' => $reviews->map(fn ($r) => [
                'id' => $r->id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at,
                'user' => $r->user ? [
                    'id' => $r->user->id,
                    'name' => $r->user->name,
                    'email' => $r->user->email,
                ] : null,
            ]),
            'avg_rating' => round($avgRating, 1),
            'reviews_count' => $reviews->count(),
        ];
    }
}
