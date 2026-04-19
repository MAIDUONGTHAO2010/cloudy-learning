<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Services\ProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    public function __construct(protected ProgressService $progressService) {}

    /**
     * POST /api/lessons/{lessonId}/progress
     * Record how far the student has watched a lesson video.
     */
    public function updateVideoProgress(Request $request, int $lessonId): JsonResponse
    {
        $request->validate([
            'watch_percent' => 'required|integer|min:0|max:100',
        ]);

        $result = $this->progressService->updateVideoProgress(
            Auth::id(),
            $lessonId,
            $request->integer('watch_percent')
        );

        return response()->json($result);
    }

    /**
     * GET /api/lessons/{lessonId}/progress
     * Get the current progress state for a lesson.
     */
    public function getLessonProgress(int $lessonId): JsonResponse
    {
        $result = $this->progressService->getLessonProgress(Auth::id(), $lessonId);

        return response()->json($result);
    }

    /**
     * GET /api/lessons/{lessonId}/quiz
     * Return quiz with questions and options — without revealing correct answers.
     */
    public function getLessonQuiz(int $lessonId): JsonResponse
    {
        $lesson = Lesson::with([
            'quiz'                  => fn($q) => $q->select(['id', 'lesson_id', 'title', 'description', 'time_limit', 'passing_score']),
            'quiz.questions'        => fn($q) => $q->orderBy('order')->select(['id', 'quiz_id', 'content', 'type', 'order']),
            'quiz.questions.options' => fn($q) => $q->orderBy('label')->select(['id', 'question_id', 'label', 'content']),
        ])->findOrFail($lessonId);

        return response()->json($lesson->quiz);
    }

    /**
     * POST /api/lessons/{lessonId}/quiz
     * Submit quiz answers; grades server-side and updates lesson completion.
     */
    public function submitQuiz(Request $request, int $lessonId): JsonResponse
    {
        $request->validate([
            'answers'   => 'required|array',
            'answers.*' => 'integer',
        ]);

        $result = $this->progressService->submitQuizAttempt(
            Auth::id(),
            $lessonId,
            $request->input('answers')
        );

        return response()->json($result);
    }

    /**
     * GET /api/courses/{courseId}/progress
     * Get aggregated progress percentage for a course.
     */
    public function getCourseProgress(int $courseId): JsonResponse
    {
        $result = $this->progressService->getCourseProgress(Auth::id(), $courseId);

        return response()->json($result);
    }
}
