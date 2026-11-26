<?php

use Illuminate\Support\Facades\Route;
use Modules\LMS\Http\Controllers\Api\Course\CourseBenefitController;
use Modules\LMS\Http\Controllers\Api\Course\CourseController;
use Modules\LMS\Http\Controllers\Api\Course\CourseMentorController;
use Modules\LMS\Http\Controllers\Api\Course\CourseSectionController;
use Modules\LMS\Http\Controllers\Api\Course\CourseStudentController;
use Modules\LMS\Http\Controllers\Api\Course\CourseTestimoniController;
use Modules\LMS\Http\Controllers\Api\Course\SectionContentController;
use Modules\LMS\Http\Controllers\Api\Course\CourseProgressController;

Route::prefix('v1')->group(function () {

    Route::prefix('courses')->group(function () {
        Route::get('/', [CourseController::class, 'index']);
        Route::get('/{slug}', [CourseController::class, 'show']);
        Route::get('/{slug}/benefits', [CourseBenefitController::class, 'index']);
        Route::get('/{slug}/testimonis', [CourseTestimoniController::class, 'index']);
    });

    Route::prefix('courses/{slug}')->group(function () {
        Route::get('/sections', [CourseSectionController::class, 'index']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/my-courses', [CourseStudentController::class, 'myCourses']);

        Route::prefix('sections/{courseSection}')->group(function () {
            Route::get('/contents', [SectionContentController::class, 'index']);
        });

        Route::post('/contents/{content}/complete', [CourseProgressController::class, 'complete']);
        Route::get('/courses/{slug}/progress', [CourseProgressController::class, 'show']);
        Route::get('/courses/progress/{slug}', [CourseProgressController::class, 'show']);

        Route::prefix('contents')->group(function () {
            Route::get('/{content}', [SectionContentController::class, 'show']);
        });

        Route::prefix('courses/{slug}')->group(function () {
            Route::post('/testimonis', [CourseTestimoniController::class, 'store']);

            Route::post('/enroll', [CourseStudentController::class, 'enroll']);
            Route::delete('/unenroll', [CourseStudentController::class, 'unenroll']);

            Route::get('/mentors', [CourseMentorController::class, 'index']);
        });

        Route::prefix('testimonis')->group(function () {
            Route::put('/{testimoni}/update', [CourseTestimoniController::class, 'update']);
            Route::delete('/{testimoni}/delete', [CourseTestimoniController::class, 'destroy']);
        });

        Route::middleware('role:admin-pusat')->group(function () {
            Route::prefix('courses')->group(function () {
                Route::post('/', [CourseController::class, 'store']);
                Route::put('/{slug}', [CourseController::class, 'update']);
                Route::delete('/{slug}', [CourseController::class, 'destroy']);

                Route::post('/{slug}/benefits/', [CourseBenefitController::class, 'store']);
                Route::put('/benefits/{benefitId}/update', [CourseBenefitController::class, 'update']);
                Route::delete('/benefits/{benefitId}/delete', [CourseBenefitController::class, 'destroy']);
            });

            Route::prefix('courses/{slug}')->group(function () {
                Route::get('/students', [CourseStudentController::class, 'index']);
                Route::patch('/students/{userId}/status', [CourseStudentController::class, 'updateStatus']);

                Route::post('/mentors', [CourseMentorController::class, 'store']);
                Route::patch('/mentors/{userId}/activate', [CourseMentorController::class, 'toggleMentorActivation']);
                Route::delete('/mentors/{userId}/delete', [CourseMentorController::class, 'destroy']);
            });

            Route::prefix('courses/{slug}/sections')->group(function () {
                Route::post('/', [CourseSectionController::class, 'store']);
                Route::patch('/reorder', [CourseSectionController::class, 'reorder']);
            });

            Route::prefix('sections')->group(function () {
                Route::put('/{section}', [CourseSectionController::class, 'update']);
                Route::delete('/{section}', [CourseSectionController::class, 'destroy']);
            });

            Route::prefix('sections/{courseSection}/contents')->group(function () {
                Route::post('/', [SectionContentController::class, 'store']);
                Route::patch('/reorder', [SectionContentController::class, 'reorder']);
            });

            Route::prefix('contents')->group(function () {
                Route::put('/{content}/update', [SectionContentController::class, 'update']);
                Route::delete('/{content}/delete', [SectionContentController::class, 'destroy']);
            });
        });
    });
});
