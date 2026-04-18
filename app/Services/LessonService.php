<?php

namespace App\Services;

use App\Models\Lesson;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\LessonRepositoryInterface;
use Illuminate\Support\Str;

class LessonService
{
    public function __construct(
        protected LessonRepositoryInterface $lessonRepository,
        protected CourseRepositoryInterface $courseRepository,
    ) {}

    public function listByCourse(int $courseId): array
    {
        $course = $this->courseRepository->find($courseId);
        $lessons = $this->lessonRepository->getByCourse($courseId);

        return ['course' => $course, 'lessons' => $lessons];
    }

    public function create(int $courseId, array $data): mixed
    {
        $this->courseRepository->find($courseId); // throws ModelNotFoundException if missing

        $data['course_id'] = $courseId;
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));

        return $this->lessonRepository->create($data);
    }

    public function update(int $id, array $data): mixed
    {
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $id);

        return $this->lessonRepository->update($data, $id);
    }

    public function delete(int $id): void
    {
        $this->lessonRepository->delete($id);
    }

    public function reorder(int $courseId, array $items): void
    {
        $this->lessonRepository->reorder($items);
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $exists = Lesson::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists();

            if (! $exists) {
                break;
            }

            $slug = $original.'-'.$count++;
        }

        return $slug;
    }
}
