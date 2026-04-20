<?php

namespace App\Services;

use App\Enums\Course\EnrollmentStatus;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Tag;
use App\Models\User;
use App\Repositories\Contracts\CourseRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CourseService
{
    public const MAX_THUMBNAIL_SIZE = 10485760;

    public function __construct(
        protected CourseRepositoryInterface $courseRepository,
        protected UserRepositoryInterface $userRepository,
        protected NotificationService $notificationService,
    ) {}

    public function list()
    {
        return $this->courseRepository->getAllWithRelations();
    }

    public function listPaginated()
    {
        return $this->courseRepository->getPaginatedWithRelations();
    }

    public function listPublicPaginated(array $filters = [], int $perPage = 12)
    {
        return $this->courseRepository->getPublicPaginated($filters, $perPage);
    }

    public function listPopular(int $limit = 8)
    {
        return $this->courseRepository->getPopular($limit);
    }

    public function listNewest(int $limit = 8)
    {
        return $this->courseRepository->getNewest($limit);
    }

    public function findBySlug(string $slug, ?int $userId = null)
    {
        $course = $this->courseRepository->findBySlug($slug);

        return $this->decorateCourseAccess($course, $userId);
    }

    public function listTopInstructors(int $limit = 6)
    {
        return $this->userRepository->getTopInstructors($limit);
    }

    public function show(int $id)
    {
        return $this->courseRepository->getWithRelations($id);
    }

    public function enrollInCourse(string $slug, int $userId): array
    {
        $course = $this->courseRepository->findBySlug($slug);

        if ((int) $course->user_id === $userId) {
            throw ValidationException::withMessages([
                'course' => 'You cannot request enrollment in your own course.',
            ]);
        }

        $existing = $this->courseRepository->getEnrollmentForUser($course->id, $userId);

        if ($existing && (int) $existing->status === EnrollmentStatus::REQUEST) {
            return [
                'message' => 'Enrollment request is already pending approval',
                'course' => $this->findBySlug($slug, $userId),
            ];
        }

        $this->courseRepository->enrollUser($course->id, $userId);

        $student = User::query()->findOrFail($userId);
        $this->notificationService->notifyCourseEnrollmentRequested($student, $course);

        return [
            'message' => 'Enrollment request submitted successfully',
            'course' => $this->findBySlug($slug, $userId),
        ];
    }

    public function myCourses(int $userId, int $perPage = 12)
    {
        return $this->courseRepository->getEnrolledByUser($userId, $perPage);
    }

    public function instructorRequests(int $instructorId)
    {
        return $this->courseRepository->getPendingRequestsForInstructor($instructorId);
    }

    public function reviewEnrollment(int $instructorId, int $courseId, int $studentId, array $data): array
    {
        $course = Course::query()
            ->whereKey($courseId)
            ->where('user_id', $instructorId)
            ->firstOrFail();

        $status = (int) $data['status'];
        $note = $status === EnrollmentStatus::CANCELED
            ? trim((string) ($data['note'] ?? ''))
            : null;

        $this->courseRepository->reviewEnrollment($courseId, $studentId, $status, $note);

        $student = User::query()->findOrFail($studentId);
        $this->notificationService->notifyCourseEnrollmentReviewed($student, $course, $status, $note);

        return [
            'message' => $status === EnrollmentStatus::APPROVED
                ? 'Enrollment approved successfully'
                : 'Enrollment canceled successfully',
        ];
    }

    public function create(array $data): mixed
    {
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']));

        $course = $this->courseRepository->create($data);
        $course->tags()->sync($this->resolveTagIds($tags));

        $course->load(['instructor:id,name', 'category:id,name', 'tags:id,name,slug']);
        $course->loadCount(['lessons', 'reviews']);
        $course->loadAvg('reviews', 'rating');

        return $course;
    }

    public function update(int $id, array $data): mixed
    {
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $data['slug'] = $this->uniqueSlug(Str::slug($data['title']), $id);

        $course = $this->courseRepository->update($data, $id);
        $course->tags()->sync($this->resolveTagIds($tags));

        $course->load(['instructor:id,name', 'category:id,name', 'tags:id,name,slug']);
        $course->loadCount(['lessons', 'reviews']);
        $course->loadAvg('reviews', 'rating');

        return $course;
    }

    public function delete(int $id): void
    {
        $this->courseRepository->delete($id);
    }

    public function listInstructorCourses(int $instructorId)
    {
        return $this->courseRepository->getByInstructor($instructorId);
    }

    public function createForInstructor(int $instructorId, array $data): mixed
    {
        $data['user_id'] = $instructorId;

        return $this->create($data);
    }

    public function updateForInstructor(int $instructorId, int $courseId, array $data): mixed
    {
        $course = Course::query()
            ->whereKey($courseId)
            ->where('user_id', $instructorId)
            ->firstOrFail();

        return $this->update($course->id, $data);
    }

    public function deleteForInstructor(int $instructorId, int $courseId): void
    {
        Course::query()
            ->whereKey($courseId)
            ->where('user_id', $instructorId)
            ->firstOrFail();

        $this->courseRepository->delete($courseId);
    }

    public function getStudentsForCourse(int $instructorId, int $courseId)
    {
        Course::query()
            ->whereKey($courseId)
            ->where('user_id', $instructorId)
            ->firstOrFail();

        return $this->courseRepository->getEnrolledStudents($courseId);
    }

    public function removeStudentFromCourse(int $instructorId, int $courseId, int $studentId): void
    {
        Course::query()
            ->whereKey($courseId)
            ->where('user_id', $instructorId)
            ->firstOrFail();

        $this->courseRepository->removeEnrollment($courseId, $studentId);
    }

    public function addStudentToCourse(int $instructorId, int $courseId, string $email): array
    {
        Course::query()
            ->whereKey($courseId)
            ->where('user_id', $instructorId)
            ->firstOrFail();

        $student = User::where('email', $email)->firstOrFail();

        $existing = $this->courseRepository->getEnrollmentForUser($courseId, $student->id);

        if ($existing) {
            throw ValidationException::withMessages([
                'email' => 'This student is already enrolled or has a pending request.',
            ]);
        }

        $this->courseRepository->enrollUserApproved($courseId, $student->id);

        return [
            'message' => 'Student added successfully.',
            'student' => ['id' => $student->id, 'name' => $student->name, 'email' => $student->email],
        ];
    }

    public function reorder(array $items): void
    {
        $this->courseRepository->reorder($items);
    }

    public function presignThumbnailUpload(array $data): array
    {
        $extension = strtolower(pathinfo($data['file_name'], PATHINFO_EXTENSION) ?: 'png');
        $name = Str::limit(
            Str::slug(pathinfo($data['file_name'], PATHINFO_FILENAME) ?: 'course-thumbnail'),
            80,
            ''
        ) ?: 'course-thumbnail';
        $path = 'courses/thumbnails/' . now()->format('Y/m') . '/' . Str::uuid() . '-' . $name . '.' . $extension;

        Log::info('Admin requested course thumbnail upload', [
            'admin_id' => Auth::id(),
            'file_name' => $data['file_name'],
            'content_type' => $data['content_type'],
            'file_size' => $data['file_size'],
            'path' => $path,
        ]);

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

        $putRequest   = $client->createPresignedRequest($command, '+15 minutes');
        $uploadUrl    = $this->buildPublicPresignedUrl($putRequest->getUri(), $parsedPublic);

        $getCommand  = $client->getCommand('GetObject', [
            'Bucket' => $diskConfig['bucket'],
            'Key'    => $path,
        ]);
        $getRequest  = $client->createPresignedRequest($getCommand, '+1 hour');
        $thumbnailUrl = $this->buildPublicPresignedUrl($getRequest->getUri(), $parsedPublic);

        return [
            'path'         => $path,
            'upload_url'   => $uploadUrl,
            'headers'      => [
                'Content-Type' => $data['content_type'],
            ],
            'thumbnail_url' => $thumbnailUrl,
            'max_file_size' => self::MAX_THUMBNAIL_SIZE,
        ];
    }

    private function decorateCourseAccess(Course $course, ?int $userId = null): Course
    {
        $enrollment = $userId ? $this->courseRepository->getEnrollmentForUser($course->id, $userId) : null;
        $status = (int) ($enrollment->status ?? 0);
        $isOwner = $userId !== null && (int) $course->user_id === $userId;
        $canAccessFullCourse = $isOwner || $status === EnrollmentStatus::APPROVED;

        $course->setAttribute('is_enrolled', $status === EnrollmentStatus::APPROVED);
        $course->setAttribute('enrollment_status', $status ?: null);
        $course->setAttribute('enrollment_status_label', $this->enrollmentStatusLabel($status));
        $course->setAttribute('enrollment_note', $enrollment->note ?? null);
        $course->setAttribute('can_access_full_course', $canAccessFullCourse);

        $myReview = $userId ? CourseReview::where('course_id', $course->id)->where('user_id', $userId)->first() : null;
        $course->setAttribute('my_review', $myReview ? ['id' => $myReview->id, 'rating' => $myReview->rating, 'comment' => $myReview->comment] : null);

        $lessons = $course->lessons->values()->map(function ($lesson, $index) use ($canAccessFullCourse) {
            $isLocked = ! $canAccessFullCourse && $index > 0;

            $lesson->setAttribute('is_locked', $isLocked);
            $lesson->setAttribute('is_preview', ! $canAccessFullCourse && $index === 0);
            $lesson->setAttribute('has_quiz', $lesson->quiz !== null);
            $lesson->makeHidden('quiz');

            if ($isLocked) {
                $lesson->content   = null;
                $lesson->video_url = null;
            }

            return $lesson;
        });

        $course->setRelation('lessons', $lessons);

        return $course;
    }

    private function enrollmentStatusLabel(?int $status): ?string
    {
        return match ($status) {
            EnrollmentStatus::REQUEST => 'request',
            EnrollmentStatus::APPROVED => 'approved',
            EnrollmentStatus::CANCELED => 'canceled',
            default => null,
        };
    }

    private function buildPublicPresignedUrl(\Psr\Http\Message\UriInterface $uri, array $parsedPublic): string
    {
        $parsedRaw = parse_url((string) $uri);
        $basePath  = rtrim($parsedPublic['path'] ?? '', '/');

        return ($parsedPublic['scheme'] ?? 'http') . '://'
            . ($parsedPublic['host'] ?? 'localhost')
            . (isset($parsedPublic['port']) ? ':' . $parsedPublic['port'] : '')
            . $basePath
            . ($parsedRaw['path'] ?? '')
            . (isset($parsedRaw['query']) ? '?' . $parsedRaw['query'] : '');
    }

    private function uniqueSlug(string $slug, ?int $excludeId = null): string
    {
        $original = $slug;
        $count = 1;

        while (true) {
            $query = Course::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) {
                break;
            }
            $slug = $original . '-' . $count++;
        }

        return $slug;
    }

    private function resolveTagIds(array $tagNames): array
    {
        return collect($tagNames)
            ->filter()
            ->map(function (string $name) {
                $slug = Str::slug($name);

                return Tag::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => trim($name), 'slug' => $slug]
                )->id;
            })
            ->toArray();
    }
}
