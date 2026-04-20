<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Course\PresignCourseThumbnailUploadRequest;
use App\Http\Requests\Admin\Course\ReorderCourseRequest;
use App\Http\Requests\Admin\Course\StoreCourseRequest;
use App\Http\Requests\Admin\Course\UpdateCourseRequest;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function __construct(protected CourseService $courseService) {}

    public function index(Request $request)
    {
        return response()->json($this->courseService->listPaginated());
    }

    public function show(int $id)
    {
        return response()->json($this->courseService->show($id));
    }

    public function store(StoreCourseRequest $request)
    {
        $course = $this->courseService->create($request->validated());

        return response()->json($course, 201);
    }

    public function update(UpdateCourseRequest $request, int $id)
    {
        $course = $this->courseService->update($id, $request->validated());

        return response()->json($course);
    }

    public function destroy(int $id)
    {
        $this->courseService->delete($id);

        return response()->noContent();
    }

    public function presignThumbnailUpload(PresignCourseThumbnailUploadRequest $request)
    {
        return response()->json(
            $this->courseService->presignThumbnailUpload($request->validated())
        );
    }

    public function reorder(ReorderCourseRequest $request)
    {
        $this->courseService->reorder($request->validated()['items']);

        return response()->json(['message' => 'Reordered successfully']);
    }
}
