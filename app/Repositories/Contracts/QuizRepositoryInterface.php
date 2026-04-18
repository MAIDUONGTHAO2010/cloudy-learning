<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

interface QuizRepositoryInterface extends RepositoryInterface
{
    public function getByLessonWithQuestions(int $lessonId): ?object;
}
