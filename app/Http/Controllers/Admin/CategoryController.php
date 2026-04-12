<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function index()
    {
        $categories = $this->categoryRepository->getCategoriesByAdmin();

        return response()->json($categories);
    }

    public function children(int $id)
    {
        $children = $this->categoryRepository->getChildrenByAdmin($id);

        return response()->json($children);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'order'       => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
            'parent_id'   => 'nullable|exists:categories,id',
        ]);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['name']));

        $category = $this->categoryRepository->create($data);

        // Reload with children_count for parent categories
        if (empty($data['parent_id'])) {
            $category->loadCount('children');
        }

        return response()->json($category, 201);
    }

    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'order'       => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['name']), $id);

        $category = $this->categoryRepository->update($data, $id);

        $category->loadCount('children');

        return response()->json($category);
    }

    public function destroy(int $id)
    {
        $this->categoryRepository->delete($id);

        return response()->json(['message' => 'Deleted successfully']);
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = \App\Models\Category::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (!$query->exists()) {
                break;
            }
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}
