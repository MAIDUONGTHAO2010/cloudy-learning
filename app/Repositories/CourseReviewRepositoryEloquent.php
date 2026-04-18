<?php

namespace App\Repositories;

use App\Models\CourseReview;
use App\Repositories\Contracts\CourseReviewRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class CourseReviewRepositoryEloquent extends BaseRepository implements CourseReviewRepositoryInterface
{
    public function model(): string
    {
        return CourseReview::class;
    }

    public function getReviewsByCourse(int $courseId)
    {
        return CourseReview::where('course_id', $courseId)
            ->with('user:id,name,email')
            ->latest()
            ->get();
    }
}
