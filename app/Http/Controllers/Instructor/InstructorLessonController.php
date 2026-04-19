<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Lesson\PresignLessonVideoUploadRequest;
use App\Http\Requests\Admin\Lesson\StoreLessonRequest;
use App\Http\Requests\Admin\Lesson\UpdateLessonRequest;
use App\Http\Requests\Admin\Quiz\UpdateQuizQuestionRequest;
use App\Models\Course;
use App\Services\LessonService;
use App\Services\QuizService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstructorLessonController extends Controller
{
    public function __construct(
        protected LessonService $lessonService,
        protected QuizService $quizService,
    ) {}

    // ── Lessons ───────────────────────────────────────────────────────────────

    public function index(Request $request, string $courseId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json($this->lessonService->listByCourse((int) $courseId));
    }

    public function store(StoreLessonRequest $request, string $courseId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json(
            $this->lessonService->create((int) $courseId, $request->validated()),
            201
        );
    }

    public function update(UpdateLessonRequest $request, string $courseId, string $id): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json($this->lessonService->update((int) $id, $request->validated()));
    }

    public function destroy(Request $request, string $courseId, string $id): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);
        $this->lessonService->delete((int) $id);

        return response()->json(['message' => 'Lesson deleted.']);
    }

    public function presignVideo(PresignLessonVideoUploadRequest $request, string $courseId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json($this->lessonService->presignVideoUpload($request->validated()));
    }

    // ── Quiz ──────────────────────────────────────────────────────────────────

    public function showQuiz(Request $request, string $courseId, string $lessonId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json($this->quizService->showByLesson((int) $lessonId));
    }

    public function storeQuiz(Request $request, string $courseId, string $lessonId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json($this->quizService->createForLesson((int) $lessonId), 201);
    }

    public function destroyQuiz(Request $request, string $courseId, string $quizId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);
        $this->quizService->deleteQuiz((int) $quizId);

        return response()->json(['message' => 'Quiz deleted.']);
    }

    public function addQuestion(Request $request, string $courseId, string $quizId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json($this->quizService->addQuestion((int) $quizId, $request->all()), 201);
    }

    public function updateQuestion(UpdateQuizQuestionRequest $request, string $courseId, string $questionId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json($this->quizService->updateQuestion((int) $questionId, $request->validated()));
    }

    public function destroyQuestion(Request $request, string $courseId, string $questionId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);
        $this->quizService->deleteQuestion((int) $questionId);

        return response()->json(['message' => 'Question deleted.']);
    }

    public function presignQuestionMedia(Request $request, string $courseId): JsonResponse
    {
        $this->authoriseCourse($request->user()->id, (int) $courseId);

        return response()->json($this->quizService->presignMediaUpload($request->all()));
    }

    // ── Helper ────────────────────────────────────────────────────────────────

    private function authoriseCourse(int $instructorId, int $courseId): void
    {
        Course::query()
            ->whereKey($courseId)
            ->where('user_id', $instructorId)
            ->firstOrFail();
    }
}