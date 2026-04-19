<?php

namespace App\Http\Controllers;

use App\Enums\User\UserRole;
use App\Http\Requests\Course\EnrollCourseRequest;
use App\Http\Requests\Course\ReviewCourseEnrollmentRequest;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use App\Services\CourseService;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService,
        protected ProgressService $progressService,
    ) {}

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
            ->whereHas('courses', fn($q) => $q->where('is_active', true))
            ->orderBy('name')
            ->get(['id', 'name']);

        $tags = Tag::whereHas('courses', fn($q) => $q->where('is_active', true))
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

    public function show(Request $request, string $slug): JsonResponse
    {
        return response()->json(
            $this->courseService->findBySlug($slug, $request->user()?->id)
        );
    }

    public function enroll(EnrollCourseRequest $request, string $slug): JsonResponse
    {
        return response()->json(
            $this->courseService->enrollInCourse($slug, $request->user()->id)
        );
    }

    public function myCourses(Request $request): JsonResponse
    {
        $userId    = $request->user()->id;
        $paginator = $this->courseService->myCourses($userId);

        $courseIds   = collect($paginator->items())->pluck('id')->toArray();
        $progressMap = $this->progressService->getCoursesProgressMap($userId, $courseIds);

        foreach ($paginator->items() as $course) {
            $course->setAttribute('progress', $progressMap[$course->id] ?? 0);
        }

        return response()->json($paginator);
    }

    public function instructorRequests(Request $request): JsonResponse
    {
        return response()->json(
            $this->courseService->instructorRequests($request->user()->id)
        );
    }

    public function reviewEnrollment(ReviewCourseEnrollmentRequest $request, int $courseId, int $userId): JsonResponse
    {
        return response()->json(
            $this->courseService->reviewEnrollment($request->user()->id, $courseId, $userId, $request->validated())
        );
    }
}
