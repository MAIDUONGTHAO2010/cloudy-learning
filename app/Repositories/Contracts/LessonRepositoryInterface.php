<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

interface LessonRepositoryInterface extends RepositoryInterface
{
    public function getByCourse(int $courseId);

    public function reorder(array $items): void;
}
