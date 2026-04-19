<?php

namespace Tests\Feature;

use App\Enums\Course\EnrollmentStatus;
use App\Enums\User\UserRole;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_request_course_enrollment_and_see_it_in_my_courses(): void
    {
        $this->withoutMiddleware();

        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
        ]);

        $instructor = User::factory()->create([
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::query()->create([
            'user_id' => $instructor->id,
            'title' => 'Laravel for Beginners',
            'slug' => 'laravel-for-beginners',
            'description' => 'Intro course',
            'is_active' => true,
            'order' => 1,
        ]);

        $this->actingAs($student)
            ->postJson("/api/courses/{$course->slug}/enroll")
            ->assertOk()
            ->assertJson([
                'message' => 'Enrollment request submitted successfully',
            ]);

        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::REQUEST,
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $student->id,
            'target' => 'user',
            'type' => 'course_enrollment_request_user',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $instructor->id,
            'target' => 'user',
            'type' => 'course_enrollment_request_instructor',
        ]);

        $this->assertDatabaseHas('notifications', [
            'target' => 'admin',
            'type' => 'course_enrollment_request_admin',
        ]);

        $this->actingAs($student)
            ->getJson('/api/my-courses')
            ->assertOk()
            ->assertJsonFragment([
                'title' => 'Laravel for Beginners',
                'slug' => 'laravel-for-beginners',
                'enrollment_status' => EnrollmentStatus::REQUEST,
            ]);
    }

    public function test_only_first_lesson_is_free_until_request_is_approved(): void
    {
        $this->withoutMiddleware();

        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
        ]);

        $instructor = User::factory()->create([
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::query()->create([
            'user_id' => $instructor->id,
            'title' => 'Advanced Laravel',
            'slug' => 'advanced-laravel',
            'description' => 'Advanced course',
            'is_active' => true,
            'order' => 1,
        ]);

        Lesson::query()->create([
            'course_id' => $course->id,
            'title' => 'Introduction',
            'slug' => 'introduction',
            'content' => 'Free preview',
            'video_url' => 'http://example.com/intro.mp4',
            'order' => 1,
            'duration' => 10,
            'is_active' => true,
        ]);

        Lesson::query()->create([
            'course_id' => $course->id,
            'title' => 'Deep Dive',
            'slug' => 'deep-dive',
            'content' => 'Locked lesson',
            'video_url' => 'http://example.com/deep.mp4',
            'order' => 2,
            'duration' => 20,
            'is_active' => true,
        ]);

        $this->actingAs($student)
            ->postJson("/api/courses/{$course->slug}/enroll")
            ->assertOk();

        $this->actingAs($student)
            ->getJson("/api/courses/{$course->slug}")
            ->assertOk()
            ->assertJsonPath('can_access_full_course', false)
            ->assertJsonPath('lessons.0.is_locked', false)
            ->assertJsonPath('lessons.1.is_locked', true);

        $this->actingAs($instructor)
            ->putJson("/api/instructor/course-requests/{$course->id}/{$student->id}", [
                'status' => EnrollmentStatus::APPROVED,
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Enrollment approved successfully',
            ]);

        $this->actingAs($student)
            ->getJson("/api/courses/{$course->slug}")
            ->assertOk()
            ->assertJsonPath('can_access_full_course', true)
            ->assertJsonPath('lessons.1.is_locked', false);
    }

    public function test_canceling_request_requires_a_reason_note(): void
    {
        $this->withoutMiddleware();

        $student = User::factory()->create([
            'role' => UserRole::STUDENT,
        ]);

        $instructor = User::factory()->create([
            'role' => UserRole::INSTRUCTOR,
        ]);

        $course = Course::query()->create([
            'user_id' => $instructor->id,
            'title' => 'Vue Mastery',
            'slug' => 'vue-mastery',
            'description' => 'Vue course',
            'is_active' => true,
            'order' => 1,
        ]);

        $this->actingAs($student)
            ->postJson("/api/courses/{$course->slug}/enroll")
            ->assertOk();

        $this->actingAs($instructor)
            ->putJson("/api/instructor/course-requests/{$course->id}/{$student->id}", [
                'status' => EnrollmentStatus::CANCELED,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['note']);

        $this->actingAs($instructor)
            ->putJson("/api/instructor/course-requests/{$course->id}/{$student->id}", [
                'status' => EnrollmentStatus::CANCELED,
                'note' => 'Need more preparation before joining this class.',
            ])
            ->assertOk()
            ->assertJson([
                'message' => 'Enrollment canceled successfully',
            ]);

        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $student->id,
            'status' => EnrollmentStatus::CANCELED,
            'note' => 'Need more preparation before joining this class.',
        ]);
    }
}
