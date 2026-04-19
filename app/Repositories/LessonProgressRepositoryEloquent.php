<?php

namespace App\Repositories;

use App\Models\LessonProgress;
use App\Repositories\Contracts\LessonProgressRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class LessonProgressRepositoryEloquent extends BaseRepository implements LessonProgressRepositoryInterface
{
    public function model(): string
    {
        return LessonProgress::class;
    }

    public function findByUserAndLesson(int $userId, int $lessonId): ?object
    {
        return LessonProgress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();
    }
}
