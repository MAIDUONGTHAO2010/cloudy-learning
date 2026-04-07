<?php

use App\Http\Controllers\Admin\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::get('login', function () {
    return view('admin');
});

Route::middleware('admin.valid')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('{any}', function () {
        return view('admin');
    })->where('any', '^(?!login(?:/|$)).*');
});
