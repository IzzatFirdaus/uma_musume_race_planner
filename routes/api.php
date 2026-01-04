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
| API routes with optional authentication for read operations.
|
*/

/**
 * Health check endpoint for connection state monitoring
 * Requirements: 78.2
 */
Route::get('health', function () {
    return response()->json(['status' => 'ok'], 200);
})->name('api.health');

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

    // Skill routes for plans - order matters: specific routes before parameterized routes
    Route::prefix('plans/{plan}/skills')->name('plans.skills.')->group(function (): void {
        Route::get('/totals', [SkillController::class, 'totals'])->name('totals');
        Route::get('/', [SkillController::class, 'forPlan'])->name('index');
        Route::post('/', [SkillController::class, 'store'])->name('store');
        Route::get('/{skill}', [SkillController::class, 'show'])->name('show');
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

    // UmaMusume API routes - search must come before apiResource
    Route::get('umamusume/search', [UmaMusumeController::class, 'search'])
        ->name('umamusume.search');
    Route::apiResource('umamusume', UmaMusumeController::class);

    // Export routes - plan-specific exports
    Route::get('plans/{plan}/export/preview', [ExportController::class, 'preview'])
        ->name('plans.export.preview');
    Route::get('plans/{plan}/export/{format}', [ExportController::class, 'exportPlan'])
        ->where('format', 'json|csv|markdown')
        ->name('plans.export');

    // Bulk export routes
    Route::prefix('export')->name('export.')->group(function (): void {
        Route::get('formats', [ExportController::class, 'formats'])->name('formats');
        Route::get('plans', [ExportController::class, 'exportBulk'])->name('plans');
    });
});
