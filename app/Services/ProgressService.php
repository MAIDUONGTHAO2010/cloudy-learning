<?php

namespace App\Services;

use App\Enums\Course\EnrollmentStatus;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use Illuminate\Support\Facades\DB;

class ProgressService
{
    /**
     * Update video watch progress for a lesson.
     * Marks lesson complete when video ≥80% watched (and quiz passed if applicable).
     */
    public function updateVideoProgress(int $userId, int $lessonId, int $watchPercent): array
    {
        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['watch_percent' => 0, 'video_completed' => false]
        );

        if ($watchPercent > $progress->watch_percent) {
            $progress->watch_percent = $watchPercent;
        }

        if ($progress->watch_percent >= 80 && ! $progress->video_completed) {
            $progress->video_completed = true;
        }

        $progress->save();

        $this->checkAndMarkLessonComplete($userId, $lessonId, $progress->fresh());

        return $this->buildProgressResponse($userId, $lessonId, $progress->fresh());
    }

    /**
     * Grade a quiz attempt and update lesson completion status.
     */
    public function submitQuizAttempt(int $userId, int $lessonId, array $answers): array
    {
        $lesson = Lesson::with(['quiz.questions.options'])->findOrFail($lessonId);

        abort_if(! $lesson->quiz, 404, 'No quiz for this lesson.');

        $quiz = $lesson->quiz;
        $totalQuestions = $quiz->questions->count();
        $correctCount = 0;

        foreach ($quiz->questions as $question) {
            $selectedOptionId = isset($answers[$question->id]) ? (int) $answers[$question->id] : null;

            if ($selectedOptionId !== null) {
                $isCorrect = $question->options
                    ->where('id', $selectedOptionId)
                    ->first()?->is_correct ?? false;

                if ($isCorrect) {
                    $correctCount++;
                }
            }
        }

        $score = $totalQuestions > 0
            ? (int) round($correctCount / $totalQuestions * 100)
            : 0;

        $passed = $score >= $quiz->passing_score;

        QuizAttempt::updateOrCreate(
            ['quiz_id' => $quiz->id, 'user_id' => $userId],
            ['score' => $score, 'passed' => $passed, 'answers' => $answers]
        );

        $progress = LessonProgress::firstOrCreate(
            ['user_id' => $userId, 'lesson_id' => $lessonId],
            ['watch_percent' => 0, 'video_completed' => false]
        );

        $this->checkAndMarkLessonComplete($userId, $lessonId, $progress->fresh());

        return [
            'score'         => $score,
            'passed'        => $passed,
            'passing_score' => $quiz->passing_score,
            'correct'       => $correctCount,
            'total'         => $totalQuestions,
        ];
    }

    /**
     * Get current progress state for a lesson.
     */
    public function getLessonProgress(int $userId, int $lessonId): array
    {
        $progress = LessonProgress::where('user_id', $userId)
            ->where('lesson_id', $lessonId)
            ->first();

        $lesson = Lesson::with('quiz')->findOrFail($lessonId);

        $quizAttempt = null;
        if ($lesson->quiz) {
            $quizAttempt = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $lesson->quiz->id)
                ->first();
        }

        return [
            'watch_percent'    => $progress?->watch_percent ?? 0,
            'video_completed'  => $progress?->video_completed ?? false,
            'lesson_completed' => $progress?->completed_at !== null,
            'quiz_passed'      => $quizAttempt?->passed ?? false,
            'quiz_score'       => $quizAttempt?->score ?? null,
        ];
    }

    /**
     * Get aggregated progress for a single course.
     */
    public function getCourseProgress(int $userId, int $courseId): array
    {
        $totalLessons = Lesson::where('course_id', $courseId)
            ->where('is_active', true)
            ->count();

        if ($totalLessons === 0) {
            return ['completed' => 0, 'total' => 0, 'percentage' => 0];
        }

        $completedCount = LessonProgress::query()
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->where('lesson_progress.user_id', $userId)
            ->where('lessons.course_id', $courseId)
            ->whereNotNull('lesson_progress.completed_at')
            ->count();

        return [
            'completed'  => $completedCount,
            'total'      => $totalLessons,
            'percentage' => (int) round($completedCount / $totalLessons * 100),
        ];
    }

    /**
     * Get progress percentages for multiple courses at once (used by Dashboard).
     *
     * @param  int[]  $courseIds
     * @return array<int, int>  courseId => percentage (0-100)
     */
    public function getCoursesProgressMap(int $userId, array $courseIds): array
    {
        if (empty($courseIds)) {
            return [];
        }

        $completedCounts = LessonProgress::query()
            ->join('lessons', 'lesson_progress.lesson_id', '=', 'lessons.id')
            ->whereIn('lessons.course_id', $courseIds)
            ->where('lesson_progress.user_id', $userId)
            ->whereNotNull('lesson_progress.completed_at')
            ->select('lessons.course_id', DB::raw('COUNT(*) as completed_count'))
            ->groupBy('lessons.course_id')
            ->pluck('completed_count', 'course_id');

        $totalCounts = Lesson::whereIn('course_id', $courseIds)
            ->where('is_active', true)
            ->select('course_id', DB::raw('COUNT(*) as total'))
            ->groupBy('course_id')
            ->pluck('total', 'course_id');

        return collect($courseIds)->mapWithKeys(function ($id) use ($completedCounts, $totalCounts) {
            $total     = $totalCounts[$id] ?? 0;
            $completed = $completedCounts[$id] ?? 0;

            return [$id => $total > 0 ? (int) round($completed / $total * 100) : 0];
        })->toArray();
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function checkAndMarkLessonComplete(int $userId, int $lessonId, LessonProgress $progress): void
    {
        $lesson = Lesson::with('quiz')->findOrFail($lessonId);

        if (! $progress->video_completed) {
            // Video not done — revoke completion if it was previously set
            if ($progress->completed_at !== null) {
                $progress->update(['completed_at' => null]);
            }

            return;
        }

        if ($lesson->quiz) {
            $quizPassed = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $lesson->quiz->id)
                ->where('passed', true)
                ->exists();

            if ($quizPassed && $progress->completed_at === null) {
                $progress->update(['completed_at' => now()]);
            } elseif (! $quizPassed && $progress->completed_at !== null) {
                $progress->update(['completed_at' => null]);
            }
        } else {
            // No quiz required — video completion is enough
            if ($progress->completed_at === null) {
                $progress->update(['completed_at' => now()]);
            }
        }
    }

    private function buildProgressResponse(int $userId, int $lessonId, LessonProgress $progress): array
    {
        $lesson      = Lesson::with('quiz')->findOrFail($lessonId);
        $quizAttempt = null;

        if ($lesson->quiz) {
            $quizAttempt = QuizAttempt::where('user_id', $userId)
                ->where('quiz_id', $lesson->quiz->id)
                ->first();
        }

        return [
            'watch_percent'    => $progress->watch_percent,
            'video_completed'  => $progress->video_completed,
            'lesson_completed' => $progress->completed_at !== null,
            'quiz_passed'      => $quizAttempt?->passed ?? false,
            'quiz_score'       => $quizAttempt?->score ?? null,
        ];
    }
}
