<?php

namespace App\Services;

use App\Models\Lesson;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\LessonRepositoryInterface;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LessonService
{
    public const MAX_VIDEO_SIZE = 1610612736;

    public function __construct(
        protected LessonRepositoryInterface $lessonRepository,
        protected CourseRepositoryInterface $courseRepository,
    ) {}

    public function listByCourse(int $courseId): array
    {
        $course = $this->courseRepository->find($courseId);
        $lessons = $this->lessonRepository->getByCourse($courseId);

        return ['course' => $course, 'lessons' => $lessons];
    }

    public function create(int $courseId, array $data): mixed
    {
        $this->courseRepository->find($courseId); // throws ModelNotFoundException if missing

        $data['course_id'] = $courseId;
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));

        $lesson = $this->lessonRepository->create($data);

        Log::info('Admin created lesson', [
            'admin_id' => Auth::id(),
            'course_id' => $courseId,
            'lesson_id' => $lesson->id,
            'title' => $lesson->title,
            'video_url' => $lesson->video_url,
        ]);

        return $lesson;
    }

    public function update(int $id, array $data): mixed
    {
        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $id);

        $lesson = $this->lessonRepository->update($data, $id);

        Log::info('Admin updated lesson', [
            'admin_id' => Auth::id(),
            'lesson_id' => $id,
            'title' => $lesson->title,
            'video_url' => $lesson->video_url,
        ]);

        return $lesson;
    }

    public function delete(int $id): void
    {
        $this->lessonRepository->delete($id);
    }

    public function reorder(int $courseId, array $items): void
    {
        $this->lessonRepository->reorder($items);
    }

    public function presignVideoUpload(array $data): array
    {
        $extension = strtolower(pathinfo($data['file_name'], PATHINFO_EXTENSION) ?: 'mp4');
        $name = Str::slug(pathinfo($data['file_name'], PATHINFO_FILENAME) ?: 'lesson-video');
        $path = 'lessons/videos/' . now()->format('Y/m') . '/' . Str::uuid() . '-' . $name . '.' . $extension;

        Log::info('Admin requested lesson video upload', [
            'admin_id' => Auth::id(),
            'file_name' => $data['file_name'],
            'content_type' => $data['content_type'],
            'file_size' => $data['file_size'],
            'path' => $path,
        ]);

        try {
            $diskConfig = config('filesystems.disks.s3');
            $internalEndpoint = rtrim((string) $diskConfig['endpoint'], '/');
            $publicEndpoint = rtrim((string) ($diskConfig['public_endpoint'] ?: $diskConfig['endpoint']), '/');
            $parsedPublic = parse_url($publicEndpoint);

            $client = new S3Client([
                'version' => 'latest',
                'region' => $diskConfig['region'],
                'endpoint' => $internalEndpoint,
                'use_path_style_endpoint' => (bool) $diskConfig['use_path_style_endpoint'],
                'credentials' => [
                    'key' => $diskConfig['key'],
                    'secret' => $diskConfig['secret'],
                ],
            ]);

            $command = $client->getCommand('PutObject', [
                'Bucket' => $diskConfig['bucket'],
                'Key' => $path,
                'ContentType' => $data['content_type'],
            ]);

            $putRequest = $client->createPresignedRequest($command, '+15 minutes');
            $uploadUrl  = $this->buildPublicPresignedUrl($putRequest->getUri(), $parsedPublic);

            $getCommand = $client->getCommand('GetObject', [
                'Bucket' => $diskConfig['bucket'],
                'Key'    => $path,
            ]);
            $getRequest = $client->createPresignedRequest($getCommand, '+1 hour');
            $videoUrl   = $this->buildPublicPresignedUrl($getRequest->getUri(), $parsedPublic);

            $result = [
                'path'         => $path,
                'upload_url'   => $uploadUrl,
                'headers'      => [
                    'Content-Type' => $data['content_type'],
                ],
                'video_url'    => $videoUrl,
                'max_file_size' => self::MAX_VIDEO_SIZE,
            ];

            Log::info('Admin lesson video upload URL generated', [
                'admin_id' => Auth::id(),
                'path' => $path,
                'video_url' => $result['video_url'],
            ]);

            return $result;
        } catch (\Throwable $e) {
            Log::error('Admin lesson video upload presign failed', [
                'admin_id' => Auth::id(),
                'file_name' => $data['file_name'],
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
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

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $exists = Lesson::where('slug', $slug)
                ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
                ->exists();

            if (! $exists) {
                break;
            }

            $slug = $original . '-' . $count++;
        }

        return $slug;
    }
}
