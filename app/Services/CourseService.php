<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Tag;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Str;

class CourseService
{
    public function __construct(
        protected CourseRepositoryInterface $courseRepository,
        protected UserRepositoryInterface $userRepository,
    ) {}

    public function list()
    {
        return $this->courseRepository->getAllWithRelations();
    }

    public function listPaginated()
    {
        return $this->courseRepository->getPaginatedWithRelations();
    }

    public function listPublicPaginated(array $filters = [], int $perPage = 12)
    {
        return $this->courseRepository->getPublicPaginated($filters, $perPage);
    }

    public function listPopular(int $limit = 8)
    {
        return $this->courseRepository->getPopular($limit);
    }

    public function listNewest(int $limit = 8)
    {
        return $this->courseRepository->getNewest($limit);
    }

    public function findBySlug(string $slug)
    {
        return $this->courseRepository->findBySlug($slug);
    }

    public function listTopInstructors(int $limit = 6)
    {
        return $this->userRepository->getTopInstructors($limit);
    }

    public function show(int $id)
    {
        return $this->courseRepository->getWithRelations($id);
    }

    public function create(array $data): mixed
    {
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));

        $course = $this->courseRepository->create($data);
        $course->tags()->sync($this->resolveTagIds($tags));

        $course->load(['instructor:id,name', 'category:id,name', 'tags:id,name,slug']);
        $course->loadCount(['lessons', 'reviews']);
        $course->loadAvg('reviews', 'rating');

        return $course;
    }

    public function update(int $id, array $data): mixed
    {
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $id);

        $course = $this->courseRepository->update($data, $id);
        $course->tags()->sync($this->resolveTagIds($tags));

        $course->load(['instructor:id,name', 'category:id,name', 'tags:id,name,slug']);
        $course->loadCount(['lessons', 'reviews']);
        $course->loadAvg('reviews', 'rating');

        return $course;
    }

    public function delete(int $id): void
    {
        $this->courseRepository->delete($id);
    }

    public function reorder(array $items): void
    {
        $this->courseRepository->reorder($items);
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = Course::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) {
                break;
            }
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }

    private function resolveTagIds(array $tagNames): array
    {
        return collect($tagNames)
            ->filter()
            ->map(function (string $name) {
                $slug = Str::slug($name);

                return Tag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => trim($name), 'slug' => $slug]
                )->id;
            })
            ->toArray();
    }
}
