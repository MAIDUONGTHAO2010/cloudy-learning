<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\CategoryController;
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
    });

    Route::get('{any}', function () {
        return view('admin');
    })->where('any', '^(?!login(?:/|$)).*');
});
