<?php

declare(strict_types=1);

/**
 * Plan Details Pages (view and edit modes)
 */
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UmamusumeController;
use App\Livewire\Dashboard\PlanDetailsPage;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

Route::get('/plans/{planId}/view', PlanDetailsPage::class)->name('plans.view');
Route::get('/plans/{planId}/edit', PlanDetailsPage::class)->name('plans.edit');

// Root dashboard route
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Umamusume roster
Route::get('/characters', [UmamusumeController::class, 'index'])->name('characters');

// Application guide page
Route::get('/guide', function () {
    return View::make('guide');
})->name('guide');
