<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->controller(AuthController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::get('google/redirect', 'redirectToGoogle');
        Route::get('google/callback', 'handleGoogleCallback');
        Route::post('logout', 'logout')->middleware('auth:api');
    });

    Route::middleware('auth:api')->group(function () {
        Route::prefix('projects')->controller(ProjectController::class)->group(function () {
            Route::get('/', 'index');
            Route::post('/', 'store');
        });
        Route::apiResource('tasks', TaskController::class);
        Route::prefix('comments')->controller(CommentController::class)->group(function () {
            Route::post('/', 'store');
            Route::post('/{comment}/replies', 'reply');
            Route::patch('/{comment}', 'update');
            Route::delete('/{comment}', 'destroy');
        });
    });

});
