<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Support\Str;

class CategoryService
{
    public function __construct(protected CategoryRepositoryInterface $categoryRepository) {}

    public function list()
    {
        return $this->categoryRepository->getCategoriesByAdmin();
    }

    public function listPublic()
    {
        return $this->categoryRepository->getPublic();
    }

    public function listPaginated()
    {
        return $this->categoryRepository->getPaginatedByAdmin();
    }

    public function children(int $parentId)
    {
        return $this->categoryRepository->getChildrenByAdmin($parentId);
    }

    public function create(array $data): mixed
    {
        $data['slug'] = $this->uniqueSlug(Str::slug($data['name']));

        $category = $this->categoryRepository->create($data);

        if (empty($data['parent_id'])) {
            $category->loadCount('children');
        }

        return $category;
    }

    public function update(int $id, array $data): mixed
    {
        $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $id);

        $category = $this->categoryRepository->update($data, $id);
        $category->loadCount('children');

        return $category;
    }

    public function delete(int $id): void
    {
        $this->categoryRepository->delete($id);
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = Category::where('slug', $slug);
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
}
