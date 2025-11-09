<?php

use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('projects')->controller(ProjectController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
    });
    Route::apiResource('tasks', TaskController::class);


});
