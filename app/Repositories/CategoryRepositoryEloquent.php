<?php

namespace App\Repositories;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class CategoryRepositoryEloquent.
 *
 * @package namespace App\Repositories;
 */
class CategoryRepositoryEloquent extends BaseRepository implements CategoryRepositoryInterface
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return Category::class;
    }

    public function getCategoriesByAdmin()
    {
        return Category::query()
            ->whereNull('parent_id')
            ->withCount('children')
            ->orderByDesc('order')
            ->orderByDesc('id')
            ->get();
    }

    public function getChildrenByAdmin(int $parentId)
    {
        return Category::query()
            ->where('parent_id', $parentId)
            ->orderByDesc('order')
            ->orderByDesc('id')
            ->get();
    }
}
