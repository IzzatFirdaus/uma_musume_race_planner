<?php

declare(strict_types=1);

/**
 * Plan Details Pages (view and edit modes)
 */
use App\Livewire\Dashboard\PlanDetailsPage;
use App\Models\Umamusume;
use Illuminate\Support\Facades\Route;

// RESTful routes for plans. Use existing Livewire page `PlanDetailsPage` for show/edit
// and map index to the dashboard view to keep behaviour consistent with existing app.
Route::prefix('plans')->name('plans.')->group(function () {
    // GET /plans -> plans.index (dashboard contains the plan list)
    Route::view('/', 'dashboard')->name('index');

    // GET /plans/{planId} -> plans.show (view mode)
    // Historically some views and links use the name `plans.view`.
    // Provide an explicit /{planId}/view route named `plans.view` to preserve
    // backwards compatibility with templates and Livewire components.
    Route::get('/{planId}/view', PlanDetailsPage::class)->name('view');

    // GET /plans/{planId} -> plans.show (view mode)
    Route::get('/{planId}', PlanDetailsPage::class)->name('show');

    // GET /plans/{planId}/edit -> plans.edit (edit mode)
    Route::get('/{planId}/edit', PlanDetailsPage::class)->name('edit');
});

// Root: serve the dashboard view at the base URL so tests visiting '/' see the plan list.
// Keep the explicit /dashboard route as well for direct access.
Route::view('/', 'dashboard')->name('dashboard.root');

// Dashboard route - render the top-level Blade dashboard view which includes
// Livewire page components inside the application's layout. Using the
// Blade view ensures the canonical layout (header, navbar, main) is present
// for Playwright accessibility and E2E checks.
Route::view('/dashboard', 'dashboard')->name('dashboard');

// Umamusume roster (Blade view) - provide Umamusume data so the Blade template has $umamusume
Route::get('/characters', function () {
    $umamusume = Umamusume::query()->orderBy('name')->get();

    return view('characters', compact('umamusume'));
})->name('characters');

// Application guide page (Blade view)
Route::view('/guide', 'guide')->name('guide');
