<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\TaskController;
use App\Http\Controllers\Api\V1\SubTaskController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\UserController;

Route::prefix('v1')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('register', 'register')->name('auth.register');
        Route::post('login', 'login')->name('auth.login');
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', 'logout')->name('auth.logout');
            Route::get('me', 'me')->name('auth.me');
            // Optional OAuth Routes
            Route::get('auth/google', 'oAuthUrl')->name('auth.google.url');
            Route::get('auth/google/callback', 'oAuthCallback')->name('auth.google.callback');
        });
    });
    // Route lainnya akan ditambahkan di sini nanti...
});

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/oauth/google', [AuthController::class, 'oAuthUrl']);
        Route::get('/oauth/google/callback', [AuthController::class, 'oAuthCallback']);
        Route::middleware('auth:sanctum')->group(function() {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/me', [UserController::class, 'me']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::delete('/user/delete-avatar', [UserController::class, 'deleteAvatar']);
    Route::post('/user/change-password', [UserController::class, 'changePassword']);
    });

    Route::get('plans', [PlanController::class, 'index']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('tasks', TaskController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::post('tasks/{id}', [TaskController::class, 'update']);
        Route::post('/subtasks/change-status', [SubtaskController::class, 'changeStatus']);
        Route::apiResource('subtasks', SubtaskController::class)->only(['index', 'destroy']);
        Route::post('subtasks', [SubtaskController::class, 'store']);
        Route::post('subtasks/{id}', [SubtaskController::class, 'update']);        
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);
    });
    Route::post('/payments/callback', [PaymentController::class, 'callback']);

});
