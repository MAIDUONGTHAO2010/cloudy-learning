<?php

namespace App\Repositories;

use App\Enums\Course\EnrollmentStatus;
use App\Models\Course;
use App\Repositories\Contracts\CourseRepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository;

class CourseRepositoryEloquent extends BaseRepository implements CourseRepositoryInterface
{
    public function model(): string
    {
        return Course::class;
    }

    public function getAllWithRelations()
    {
        return Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('order')
            ->get();
    }

    public function getPaginatedWithRelations()
    {
        return Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderBy('order')
            ->paginate(10);
    }

    public function getWithRelations(int $id)
    {
        return Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->findOrFail($id);
    }

    public function getEnrolledByUser(int $userId, int $perPage = 12)
    {
        return Course::query()
            ->select([
                'courses.*',
                'course_user.status as enrollment_status',
                'course_user.note as enrollment_note',
                'course_user.created_at as enrolled_at',
            ])
            ->join('course_user', 'courses.id', '=', 'course_user.course_id')
            ->with(['instructor:id,name', 'category:id,name'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->where('course_user.user_id', $userId)
            ->where('courses.is_active', true)
            ->orderByDesc('course_user.created_at')
            ->paginate($perPage);
    }

    public function enrollUser(int $courseId, int $userId): void
    {
        $course = Course::query()->findOrFail($courseId);
        $existing = $this->getEnrollmentForUser($courseId, $userId);

        if ($existing) {
            $course->students()->updateExistingPivot($userId, [
                'status' => EnrollmentStatus::REQUEST,
                'note' => null,
                'approved_at' => null,
                'cancelled_at' => null,
                'updated_at' => now(),
            ]);

            return;
        }

        $course->students()->attach($userId, [
            'status' => EnrollmentStatus::REQUEST,
            'note' => null,
            'approved_at' => null,
            'cancelled_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function isUserEnrolled(int $courseId, int $userId): bool
    {
        return Course::query()
            ->whereKey($courseId)
            ->whereHas('students', fn($query) => $query
                ->where('users.id', $userId)
                ->where('course_user.status', EnrollmentStatus::APPROVED))
            ->exists();
    }

    public function getEnrollmentForUser(int $courseId, int $userId): ?object
    {
        return Course::query()
            ->select([
                'course_user.status',
                'course_user.note',
                'course_user.created_at',
                'course_user.approved_at',
                'course_user.cancelled_at',
            ])
            ->join('course_user', 'courses.id', '=', 'course_user.course_id')
            ->where('courses.id', $courseId)
            ->where('course_user.user_id', $userId)
            ->first();
    }

    public function getPendingRequestsForInstructor(int $instructorId): mixed
    {
        return Course::query()
            ->select([
                'course_user.course_id',
                'course_user.user_id',
                'course_user.status',
                'course_user.note',
                'course_user.created_at as requested_at',
                'courses.title as course_title',
                'users.name as student_name',
                'users.email as student_email',
            ])
            ->join('course_user', 'courses.id', '=', 'course_user.course_id')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->where('courses.user_id', $instructorId)
            ->where('course_user.status', EnrollmentStatus::REQUEST)
            ->orderByDesc('course_user.created_at')
            ->get();
    }

    public function reviewEnrollment(int $courseId, int $userId, int $status, ?string $note = null): void
    {
        $course = Course::query()->findOrFail($courseId);

        $course->students()->updateExistingPivot($userId, [
            'status' => $status,
            'note' => $note,
            'approved_at' => $status === EnrollmentStatus::APPROVED ? now() : null,
            'cancelled_at' => $status === EnrollmentStatus::CANCELED ? now() : null,
            'updated_at' => now(),
        ]);
    }

    public function reorder(array $items): void
    {
        foreach ($items as $item) {
            Course::where('id', $item['id'])->update(['order' => $item['order']]);
        }
    }

    public function getByInstructor(int $instructorId)
    {
        return Course::with(['category:id,name', 'tags:id,name,slug'])
            ->withCount(['lessons', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->withCount(['students as enrolled_count' => fn($q) => $q->where('course_user.status', EnrollmentStatus::APPROVED)])
            ->where('user_id', $instructorId)
            ->orderByDesc('created_at')
            ->get();
    }

    public function getEnrolledStudents(int $courseId)
    {
        return Course::query()
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'course_user.status',
                'course_user.note',
                'course_user.created_at as enrolled_at',
                'course_user.approved_at',
            ])
            ->join('course_user', 'courses.id', '=', 'course_user.course_id')
            ->join('users', 'users.id', '=', 'course_user.user_id')
            ->where('courses.id', $courseId)
            ->orderByDesc('course_user.created_at')
            ->get();
    }

    public function removeEnrollment(int $courseId, int $userId): void
    {
        Course::query()->findOrFail($courseId)
            ->students()->detach($userId);
    }

    public function enrollUserApproved(int $courseId, int $userId): void
    {
        $course = Course::query()->findOrFail($courseId);
        $course->students()->attach($userId, [
            'status'      => EnrollmentStatus::APPROVED,
            'note'        => null,
            'approved_at' => now(),
            'cancelled_at' => null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function getPublicPaginated(array $filters = [], int $perPage = 12)
    {
        $query = Course::with(['instructor:id,name', 'category:id,name', 'tags:id,name,slug'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->where('is_active', true);

        if (! empty($filters['search'])) {
            $query->where('title', 'ilike', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['instructor_id'])) {
            $query->where('user_id', $filters['instructor_id']);
        }

        if (! empty($filters['tag'])) {
            $query->whereHas('tags', fn($q) => $q->where('slug', $filters['tag']));
        }

        return $query->orderBy('order')->paginate($perPage);
    }

    public function getPopular(int $limit = 8)
    {
        return Course::with(['instructor:id,name', 'category:id,name'])
            ->withCount(['lessons', 'reviews'])
            ->withAvg('reviews', 'rating')
            ->where('is_active', true)
            ->orderByDesc('reviews_count')
            ->limit($limit)
            ->get();
    }

    public function getNewest(int $limit = 8)
    {
        return Course::with(['instructor:id,name', 'category:id,name'])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->where('is_active', true)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function findBySlug(string $slug)
    {
        return Course::with([
            'instructor:id,name',
            'category:id,name',
            'tags:id,name,slug',
            'lessons'      => fn($q) => $q->where('is_active', true)->orderBy('order'),
            'lessons.quiz' => fn($q) => $q->select(['id', 'lesson_id']),
        ])
            ->withCount('lessons')
            ->withAvg('reviews', 'rating')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
