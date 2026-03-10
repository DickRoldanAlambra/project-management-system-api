<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    //Auth
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('guest');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('guest');
    Route::post('auth/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
    Route::get('/me', [AuthController::class, 'me'])->middleware('auth:sanctum');

    Route::middleware('auth:sanctum')->group(function () {
        // Projects: read for all, write for admin
        Route::get('projects', [ProjectController::class, 'index']);
        Route::get('projects/{project}', [ProjectController::class, 'show']);

        Route::middleware('role:admin')->group(function () {
            Route::post('projects', [ProjectController::class, 'store']);
            Route::put('projects/{project}', [ProjectController::class, 'update']);
            Route::delete('projects/{project}', [ProjectController::class, 'destroy']);
        });

        // Tasks nested under projects
        Route::get('projects/{project}/tasks', [TaskController::class, 'index']);
        Route::get('tasks/{task}', [TaskController::class, 'show']);

        // Manager: create & delete tasks
        Route::middleware('role:manager')->group(function () {
            Route::post('projects/{project}/tasks', [TaskController::class, 'store']);
            Route::delete('tasks/{task}', [TaskController::class, 'destroy']);
        });

        // Manager or assigned user: update task
        Route::put('tasks/{task}', [TaskController::class, 'update']);

        // Comments nested under tasks
        Route::get('tasks/{task}/comments', [CommentController::class, 'index']);
        Route::post('tasks/{task}/comments', [CommentController::class, 'store']);
    });
});
