<?php

namespace App\Repositories\Contracts;

use Prettus\Repository\Contracts\RepositoryInterface;

interface CourseRepositoryInterface extends RepositoryInterface
{
    public function getAllWithRelations();

    public function getPaginatedWithRelations();

    public function getPublicPaginated(array $filters = [], int $perPage = 12);

    public function getPopular(int $limit = 8);

    public function getNewest(int $limit = 8);

    public function findBySlug(string $slug);

    public function getWithRelations(int $id);

    public function getEnrolledByUser(int $userId, int $perPage = 12);

    public function enrollUser(int $courseId, int $userId): void;

    public function isUserEnrolled(int $courseId, int $userId): bool;

    public function getEnrollmentForUser(int $courseId, int $userId): ?object;

    public function getPendingRequestsForInstructor(int $instructorId): mixed;

    public function reviewEnrollment(int $courseId, int $userId, int $status, ?string $note = null): void;

    public function reorder(array $items): void;
    public function getByInstructor(int $instructorId);
    public function getEnrolledStudents(int $courseId);
    public function removeEnrollment(int $courseId, int $userId): void;
    public function enrollUserApproved(int $courseId, int $userId): void;
}
