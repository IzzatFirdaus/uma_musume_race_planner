<?php

declare(strict_types=1);

/**
 * Plan Details Pages (view and edit modes)
 */

use App\Livewire\Dashboard\PlanDetailsPage;
use App\Livewire\Plans\LocalPlanView;
use Illuminate\Support\Facades\Route;

// RESTful routes for plans. Use existing Livewire page `PlanDetailsPage` for show/edit
// and map index to the dashboard view to keep behaviour consistent with existing app.
Route::prefix('plans')->name('plans.')->group(function () {
    // GET /plans -> plans.index (dashboard contains the plan list)
    Route::view('/', 'dashboard')->name('index');

    // Local plan routes (Req 56.2, 56.3, 56.5)
    // Routes for plans stored in localStorage, identified by UUID
    Route::prefix('local')->name('local.')->group(function () {
        // GET /plans/local/{uuid} -> plans.local.show (view mode)
        Route::get('/{uuid}', LocalPlanView::class)->name('show');

        // GET /plans/local/{uuid}/view -> plans.local.view (explicit view mode)
        Route::get('/{uuid}/view', LocalPlanView::class)->name('view');

        // GET /plans/local/{uuid}/edit -> plans.local.edit (edit mode)
        Route::get('/{uuid}/edit', LocalPlanView::class)->name('edit');
    });

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

// Local Data Management (FR-9D.1)
// Manages locally stored plans - export, import, convert to account
Route::get('/local-data', \App\Livewire\LocalData\Manager::class)->name('local-data');

// Import Wizard (FR-6B / Requirements 68.1-68.10)
// Multi-step wizard for importing career plans from JSON files
Route::get('/import', \App\Livewire\Import\ImportWizard::class)->name('import');

// Umamusume roster (Livewire page component)
// Requirements: 11.1-11.6 - Character roster browsing with filtering
Route::get('/characters', \App\Livewire\Characters\CharacterList::class)->name('characters');

// Application guide page (Blade view)
Route::view('/guide', 'guide')->name('guide');

// Authentication routes (optional - for users who want to sync across devices)
Route::view('/login', 'auth.login')->name('login');
Route::view('/register', 'auth.register')->name('register');
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->name('logout');
