<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Lesson\PresignLessonVideoUploadRequest;
use App\Http\Requests\Admin\Lesson\ReorderLessonRequest;
use App\Http\Requests\Admin\Lesson\StoreLessonRequest;
use App\Http\Requests\Admin\Lesson\UpdateLessonRequest;
use App\Services\LessonService;

class LessonController extends Controller
{
    public function __construct(protected LessonService $lessonService) {}

    public function index(int $courseId)
    {
        return response()->json($this->lessonService->listByCourse($courseId));
    }

    public function store(StoreLessonRequest $request, int $courseId)
    {
        return response()->json(
            $this->lessonService->create($courseId, $request->validated()),
            201
        );
    }

    public function update(UpdateLessonRequest $request, int $id)
    {
        return response()->json($this->lessonService->update($id, $request->validated()));
    }

    public function destroy(int $id)
    {
        $this->lessonService->delete($id);

        return response()->noContent();
    }

    public function presignVideoUpload(PresignLessonVideoUploadRequest $request)
    {
        return response()->json(
            $this->lessonService->presignVideoUpload($request->validated())
        );
    }

    public function reorder(ReorderLessonRequest $request, int $courseId)
    {
        $this->lessonService->reorder($courseId, $request->validated()['items']);

        return response()->json(['message' => 'Reordered successfully']);
    }
}
