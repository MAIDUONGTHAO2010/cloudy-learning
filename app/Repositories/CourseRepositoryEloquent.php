<?php

namespace App\Repositories;

use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class CourseRepositoryEloquent extends BaseRepository implements CourseRepositoryInterface
{
    public function model(): string
    {
        return Course::class;
    }

    public function getAllWithRelations()
    {
        return Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('order')
            ->get();
    }

    public function getPaginatedWithRelations()
    {
        return Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('order')
            ->paginate(10);
    }

    public function getWithRelations(int $id)
    {
        return Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->findOrFail($id);
    }

    public function reorder(array $items): void
    {
        foreach ($items as $item) {
            Course::where('id', $item['id'])->update(['order' => $item['order']]);
        }
    }

    public function getPublicPaginated(array $filters = [], int $perPage = 12)
    {
        $query = Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->where('is_active', true);

        if (! empty($filters['search'])) {
            $query->where('title', 'ilike', '%'.$filters['search'].'%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['instructor_id'])) {
            $query->where('user_id', $filters['instructor_id']);
        }

        if (! empty($filters['tag'])) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $filters['tag']));
        }

        return $query->orderBy('order')->paginate($perPage);
    }

    public function getPopular(int $limit = 8)
    {
        return Course::with(['instructor:id,name', 'category:id,name'])
            ->withCount(['lessons', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->where('is_active', true)
            ->orderByDesc('reviews_count')
            ->limit($limit)
            ->get();
    }

    public function getNewest(int $limit = 8)
    {
        return Course::with(['instructor:id,name', 'category:id,name'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->where('is_active', true)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug)
    {
        return Course::with([
            'instructor:id,name',
            'category:id,name',
            'tags:id,name,slug',
            'lessons' => fn ($q) => $q->where('is_active', true)->orderBy('order'),
        ])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
