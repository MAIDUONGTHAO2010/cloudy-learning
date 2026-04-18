<?php

namespace App\Http\Controllers;

use App\Enums\User\UserRole;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(protected CourseService $courseService) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'category_id', 'instructor_id', 'tag']);

        return response()->json($this->courseService->listPublicPaginated($filters));
    }

    public function filterData(): JsonResponse
    {
        $categories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name']);

        $instructors = User::where('role', UserRole::INSTRUCTOR)
            ->whereHas('courses', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name']);

        $tags = Tag::whereHas('courses', fn ($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        return response()->json(compact('categories', 'instructors', 'tags'));
    }

    public function popular(): JsonResponse
    {
        return response()->json($this->courseService->listPopular());
    }

    public function newest(): JsonResponse
    {
        return response()->json($this->courseService->listNewest());
    }

    public function instructors(): JsonResponse
    {
        return response()->json($this->courseService->listTopInstructors());
    }

    public function show(string $slug): JsonResponse
    {
        return response()->json($this->courseService->findBySlug($slug));
    }
}
