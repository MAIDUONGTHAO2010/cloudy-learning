<?php

namespace App\Services;

use App\Enums\Course\EnrollmentStatus;
use App\Models\Course;
use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Support\Str;

class NotificationService
{
    public function __construct(protected NotificationRepositoryInterface $notificationRepository) {}

    /**
     * Send welcome notification to the newly registered user.
     */
    public function notifyUserWelcome(User $user): void
    {
        $this->notificationRepository->create([
            'user_id' => $user->id,
            'target' => 'user',
            'type' => 'welcome',
            'title' => 'Welcome to Cloudy Learning! 🎉',
            'body' => "Hi {$user->name}, we're thrilled to have you on board! Explore our courses, connect with expert instructors, and start learning today.",
            'is_read' => false,
        ]);
    }

    /**
     * Send new-registration notification to the admin site.
     */
    public function notifyAdminNewUser(User $user): void
    {
        $role = match ((int) $user->role) {
            2 => 'Instructor',
            3 => 'Admin',
            default => 'Student',
        };

        $this->notificationRepository->create([
            'user_id' => null,
            'target' => 'admin',
            'type' => 'new_registration',
            'title' => 'New user registered',
            'body' => "{$user->name} ({$user->email}) just joined the platform as a {$role}.",
            'is_read' => false,
        ]);
    }

    public function notifyAdminContactMessage(array $data): void
    {
        $subject = trim((string) ($data['subject'] ?? '')) ?: 'General inquiry';
        $message = trim((string) ($data['message'] ?? ''));

        $this->notificationRepository->create([
            'user_id' => null,
            'target' => 'admin',
            'type' => 'contact_message',
            'title' => 'New contact message',
            'body' => sprintf(
                "From: %s (%s)\nSubject: %s\nMessage: %s",
                $data['name'],
                $data['email'],
                $subject,
                Str::limit($message, 1000)
            ),
            'is_read' => false,
        ]);
    }

    public function notifyCourseEnrollmentRequested(User $student, Course $course): void
    {
        $this->notificationRepository->create([
            'user_id' => $student->id,
            'target' => 'user',
            'type' => 'course_enrollment_request_user',
            'title' => 'Enrollment request submitted',
            'body' => "Your request to join {$course->title} has been sent and is waiting for approval.",
            'is_read' => false,
        ]);

        $this->notificationRepository->create([
            'user_id' => $course->user_id,
            'target' => 'user',
            'type' => 'course_enrollment_request_instructor',
            'title' => 'New enrollment request',
            'body' => "{$student->name} ({$student->email}) requested to join {$course->title}.",
            'is_read' => false,
        ]);

        $this->notificationRepository->create([
            'user_id' => null,
            'target' => 'admin',
            'type' => 'course_enrollment_request_admin',
            'title' => 'Course enrollment request',
            'body' => "{$student->name} requested enrollment in {$course->title}.",
            'is_read' => false,
        ]);
    }

    public function notifyCourseEnrollmentReviewed(User $student, Course $course, int $status, ?string $note = null): void
    {
        $statusLabel = match ($status) {
            EnrollmentStatus::APPROVED => 'approved',
            EnrollmentStatus::CANCELED => 'canceled',
            default => 'updated',
        };

        $noteText = $note ? " Reason: {$note}" : '';

        $this->notificationRepository->create([
            'user_id' => $student->id,
            'target' => 'user',
            'type' => 'course_enrollment_review_user',
            'title' => 'Enrollment status updated',
            'body' => "Your enrollment for {$course->title} was {$statusLabel}.{$noteText}",
            'is_read' => false,
        ]);

        $this->notificationRepository->create([
            'user_id' => $course->user_id,
            'target' => 'user',
            'type' => 'course_enrollment_review_instructor',
            'title' => 'Enrollment review recorded',
            'body' => "Enrollment for {$student->name} in {$course->title} was {$statusLabel}.{$noteText}",
            'is_read' => false,
        ]);

        $this->notificationRepository->create([
            'user_id' => null,
            'target' => 'admin',
            'type' => 'course_enrollment_review_admin',
            'title' => 'Enrollment status changed',
            'body' => "{$student->name}'s request for {$course->title} was {$statusLabel}.{$noteText}",
            'is_read' => false,
        ]);
    }

    public function getForUser(int $userId): mixed
    {
        return $this->notificationRepository->getForUser($userId);
    }

    public function getForAdmin(): mixed
    {
        return $this->notificationRepository->getForAdmin();
    }

    public function unreadCountForUser(int $userId): int
    {
        return $this->notificationRepository->unreadCountForUser($userId);
    }

    public function unreadCountForAdmin(): int
    {
        return $this->notificationRepository->unreadCountForAdmin();
    }

    public function markRead(int $id): void
    {
        $this->notificationRepository->markRead($id);
    }

    public function markAllReadForUser(int $userId): void
    {
        $this->notificationRepository->markAllReadForUser($userId);
    }

    public function markAllReadForAdmin(): void
    {
        $this->notificationRepository->markAllReadForAdmin();
    }
}
