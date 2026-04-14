<?php

namespace Database\Seeders;

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
                'lesson_id'     => $lesson->id,
                'title'         => 'Quiz: ' . $lesson->title,
                'description'   => 'Kiểm tra kiến thức bài học: ' . $lesson->title,
                'time_limit'    => 10,
                'passing_score' => 70,
            ]);

            // Câu 1 – single choice
            $q1 = Question::create([
                'quiz_id' => $quiz->id,
                'content' => 'Nội dung chính của bài học này là gì?',
                'type'    => QuestionType::SINGLE, // 1
                'order'   => 0,
            ]);

            $this->createOptions($q1->id, [
                [1, 'Lý thuyết cơ bản và khái niệm nền tảng', true],
                [2, 'Cài đặt phần mềm',                        false],
                [3, 'Thực hành dự án thực tế',                 false],
                [4, 'Ôn tập kiến thức cũ',                     false],
            ]);

            // Câu 2 – single choice
            $q2 = Question::create([
                'quiz_id' => $quiz->id,
                'content' => 'Công cụ nào được đề cập trong bài học?',
                'type'    => QuestionType::SINGLE,
                'order'   => 1,
            ]);

            $this->createOptions($q2->id, [
                [1, 'Terminal / Command Line', false],
                [2, 'Công cụ chính được giới thiệu trong bài', true],
                [3, 'Trình duyệt web',         false],
                [4, 'IDE như VS Code',          false],
            ]);

            // Câu 3 – multiple choice
            $q3 = Question::create([
                'quiz_id' => $quiz->id,
                'content' => 'Những điểm nào là quan trọng cần ghi nhớ? (Chọn nhiều đáp án)',
                'type'    => QuestionType::MULTIPLE, // 2
                'order'   => 2,
            ]);

            $this->createOptions($q3->id, [
                [1, 'Nắm vững lý thuyết cơ bản',     true],
                [2, 'Thực hành đều đặn',              true],
                [3, 'Bỏ qua phần tài liệu tham khảo', false],
                [4, 'Áp dụng vào dự án thực tế',     true],
            ]);
        }
    }

    /**
     * @param array<array{int, string, bool}> $options [label, content, is_correct]
     */
    private function createOptions(int $questionId, array $options): void
    {
        foreach ($options as [$label, $content, $isCorrect]) {
            QuestionOption::create([
                'question_id' => $questionId,
                'label'       => $label,   // 1=A, 2=B, 3=C, 4=D
                'content'     => $content,
                'is_correct'  => $isCorrect,
            ]);
        }
    }
}
