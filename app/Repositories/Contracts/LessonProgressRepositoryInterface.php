<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

interface LessonProgressRepositoryInterface extends RepositoryInterface
{
    public function findByUserAndLesson(int $userId, int $lessonId): ?object;
}
