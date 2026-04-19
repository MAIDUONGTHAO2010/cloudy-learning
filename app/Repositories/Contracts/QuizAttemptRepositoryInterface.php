<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

interface QuizAttemptRepositoryInterface extends RepositoryInterface
{
    public function findByUserAndQuiz(int $userId, int $quizId): ?object;
}
