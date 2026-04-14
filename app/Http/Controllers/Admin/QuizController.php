<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Get the quiz (with questions + options) belonging to a lesson.
     */
    public function show(int $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);

        $quiz = $lesson->quiz()->with(['questions' => function ($q) {
            $q->orderBy('order')->with(['options' => function ($o) {
                $o->orderBy('label');
            }]);
        }])->first();

        if (! $quiz) {
            return response()->json(null);
        }

        return response()->json($quiz);
    }

    /**
     * Create a default quiz with 3 blank questions for a lesson.
     */
    public function store(int $lessonId)
    {
        $lesson = Lesson::findOrFail($lessonId);

        if ($lesson->quiz()->exists()) {
            return response()->json(['message' => 'This lesson already has a quiz.'], 422);
        }

        $quiz = Quiz::create([
            'lesson_id'     => $lesson->id,
            'title'         => 'Quiz: ' . $lesson->title,
            'description'   => null,
            'time_limit'    => 10,
            'passing_score' => 70,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'content' => 'Question ' . ($i + 1),
                'type'    => 1,
                'order'   => $i,
            ]);

            foreach ([1, 2, 3, 4] as $label) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label'       => $label,
                    'content'     => 'Option ' . ['A', 'B', 'C', 'D'][$label - 1],
                    'is_correct'  => $label === 1,
                ]);
            }
        }

        $quiz->load(['questions' => function ($q) {
            $q->orderBy('order')->with(['options' => fn ($o) => $o->orderBy('label')]);
        }]);

        return response()->json($quiz, 201);
    }

    /**
     * Delete a quiz entirely.
     */
    public function destroy(int $quizId)
    {
        Quiz::findOrFail($quizId)->delete();

        return response()->json(['message' => 'Quiz deleted successfully']);
    }

    /**
     * Add a blank question to an existing quiz.
     */
    public function addQuestion(int $quizId)
    {
        $quiz = Quiz::findOrFail($quizId);

        $order = $quiz->questions()->max('order') ?? -1;

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'content' => 'New question',
            'type'    => 1,
            'order'   => $order + 1,
        ]);

        foreach ([1, 2, 3, 4] as $label) {
            QuestionOption::create([
                'question_id' => $question->id,
                'label'       => $label,
                'content'     => 'Option ' . ['A', 'B', 'C', 'D'][$label - 1],
                'is_correct'  => $label === 1,
            ]);
        }

        $question->load(['options' => fn ($o) => $o->orderBy('label')]);

        return response()->json($question, 201);
    }

    /**
     * Delete a single question (and its options via cascade).
     */
    public function destroyQuestion(int $questionId)
    {
        Question::findOrFail($questionId)->delete();

        return response()->json(['message' => 'Question deleted successfully']);
    }

    /**
     * Update a question's content, type and all four options at once.
     */
    public function updateQuestion(Request $request, int $questionId)
    {
        $data = $request->validate([
            'content'              => 'required|string',
            'type'                 => 'required|integer|in:1,2',
            'options'              => 'required|array|size:4',
            'options.*.id'         => 'required|integer|exists:question_options,id',
            'options.*.content'    => 'required|string',
            'options.*.is_correct' => 'required|boolean',
        ]);

        $question = Question::findOrFail($questionId);
        $question->update([
            'content' => $data['content'],
            'type'    => $data['type'],
        ]);

        foreach ($data['options'] as $opt) {
            QuestionOption::where('id', $opt['id'])->update([
                'content'    => $opt['content'],
                'is_correct' => $opt['is_correct'],
            ]);
        }

        $question->load(['options' => fn ($o) => $o->orderBy('label')]);

        return response()->json($question);
    }
}
