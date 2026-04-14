<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseReviewController;
use App\Http\Controllers\Admin\InstructorController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\QuizController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::get('login', function () {
    return view('admin');
});

Route::middleware('admin.valid')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    // API routes
    Route::prefix('api')->group(function () {
        Route::get('categories', [CategoryController::class, 'index']);
        Route::post('categories', [CategoryController::class, 'store']);
        Route::put('categories/{id}', [CategoryController::class, 'update']);
        Route::delete('categories/{id}', [CategoryController::class, 'destroy']);
        Route::get('categories/{id}/children', [CategoryController::class, 'children']);

        // Instructors
        Route::get('instructors', [InstructorController::class, 'index']);

        // Courses — reorder must come before {id} wildcard
        Route::post('courses/reorder', [CourseController::class, 'reorder']);
        Route::get('courses', [CourseController::class, 'index']);
        Route::get('courses/{id}', [CourseController::class, 'show']);
        Route::post('courses', [CourseController::class, 'store']);
        Route::put('courses/{id}', [CourseController::class, 'update']);
        Route::delete('courses/{id}', [CourseController::class, 'destroy']);

        // Course reviews (read-only, no delete)
        Route::get('courses/{courseId}/reviews', [CourseReviewController::class, 'index']);

        // Lessons (scoped to course) — reorder must come before {courseId}/lessons wildcard
        Route::post('courses/{courseId}/lessons/reorder', [LessonController::class, 'reorder']);
        Route::get('courses/{courseId}/lessons', [LessonController::class, 'index']);
        Route::post('courses/{courseId}/lessons', [LessonController::class, 'store']);
        Route::put('lessons/{id}', [LessonController::class, 'update']);
        Route::delete('lessons/{id}', [LessonController::class, 'destroy']);

        // Quiz (scoped to lesson)
        Route::get('lessons/{lessonId}/quiz', [QuizController::class, 'show']);
        Route::post('lessons/{lessonId}/quiz', [QuizController::class, 'store']);
        Route::delete('quizzes/{quizId}', [QuizController::class, 'destroy']);
        Route::put('questions/{questionId}', [QuizController::class, 'updateQuestion']);
        Route::post('quizzes/{quizId}/questions', [QuizController::class, 'addQuestion']);
        Route::delete('questions/{questionId}', [QuizController::class, 'destroyQuestion']);
    });

    Route::get('{any}', function () {
        return view('admin');
    })->where('any', '^(?!login(?:/|$)).*');
});
