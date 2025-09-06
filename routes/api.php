<?php

use App\Http\Controllers\Api\V1\AutosuggestController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\DashboardController;
// Removed unused use Illuminate\Http\Request;
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
Route::prefix('v1')->name('api.v1.')->group(function () {
    // Plan resource routes (index, show, store, update, destroy)
    Route::apiResource('plans', PlanController::class);

    // Custom routes for the PlanController
    Route::post('plans/quick', [PlanController::class, 'storeQuick'])
        ->name('plans.storeQuick');
    Route::get('plans/{plan}/progress-chart',
        [PlanController::class, 'progressChart'])
        ->name('plans.progressChart');

    // Route for the autosuggest functionality
    Route::get('autosuggest', [AutosuggestController::class, 'index'])
        ->name('autosuggest');

    // Routes for refreshing dashboard data
    Route::get('dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get(
        'dashboard/activities',
        [DashboardController::class, 'getActivities']
    )->name('dashboard.activities');
});
