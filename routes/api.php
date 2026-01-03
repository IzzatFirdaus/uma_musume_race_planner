<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\AutosuggestController;
use App\Http\Controllers\Api\V1\ExportController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\SkillController;
use App\Http\Controllers\Api\V1\StatProgressController;
use App\Http\Controllers\Api\V1\UmaMusumeController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| API routes are now public and do not require authentication.
|
*/

/**
 * API Version 1 Routes
 */
Route::prefix('v1')->name('api.v1.')->group(function (): void {
    // Plan resource routes (index, show, store, update, destroy)
    Route::apiResource('plans', PlanController::class);

    // Custom routes for the PlanController
    Route::post('plans/quick', [PlanController::class, 'storeQuick'])
        ->name('plans.storeQuick');
    Route::get('plans/{plan}/progress-chart', [PlanController::class, 'progressChart'])
        ->name('plans.progressChart');

    // Stat Progress routes for plans
    Route::prefix('plans/{plan}/stats')->name('plans.stats.')->group(function (): void {
        Route::get('/', [StatProgressController::class, 'index'])->name('index');
        Route::post('/', [StatProgressController::class, 'store'])->name('store');
        Route::get('/totals', [StatProgressController::class, 'totals'])->name('totals');
        Route::get('/averages', [StatProgressController::class, 'averages'])->name('averages');
        Route::get('/chart', [StatProgressController::class, 'chart'])->name('chart');
        Route::get('/summary', [StatProgressController::class, 'summary'])->name('summary');
        Route::get('/{stat}', [StatProgressController::class, 'show'])->name('show');
        Route::put('/{stat}', [StatProgressController::class, 'update'])->name('update');
        Route::delete('/{stat}', [StatProgressController::class, 'destroy'])->name('destroy');
    });

    // Skill routes for plans
    Route::prefix('plans/{plan}/skills')->name('plans.skills.')->group(function (): void {
        Route::get('/', [SkillController::class, 'forPlan'])->name('index');
        Route::post('/', [SkillController::class, 'store'])->name('store');
        Route::get('/totals', [SkillController::class, 'totals'])->name('totals');
        Route::put('/{skill}', [SkillController::class, 'update'])->name('update');
        Route::delete('/{skill}', [SkillController::class, 'destroy'])->name('destroy');
    });

    // Skill reference routes (global)
    Route::get('skills', [SkillController::class, 'index'])->name('skills.index');
    Route::get('skills/search', [SkillController::class, 'search'])->name('skills.search');

    // Route for the autosuggest functionality
    Route::get('autosuggest', [AutosuggestController::class, 'index'])
        ->name('autosuggest');

    // Routes for refreshing dashboard data
    Route::get('dashboard/stats', [DashboardController::class, 'getStats'])
        ->name('dashboard.stats');
    Route::get('dashboard/activities', [DashboardController::class, 'getActivities'])
        ->name('dashboard.activities');

    // UmaMusume API routes (full CRUD)
    Route::apiResource('uma-musume', UmaMusumeController::class);
    Route::get('uma-musume-search', [UmaMusumeController::class, 'search'])
        ->name('uma-musume.search');

    // Export routes
    Route::prefix('export')->name('export.')->group(function (): void {
        Route::get('formats', [ExportController::class, 'formats'])->name('formats');
        Route::get('career-run/{plan}', [ExportController::class, 'exportPlan'])->name('plan');
        Route::get('career-run/{plan}/preview', [ExportController::class, 'preview'])->name('preview');
        Route::post('bulk', [ExportController::class, 'exportBulk'])->name('bulk');
    });
});
