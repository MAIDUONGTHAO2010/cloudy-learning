<?php

namespace App\Services;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Repositories\Contracts\LessonRepositoryInterface;
use App\Repositories\Contracts\QuizRepositoryInterface;
use Aws\S3\S3Client;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Str;

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
            'title' => 'Quiz: ' . $lesson->title,
            'description' => null,
            'time_limit' => 10,
            'passing_score' => 70,
        ]);

        for ($i = 0; $i < 3; $i++) {
            $question = Question::create([
                'quiz_id'     => $quiz->id,
                'content'     => 'Question ' . ($i + 1),
                'type'        => 1,
                'answer_type' => 1,
                'order'       => $i,
            ]);

            foreach ([1, 2, 3, 4] as $label) {
                QuestionOption::create([
                    'question_id' => $question->id,
                    'label' => $label,
                    'content' => 'Option ' . ['A', 'B', 'C', 'D'][$label - 1],
                    'is_correct' => $label === 1,
                ]);
            }
        }

        $quiz->load(['questions' => fn($q) => $q->orderBy('order')
            ->with(['options' => fn($o) => $o->orderBy('label')])]);

        return $quiz;
    }

    public function deleteQuiz(int $quizId): void
    {
        $this->quizRepository->delete($quizId);
    }

    public function addQuestion(int $quizId, array $data = []): object
    {
        $quiz = $this->quizRepository->find($quizId);
        $order = $quiz->questions()->max('order') ?? -1;

        $question = Question::create([
            'quiz_id'     => $quiz->id,
            'content'     => $data['content'] ?? 'New question',
            'type'        => $data['type'] ?? 1,
            'answer_type' => $data['answer_type'] ?? 1,
            'order'       => $order + 1,
        ]);

        $optionContents = $data['options'] ?? [];
        foreach ([1, 2, 3, 4] as $label) {
            $opt = collect($optionContents)->firstWhere('label', $label);
            QuestionOption::create([
                'question_id' => $question->id,
                'label' => $label,
                'content' => $opt['content'] ?? 'Option ' . ['A', 'B', 'C', 'D'][$label - 1],
                'is_correct' => $opt['is_correct'] ?? $label === 1,
            ]);
        }

        $question->load(['options' => fn($o) => $o->orderBy('label')]);

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
            'content'     => $data['content'],
            'type'        => $data['type'],
            'answer_type' => $data['answer_type'],
        ]);

        foreach ($data['options'] as $opt) {
            QuestionOption::where('id', $opt['id'])->update([
                'content' => $opt['content'],
                'is_correct' => $opt['is_correct'],
            ]);
        }

        $question->load(['options' => fn($o) => $o->orderBy('label')]);

        return $question;
    }

    public function presignMediaUpload(array $data): array
    {
        $extension = strtolower(pathinfo($data['file_name'] ?? 'media', PATHINFO_EXTENSION) ?: 'bin');
        $name      = Str::slug(pathinfo($data['file_name'] ?? 'media', PATHINFO_FILENAME) ?: 'question-media');
        $path      = 'questions/media/' . now()->format('Y/m') . '/' . Str::uuid() . '-' . $name . '.' . $extension;

        $diskConfig       = config('filesystems.disks.s3');
        $internalEndpoint = rtrim((string) $diskConfig['endpoint'], '/');
        $publicEndpoint   = rtrim((string) ($diskConfig['public_endpoint'] ?: $diskConfig['endpoint']), '/');
        $parsedPublic     = parse_url($publicEndpoint);

        $client = new S3Client([
            'version'                 => 'latest',
            'region'                  => $diskConfig['region'],
            'endpoint'                => $internalEndpoint,
            'use_path_style_endpoint' => (bool) $diskConfig['use_path_style_endpoint'],
            'credentials'             => [
                'key'    => $diskConfig['key'],
                'secret' => $diskConfig['secret'],
            ],
        ]);

        $putCommand = $client->getCommand('PutObject', [
            'Bucket'      => $diskConfig['bucket'],
            'Key'         => $path,
            'ContentType' => $data['content_type'] ?? 'application/octet-stream',
        ]);
        $putRequest = $client->createPresignedRequest($putCommand, '+15 minutes');
        $uploadUrl  = $this->buildPublicPresignedUrl($putRequest->getUri(), $parsedPublic);

        $getCommand = $client->getCommand('GetObject', [
            'Bucket' => $diskConfig['bucket'],
            'Key'    => $path,
        ]);
        $getRequest = $client->createPresignedRequest($getCommand, '+1 hour');
        $mediaUrl   = $this->buildPublicPresignedUrl($getRequest->getUri(), $parsedPublic);

        return [
            'path'       => $path,
            'upload_url' => $uploadUrl,
            'media_url'  => $mediaUrl,
        ];
    }

    private function buildPublicPresignedUrl(\Psr\Http\Message\UriInterface $uri, array $parsedPublic): string
    {
        $parsedRaw = parse_url((string) $uri);

        return ($parsedPublic['scheme'] ?? 'http') . '://'
            . ($parsedPublic['host'] ?? 'localhost')
            . (isset($parsedPublic['port']) ? ':' . $parsedPublic['port'] : '')
            . ($parsedRaw['path'] ?? '')
            . (isset($parsedRaw['query']) ? '?' . $parsedRaw['query'] : '');
    }
}
