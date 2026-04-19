<?php

namespace App\Repositories;

use App\Models\QuizAttempt;
use App\Repositories\Contracts\QuizAttemptRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class QuizAttemptRepositoryEloquent extends BaseRepository implements QuizAttemptRepositoryInterface
{
    public function model(): string
    {
        return QuizAttempt::class;
    }

    public function findByUserAndQuiz(int $userId, int $quizId): ?object
    {
        return QuizAttempt::where('user_id', $userId)
            ->where('quiz_id', $quizId)
            ->first();
    }
}
