<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\JobApplicationController;

// ── Public Routes ─────────────────────────────────────────

// Auth - strict rate limit (5 per minute)
Route::middleware('throttle:auth')->prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
});

// Public job listing - global rate limit
Route::middleware('throttle:api')->group(function () {
    Route::get('/jobs',       [JobController::class, 'index']);
    Route::get('/jobs/{id}',  [JobController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
});

// ── Protected Routes ──────────────────────────────────────
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {

    // Auth
    Route::get('/profile',      [AuthController::class, 'profile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Candidate only
    Route::middleware('role:candidate')->group(function () {
        Route::middleware('throttle:applications')->group(function () {
            Route::post('/jobs/{jobId}/apply',  [JobApplicationController::class, 'apply']);
        });
        Route::get('/my-applications',          [JobApplicationController::class, 'myApplications']);
        Route::delete('/applications/{id}',     [JobApplicationController::class, 'withdraw']);
    });

    // Employer only
    Route::middleware('role:employer')->group(function () {
        Route::middleware('throttle:jobs')->group(function () {
            Route::post('/jobs',                [JobController::class, 'store']);
        });
        Route::put('/jobs/{id}',                        [JobController::class, 'update']);
        Route::delete('/jobs/{id}',                     [JobController::class, 'destroy']);
        Route::get('/my-jobs',                          [JobController::class, 'myJobs']);
        Route::get('/jobs/{jobId}/applications',        [JobApplicationController::class, 'jobApplications']);
        Route::put('/applications/{id}/status',         [JobApplicationController::class, 'updateStatus']);
    });

    // Admin only
    Route::middleware('role:admin')->group(function () {
        Route::post('/categories',              [CategoryController::class, 'store']);
        Route::delete('/categories/{id}',       [CategoryController::class, 'destroy']);
        Route::get('/applications',             [JobApplicationController::class, 'allApplications']);
    });
});