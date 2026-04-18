<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\NotificationController;
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
    Route::post('contact', [ContactController::class, 'store']);
});

// Authenticated API (students & instructors only)
Route::prefix('api')->middleware(['auth', 'user.site'])->group(function () {
    Route::put('profile', [AuthController::class, 'updateProfile']);

    // Notifications
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markRead']);
});

// User SPA — catch-all (must be last)
Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!admin(?:/|$)).*')->name('app');
