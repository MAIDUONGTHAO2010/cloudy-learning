<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(protected CategoryService $categoryService) {}

    public function index(Request $request)
    {
        if ($request->has('page')) {
            return response()->json($this->categoryService->listPaginated());
        }

        return response()->json($this->categoryService->list());
    }

    public function children(int $id)
    {
        return response()->json($this->categoryService->children($id));
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = $this->categoryService->create($request->validated());

        return response()->json($category, 201);
    }

    public function update(UpdateCategoryRequest $request, int $id)
    {
        $category = $this->categoryService->update($id, $request->validated());

        return response()->json($category);
    }

    public function destroy(int $id)
    {
        $this->categoryService->delete($id);

        return response()->noContent();
    }
}
