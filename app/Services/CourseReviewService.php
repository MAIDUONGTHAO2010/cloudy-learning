<?php

namespace App\Services;

use App\Enums\Course\EnrollmentStatus;
use App\Models\Course;
use App\Models\CourseReview;
use App\Repositories\Contracts\CourseReviewRepositoryInterface;
use Illuminate\Http\Exceptions\HttpResponseException;

class CourseReviewService
{
    public function __construct(protected CourseReviewRepositoryInterface $courseReviewRepository) {}

    public function listByCourse(int $courseId): array
    {
        $reviews = $this->courseReviewRepository->getReviewsByCourse($courseId);

        $avgRating = $reviews->avg('rating') ?? 0;

        return [
            'reviews' => $reviews->map(fn($r) => [
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

    public function store(int $courseId, int $userId, array $data): CourseReview
    {
        $course = Course::findOrFail($courseId);

        // Only approved enrolled students can review
        $enrollment = $course->students()
            ->where('user_id', $userId)
            ->first();

        if (! $enrollment || (int) $enrollment->pivot->status !== EnrollmentStatus::APPROVED) {
            throw new HttpResponseException(
                response()->json(['message' => 'You must be enrolled and approved to review this course.'], 403)
            );
        }

        $existing = CourseReview::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            throw new HttpResponseException(
                response()->json(['message' => 'You have already reviewed this course. Edit your existing review.'], 422)
            );
        }

        return CourseReview::create([
            'course_id' => $courseId,
            'user_id'   => $userId,
            'rating'    => $data['rating'],
            'comment'   => $data['comment'] ?? null,
        ]);
    }

    public function update(int $reviewId, int $userId, array $data): CourseReview
    {
        $review = CourseReview::where('id', $reviewId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $review->update([
            'rating'  => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return $review->fresh();
    }

    public function destroy(int $reviewId, int $userId): void
    {
        $review = CourseReview::where('id', $reviewId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $review->delete();
    }

    public function getMyReview(int $courseId, int $userId): ?CourseReview
    {
        return CourseReview::where('course_id', $courseId)
            ->where('user_id', $userId)
            ->first();
    }
}
