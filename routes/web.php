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
