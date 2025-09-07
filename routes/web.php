<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| All routes are now public and do not require authentication.
|
*/

/**
 * The main application dashboard is now the root page.
 */
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

/**
 * The application guide page.
 */
Route::get('/guide', fn () => view('guide'))->name('guide');

/**
 * The Umamusume Roster page.
 */
Route::get('/characters', [App\Http\Controllers\UmamusumeController::class, 'index'])->name('characters');

/**
 * Plan Details Pages (view and edit modes)
 */
use App\Livewire\Dashboard\PlanDetailsPage;

Route::get('/plans/{planId}/view', PlanDetailsPage::class)->name('plans.view');
Route::get('/plans/{planId}/edit', PlanDetailsPage::class)->name('plans.edit');
