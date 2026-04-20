<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\StoreCourseReviewRequest;
use App\Http\Requests\Course\UpdateCourseReviewRequest;
use App\Services\CourseReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseReviewController extends Controller
{
    public function __construct(protected CourseReviewService $courseReviewService) {}

    public function index(int $courseId): JsonResponse
    {
        return response()->json($this->courseReviewService->listByCourse($courseId));
    }

    public function store(StoreCourseReviewRequest $request, int $courseId): JsonResponse
    {
        $review = $this->courseReviewService->store($courseId, $request->user()->id, $request->validated());

        return response()->json($review, 201);
    }

    public function update(UpdateCourseReviewRequest $request, int $courseId, int $reviewId): JsonResponse
    {
        $review = $this->courseReviewService->update($reviewId, $request->user()->id, $request->validated());

        return response()->json($review);
    }

    public function destroy(Request $request, int $courseId, int $reviewId): JsonResponse
    {
        $this->courseReviewService->destroy($reviewId, $request->user()->id);

        return response()->noContent();
    }
}
