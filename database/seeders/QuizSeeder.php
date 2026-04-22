<?php

namespace Database\Seeders;

use App\Enums\Question\Answerpe;
use App\Enums\Question\AnswerType;
use App\Enums\Question\QuestionType;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class QuizSeeder extends Seeder
{
    public function run(): void
    {
        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {
            $quiz = Quiz::create([
                'lesson_id' => $lesson->id,
                'title' => 'Quiz: ' . $lesson->title,
                'description' => 'Test your knowledge: ' . $lesson->title,
                'time_limit' => 10,
                'passing_score' => 70,
            ]);

            // Question 1 – single choice
            $q1 = Question::create([
                'quiz_id' => $quiz->id,
                'content' => 'What is the main topic of this lesson?',
                'type' => QuestionType::TEXT, // 1
                'order' => 0,
            ]);

            $this->createOptions($q1->id, [
                [1, 'Core theory and foundational concepts', true],
                [2, 'Installing software',                   false],
                [3, 'Hands-on real-world project',           false],
                [4, 'Reviewing previous knowledge',          false],
            ]);

            // Question 2 – single choice
            $q2 = Question::create([
                'quiz_id' => $quiz->id,
                'content' => 'Which tool is covered in this lesson?',
                'type' => QuestionType::TEXT,
                'order' => 1,
            ]);

            $this->createOptions($q2->id, [
                [1, 'Terminal / Command Line',                      false],
                [2, 'The main tool introduced in the lesson',       true],
                [3, 'Web browser',                                  false],
                [4, 'IDE such as VS Code',                          false],
            ]);

            // Question 3 – multiple choice
            $q3 = Question::create([
                'quiz_id' => $quiz->id,
                'content' => 'Which points are important to remember? (Select all that apply)',
                'type' => QuestionType::TEXT, // 2
                'order' => 2,
            ]);

            $this->createOptions($q3->id, [
                [1, 'Master the core theory',                  true],
                [2, 'Practice regularly',                      true],
                [3, 'Skip the reference documentation',        false],
                [4, 'Apply knowledge to real-world projects',  true],
            ]);
        }
    }

    /**
     * @param  array<array{int, string, bool}>  $options  [label, content, is_correct]
     */
    private function createOptions(int $questionId, array $options): void
    {
        foreach ($options as [$label, $content, $isCorrect]) {
            QuestionOption::create([
                'question_id' => $questionId,
                'label' => $label,   // 1=A, 2=B, 3=C, 4=D
                'content' => $content,
                'is_correct' => $isCorrect,
            ]);
        }
    }
}
