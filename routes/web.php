<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseReviewController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\Instructor\InstructorCourseController;
use App\Http\Controllers\Instructor\InstructorLessonController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProgressController;
use Illuminate\Support\Facades\Route;

// Auth API (JSON)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('logout', [AuthController::class, 'logout'])->middleware('auth');
    Route::get('me', [AuthController::class, 'me']);
});

// Public API
Route::prefix('api')->group(function () {
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('instructors', [InstructorController::class, 'index']);
    Route::get('courses', [CourseController::class, 'index']);
    Route::get('courses/filter-data', [CourseController::class, 'filterData']);
    Route::get('courses/popular', [CourseController::class, 'popular']);
    Route::get('courses/newest', [CourseController::class, 'newest']);
    Route::get('courses/instructors', [CourseController::class, 'instructors']);
    Route::get('courses/{slug}', [CourseController::class, 'show']);
    Route::get('courses/{courseId}/reviews', [CourseReviewController::class, 'index']);
    Route::post('contact', [ContactController::class, 'store']);
});

// Authenticated API (students & instructors only)
Route::prefix('api')->middleware(['auth', 'user.site'])->group(function () {
    Route::put('profile', [AuthController::class, 'updateProfile']);
    Route::put('change-password', [AuthController::class, 'changePassword']);
    Route::post('profile/presign-avatar', [AuthController::class, 'presignAvatar']);
    Route::get('my-courses', [CourseController::class, 'myCourses']);
    Route::get('instructor/course-requests', [CourseController::class, 'instructorRequests']);
    Route::put('instructor/course-requests/{courseId}/{userId}', [CourseController::class, 'reviewEnrollment']);
    Route::post('courses/{slug}/enroll', [CourseController::class, 'enroll']);

    // Course reviews
    Route::post('courses/{courseId}/reviews', [CourseReviewController::class, 'store']);
    Route::put('courses/{courseId}/reviews/{reviewId}', [CourseReviewController::class, 'update']);
    Route::delete('courses/{courseId}/reviews/{reviewId}', [CourseReviewController::class, 'destroy']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markRead']);

    // Student progress & quiz
    Route::get('lessons/{lessonId}/quiz', [ProgressController::class, 'getLessonQuiz']);
    Route::get('lessons/{lessonId}/progress', [ProgressController::class, 'getLessonProgress']);
    Route::post('lessons/{lessonId}/progress', [ProgressController::class, 'updateVideoProgress']);
    Route::post('lessons/{lessonId}/quiz', [ProgressController::class, 'submitQuiz']);
    Route::get('courses/{courseId}/progress', [ProgressController::class, 'getCourseProgress']);
});

// Instructor course management (instructor role only)
Route::prefix('api/instructor')->middleware(['auth', 'instructor'])->group(function () {
    Route::get('courses', [InstructorCourseController::class, 'index']);
    Route::post('courses/presign-thumbnail', [InstructorCourseController::class, 'presignThumbnail']);
    Route::post('courses', [InstructorCourseController::class, 'store']);
    Route::put('courses/{id}', [InstructorCourseController::class, 'update']);
    Route::delete('courses/{id}', [InstructorCourseController::class, 'destroy']);
    Route::get('courses/{id}/students', [InstructorCourseController::class, 'students']);
    Route::post('courses/{id}/students', [InstructorCourseController::class, 'addStudent']);
    Route::delete('courses/{id}/students/{userId}', [InstructorCourseController::class, 'removeStudent']);

    // Lessons
    Route::post('courses/{courseId}/lessons/presign-video', [InstructorLessonController::class, 'presignVideo']);
    Route::get('courses/{courseId}/lessons', [InstructorLessonController::class, 'index']);
    Route::post('courses/{courseId}/lessons', [InstructorLessonController::class, 'store']);
    Route::put('courses/{courseId}/lessons/{id}', [InstructorLessonController::class, 'update']);
    Route::delete('courses/{courseId}/lessons/{id}', [InstructorLessonController::class, 'destroy']);

    // Quiz
    Route::get('courses/{courseId}/lessons/{lessonId}/quiz', [InstructorLessonController::class, 'showQuiz']);
    Route::post('courses/{courseId}/lessons/{lessonId}/quiz', [InstructorLessonController::class, 'storeQuiz']);
    Route::delete('courses/{courseId}/quizzes/{quizId}', [InstructorLessonController::class, 'destroyQuiz']);
    Route::post('courses/{courseId}/questions/presign-media', [InstructorLessonController::class, 'presignQuestionMedia']);
    Route::post('courses/{courseId}/quizzes/{quizId}/questions', [InstructorLessonController::class, 'addQuestion']);
    Route::put('courses/{courseId}/questions/{questionId}', [InstructorLessonController::class, 'updateQuestion']);
    Route::delete('courses/{courseId}/questions/{questionId}', [InstructorLessonController::class, 'destroyQuestion']);
});

// User SPA — catch-all (must be last)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!admin(?:/|$)).*')->name('app');
