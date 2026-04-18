<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Repositories\Contracts\LessonRepositoryInterface;
use App\Repositories\Contracts\QuizRepositoryInterface;
use Illuminate\Http\Exceptions\HttpResponseException;

class QuizService
{
    public function __construct(
        protected QuizRepositoryInterface $quizRepository,
        protected LessonRepositoryInterface $lessonRepository,
    ) {}

    public function showByLesson(int $lessonId): ?object
    {
        $this->lessonRepository->find($lessonId);

        return $this->quizRepository->getByLessonWithQuestions($lessonId);
    }

    public function createForLesson(int $lessonId): object
    {
        $lesson = $this->lessonRepository->find($lessonId);

        if ($lesson->quiz()->exists()) {
            throw new HttpResponseException(
                response()->json(['message' => 'This lesson already has a quiz.'], 422)
            );
        }

        $quiz = Quiz::create([
            'lesson_id' => $lesson->id,
            'title' => 'Quiz: '.$lesson->title,
            'description' => null,
            'time_limit' => 10,
            'passing_score' => 70,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'content' => 'Question '.($i + 1),
                'type' => 1,
                'order' => $i,
            ]);

            foreach ([1, 2, 3, 4] as $label) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'content' => 'Option '.['A', 'B', 'C', 'D'][$label - 1],
                    'is_correct' => $label === 1,
                ]);
            }
        }

        $quiz->load(['questions' => fn ($q) => $q->orderBy('order')
            ->with(['options' => fn ($o) => $o->orderBy('label')])]);

        return $quiz;
    }

    public function deleteQuiz(int $quizId): void
    {
        $this->quizRepository->delete($quizId);
    }

    public function addQuestion(int $quizId): object
    {
        $quiz = $this->quizRepository->find($quizId);
        $order = $quiz->questions()->max('order') ?? -1;

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'content' => 'New question',
            'type' => 1,
            'order' => $order + 1,
        ]);

        foreach ([1, 2, 3, 4] as $label) {
            QuestionOption::create([
                'question_id' => $question->id,
                'label' => $label,
                'content' => 'Option '.['A', 'B', 'C', 'D'][$label - 1],
                'is_correct' => $label === 1,
            ]);
        }

        $question->load(['options' => fn ($o) => $o->orderBy('label')]);

        return $question;
    }

    public function deleteQuestion(int $questionId): void
    {
        Question::findOrFail($questionId)->delete();
    }

    public function updateQuestion(int $questionId, array $data): object
    {
        $question = Question::findOrFail($questionId);
        $question->update([
            'content' => $data['content'],
            'type' => $data['type'],
        ]);

        foreach ($data['options'] as $opt) {
            QuestionOption::where('id', $opt['id'])->update([
                'content' => $opt['content'],
                'is_correct' => $opt['is_correct'],
            ]);
        }

        $question->load(['options' => fn ($o) => $o->orderBy('label')]);

        return $question;
    }
}
