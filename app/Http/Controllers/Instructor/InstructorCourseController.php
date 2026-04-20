<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Course\PresignCourseThumbnailUploadRequest;
use App\Http\Requests\Instructor\StoreCourseRequest;
use App\Http\Requests\Instructor\UpdateCourseRequest;
use App\Services\CourseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InstructorCourseController extends Controller
{
    public function __construct(protected CourseService $courseService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->courseService->listInstructorCourses($request->user()->id)
        );
    }

    public function store(StoreCourseRequest $request): JsonResponse
    {
        $course = $this->courseService->createForInstructor(
            $request->user()->id,
            $request->validated()
        );

        return response()->json($course, 201);
    }

    public function update(UpdateCourseRequest $request, int $id): JsonResponse
    {
        $course = $this->courseService->updateForInstructor(
            $request->user()->id,
            $id,
            $request->validated()
        );

        return response()->json($course);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->courseService->deleteForInstructor($request->user()->id, $id);

        return response()->noContent();
    }

    public function students(Request $request, int $id): JsonResponse
    {
        return response()->json(
            $this->courseService->getStudentsForCourse($request->user()->id, $id)
        );
    }

    public function removeStudent(Request $request, int $id, int $userId): JsonResponse
    {
        $this->courseService->removeStudentFromCourse($request->user()->id, $id, $userId);

        return response()->noContent();
    }

    public function addStudent(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        return response()->json(
            $this->courseService->addStudentToCourse($request->user()->id, $id, $request->email)
        );
    }

    public function presignThumbnail(PresignCourseThumbnailUploadRequest $request): JsonResponse
    {
        return response()->json(
            $this->courseService->presignThumbnailUpload($request->validated())
        );
    }
}
