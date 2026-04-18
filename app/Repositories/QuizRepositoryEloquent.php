<?php

namespace App\Repositories;

use App\Models\Quiz;
use App\Repositories\Contracts\QuizRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class QuizRepositoryEloquent extends BaseRepository implements QuizRepositoryInterface
{
    public function model(): string
    {
        return Quiz::class;
    }

    public function getByLessonWithQuestions(int $lessonId): ?object
    {
        return Quiz::where('lesson_id', $lessonId)
            ->with(['questions' => function ($q) {
                $q->orderBy('order')->with(['options' => fn ($o) => $o->orderBy('label')]);
            }])
            ->first();
    }
}
