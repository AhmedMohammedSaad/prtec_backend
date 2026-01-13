<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\CoursePostController;
use App\Http\Controllers\Api\EnrollmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication Routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public Course & Lesson View Routes
Route::get('/courses', [CourseController::class, 'index']); // Filtered by published
Route::get('/courses/{id}', [CourseController::class, 'show']);
Route::get('/courses/{id}/posts', [CoursePostController::class, 'index']); // Public list of lessons

// Protected Routes (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Management
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Student: View Lesson Content (Protected by Enrollment Policy)
    Route::get('/posts/{id}', [CoursePostController::class, 'show']);

    // Admin: Course Management
    Route::post('/courses', [CourseController::class, 'store']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{id}', [CourseController::class, 'destroy']);

    // Admin: Lesson Management
    Route::post('/courses/{courseId}/posts', [CoursePostController::class, 'store']);
    Route::put('/posts/batch-update', [CoursePostController::class, 'batchUpdate']); // Bulk reorder
    Route::put('/posts/{id}', [CoursePostController::class, 'update']);
    Route::delete('/posts/{id}', [CoursePostController::class, 'destroy']);

    // Enrollment Management
    Route::prefix('enrollments')->group(function () {
        Route::post('/', [EnrollmentController::class, 'store']); // Enroll
        Route::get('/my-courses', [EnrollmentController::class, 'myCourses']); // Student's courses
        Route::get('/', [EnrollmentController::class, 'index']); // Admin view all
    });
});
