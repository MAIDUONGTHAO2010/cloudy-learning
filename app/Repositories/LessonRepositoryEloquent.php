<?php

namespace App\Repositories;

use App\Models\Lesson;
use App\Repositories\Contracts\LessonRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class LessonRepositoryEloquent extends BaseRepository implements LessonRepositoryInterface
{
    public function model(): string
    {
        return Lesson::class;
    }

    public function getByCourse(int $courseId)
    {
        return Lesson::where('course_id', $courseId)->orderBy('order')->get();
    }

    public function reorder(array $items): void
    {
        foreach ($items as $item) {
            Lesson::where('id', $item['id'])->update(['order' => $item['order']]);
        }
    }
}
