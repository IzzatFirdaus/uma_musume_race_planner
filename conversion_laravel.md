# PHP to Laravel 11 Conversion Plan: Uma Musume Race Planner

This document outlines a comprehensive plan for migrating the "Uma Musume Race Planner" application from its current custom PHP and MySQL stack to the Laravel 11 framework.

## ✨ Why Convert to Laravel?

Migrating from a custom PHP structure to a modern framework like Laravel 11 offers substantial, long-term benefits for the project:

- **Structure and Maintainability**: Laravel's Model-View-Controller (MVC) architecture will replace the current mix of procedural PHP files (`handle_plan_crud.php`, `get_*.php`). This cleanly separates database logic (Models), application logic (Controllers), and presentation (Views), making the code vastly easier to understand, manage, and scale.
- **Eloquent ORM**: Instead of writing raw SQL queries, you will use Laravel's Eloquent ORM. This provides a simple ActiveRecord implementation for working with your database, which helps prevent SQL injection vulnerabilities and makes defining table relationships intuitive.
- **Blade Templating**: Your current UI components (`components/`) and inline HTML will be converted to Blade templates (`.blade.php`). Blade is a powerful templating engine that allows for clean syntax, template inheritance (`@extends`), and reusable partials (`@include`).
- **Robust API Development**: The existing API endpoints will be replaced by Laravel's formal routing system (`routes/api.php`). This allows you to define clean, versionable, and resource-based routes for your CRUD operations, making the API more professional and easier to consume by the frontend.
- **Built-in Security**: Laravel provides out-of-the-box protection against common web vulnerabilities like Cross-Site Request Forgery (CSRF), Cross-Site Scripting (XSS), and SQL injection.
- **Future-Proofing**: The project's to-do list items, such as adding authentication and cloud sync, are dramatically simplified with Laravel. A complete login system can be added in minutes with `Laravel Breeze` and file storage can be handled with the `Storage` facade.

---

## 🚀 Conversion Plan: Step-by-Step

### Step 1: Project Setup & Initial Configuration

1.  **Install Laravel**: Use Composer to create a new Laravel 11 project.
    ```bash
    composer create-project laravel/laravel uma-musume-planner-laravel
    cd uma-musume-planner-laravel
    ```
2.  **Configure Environment**: Laravel uses a `.env` file by default. Copy your existing database credentials and application settings into the new `.env` file.

    ```env
    # .env - Laravel Configuration
    APP_NAME="Uma Musume Planner"
    DB_CONNECTION=mysql
    DB_HOST=localhost
    DB_PORT=3306
    DB_DATABASE=uma_musume_planner
    DB_USERNAME=root
    DB_PASSWORD=

    # Custom App Settings from original project
    APP_VERSION=v1.5.0-laravel
    APP_THEME_COLOR=#7d2b8b
    LAST_UPDATED="August 4, 2025"
    ```

---

### Step 2: Database and Models

This step focuses on rebuilding the database structure within the Laravel ecosystem.

1.  **Create Migrations**: Translate your `uma_musume_planner.sql` schema into Laravel migration files to make your database structure version-controllable. Generate a migration for each table.

    ```bash
    # Lookup Tables
    php artisan make:migration create_moods_table
    php artisan make:migration create_conditions_table
    php artisan make:migration create_strategies_table

    # Main Tables
    php artisan make:migration create_plans_table
    php artisan make:migration create_attributes_table
    php artisan make:migration create_skill_reference_table
    php artisan make:migration create_skills_table
    php artisan make:migration create_race_predictions_table
    php artisan make:migration create_activity_log_table
    php artisan make:migration create_terrain_grades_table
    php artisan make:migration create_distance_grades_table
    php artisan make:migration create_style_grades_table
    php artisan make:migration create_goals_table
    php artisan make:migration create_turns_table
    ```

2.  **Define Schema in Migrations**: Edit each generated migration file in `database/migrations/` to define the table columns using Laravel's schema builder.
    _Example for `create_plans_table.php` (Updated with full schema):_
    ```php
    // database/migrations/YYYY_MM_DD_HHMMSS_create_plans_table.php
    Schema::create('plans', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('plan_title')->nullable();
        $table->integer('turn_before')->nullable();
        $table->string('race_name')->nullable();
        $table->string('name')->index();
        $table->enum('career_stage', ['predebut', 'junior', 'classic', 'senior', 'finale'])->nullable();
        $table->enum('class', ['debut', 'maiden', 'beginner', 'bronze', 'silver', 'gold', 'platinum', 'star', 'legend'])->nullable();
        $table->string('time_of_day', 50)->nullable();
        $table->string('month', 50)->nullable();
        $table->integer('total_available_skill_points')->nullable();
        $table->enum('acquire_skill', ['YES', 'NO'])->default('NO');
        $table->foreignId('mood_id')->nullable()->constrained()->onDelete('set null');
        $table->foreignId('condition_id')->nullable()->constrained()->onDelete('set null');
        $table->tinyInteger('energy')->nullable();
        $table->enum('race_day', ['yes', 'no'])->default('no');
        $table->string('goal')->nullable();
        $table->foreignId('strategy_id')->nullable()->constrained()->onDelete('set null');
        $table->integer('growth_rate_speed')->default(0);
        $table->integer('growth_rate_stamina')->default(0);
        $table->integer('growth_rate_power')->default(0);
        $table->integer('growth_rate_guts')->default(0);
        $table->integer('growth_rate_wit')->default(0);
        $table->enum('status', ['Planning', 'Active', 'Finished', 'Draft', 'Abandoned'])->default('Planning')->index();
        $table->string('source')->nullable();
        $table->string('trainee_image_path')->nullable();
        $table->softDeletes(); // For soft-delete support
        $table->timestamps(); // Handles created_at and updated_at
    });
    ```
3.  **Create Models & Define Relationships**: Create an Eloquent Model for each database table and define the relationships between your data entities.

    ```bash
    php artisan make:model Plan
    php artisan make:model Attribute
    # ... and all other models ...
    ```

    _In your `app/Models/Plan.php` file, define the relationships to match the full schema:_

    ```php
    // app/Models/Plan.php
    namespace App\Models;
    use Illuminate\Database\Eloquent\Model;
    use Illuminate\Database\Eloquent\SoftDeletes;
    // ... other imports

    class Plan extends Model
    {
        use HasFactory, SoftDeletes;

        protected $fillable = [
            'user_id', 'plan_title', 'turn_before', 'race_name', 'name', 'career_stage',
            'class', 'time_of_day', 'month', 'total_available_skill_points', 'acquire_skill',
            'mood_id', 'condition_id', 'energy', 'race_day', 'goal', 'strategy_id',
            'growth_rate_speed', 'growth_rate_stamina', 'growth_rate_power',
            'growth_rate_guts', 'growth_rate_wit', 'status', 'source', 'trainee_image_path',
        ];

        // Relationship to the User who owns the plan
        public function user(): BelongsTo { return $this->belongsTo(User::class); }

        // Relationships to child tables
        public function attributes(): HasMany { return $this->hasMany(Attribute::class); }
        public function skills(): HasMany { return $this->hasMany(Skill::class); }
        public function goals(): HasMany { return $this->hasMany(Goal::class); }
        public function racePredictions(): HasMany { return $this->hasMany(RacePrediction::class); }
        public function turns(): HasMany { return $this->hasMany(Turn::class); }
        public function terrainGrades(): HasMany { return $this->hasMany(TerrainGrade::class); }
        public function distanceGrades(): HasMany { return $this->hasMany(DistanceGrade::class); }
        public function styleGrades(): HasMany { return $this->hasMany(StyleGrade::class); }

        // Relationships to parent lookup tables
        public function mood(): BelongsTo { return $this->belongsTo(Mood::class); }
        public function condition(): BelongsTo { return $this->belongsTo(Condition::class); }
        public function strategy(): BelongsTo { return $this->belongsTo(Strategy::class); }
    }
    ```

4.  **Run Migrations**: Use Artisan to execute the migrations and build your database schema.
    ```bash
    php artisan migrate
    ```

---

### Step 3: Routing and Controllers

This step replaces the loose `.php` file endpoints with a structured routing and controller system.

1.  **Define Routes**: Map your application's endpoints in `routes/web.php` for user-facing pages and `routes/api.php` for data-fetching.
    _Example `routes/web.php`:_

    ```php
    // routes/web.php
    use App\Http\Controllers\DashboardController;

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    ```

    _Example `routes/api.php`:_

    ```php
    // routes/api.php
    use App\Http\Controllers\Api\V1\PlanController;

    Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
        Route::apiResource('plans', PlanController::class);
        Route::post('plans/quick', [PlanController::class, 'storeQuick']); // Route for quick creation
    });
    ```

2.  **Create Controllers**: The logic from `handle_plan_crud.php` will be moved into a dedicated controller.
    ```bash
    php artisan make:controller DashboardController
    php artisan make:controller Api/V1/PlanController --api --model=Plan
    ```
3.  **Implement Controller Logic**: Populate the controller methods using Eloquent, including authentication, validation via Form Requests, and transaction management.
    _Create Form Requests for validation:_

    ```bash
    php artisan make:request StorePlanRequest
    php artisan make:request UpdatePlanRequest
    ```

    _Refined `app/Http/Controllers/Api/V1/PlanController.php` summary:_

    ```php
    // app/Http/Controllers/Api/V1/PlanController.php
    class PlanController extends Controller
    {
        use AuthorizesRequests;

        public function index(): JsonResponse { /* ... */ }
        public function store(StorePlanRequest $request): JsonResponse { /* ... */ }
        public function show(Plan $plan): JsonResponse { /* ... */ }
        public function update(UpdatePlanRequest $request, Plan $plan): JsonResponse { /* ... */ }
        public function destroy(Plan $plan): JsonResponse { /* ... */ }

        // New method for handling simplified plan creation
        public function storeQuick(Request $request): JsonResponse { /* ... */ }

        // Private helpers to keep controller actions clean
        private function syncSkills(Plan $plan, array $skillsData): void { /* ... */ }
        private function handleTraineeImageUpload(Request $request, Plan $plan): ?string { /* ... */ }
    }
    ```

---

### Step 4: Frontend Views and Assets

This step involves migrating the user interface and client-side scripts into the Blade and Vite ecosystem.

1.  **Convert UI to Blade Templates**: All UI code from `index.php`, `plan_details_modal.php`, and the `components/` directory is converted into `.blade.php` files inside `resources/views`.
    - **Master Layout (`resources/views/layouts/app.blade.php`)**: A single master layout holds the common HTML structure. It uses `@yield('content')` to inject page-specific content and `@stack('scripts')` for page-specific JavaScript.
    - **Dashboard View (`resources/views/dashboard.blade.php`)**: The main application interface, converted from `index.php`. It `@extends('layouts.app')` and defines the main content section.
    - **Reusable Partials (`@include`)**: The original PHP components are converted into Blade partials, organized by their scope.
        - `resources/views/layouts/partials/`: For site-wide components like `navbar.blade.php` and `footer.blade.php`.
        - `resources/views/dashboard/partials/`: For components specific to the dashboard, such as `stats-panel.blade.php`, `plan-list.blade.php`, and `recent-activity.blade.php`.
        - `resources/views/plans/partials/`: For partials related to the "Plan" resource, like `form-tabs.blade.php` and `copy-script.blade.php`.
2.  **Manage Assets with Vite**: Move your `css/` and `js/` files into `resources/css` and `resources/js`. Use Vite, Laravel's default asset bundler, to compile and version them.
    - In `vite.config.js`, reference your main asset files (`app.css`, `app.js`).
    - In your `app.blade.php` layout, include the compiled assets using the `@vite` directive.
        ```blade
        <head>
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        </head>
        ```
3.  **Update JavaScript**: Client-side JavaScript is updated to interact with the new Laravel backend.
    - **API Endpoints**: All `fetch()` requests are updated from old PHP scripts (`get_plans.php`, `handle_plan_crud.php`) to the new, versioned API routes (e.g., `fetch('/api/v1/plans')`).
    - **Data Injection**: Initial page data is passed from the `DashboardController` to `dashboard.blade.php` and injected into a global `window.plannerData` JavaScript object using the `@json` Blade directive.
        <!-- end list -->
    ```blade
    // In dashboard.blade.php
    @push('scripts')
    <script>
        window.plannerData = {
            plans: @json($plans ?? []),
            stats: @json($stats ?? []),
            // ... other data from the controller
        };
    </script>
    @endpush
    ```

---

### Step 5: Migrating Specific Features

1.  **Image Uploads**: Use Laravel's built-in `Storage` facade to handle the trainee image uploads.

    ```php
    // Example in PlanController's store or update method
    if ($request->hasFile('trainee_image')) {
        // 'public' disk makes it publicly accessible
        $path = $request->file('trainee_image')->store('trainee_images', 'public');
        $plan->update(['trainee_image_path' => $path]);
    }
    ```

    - Run `php artisan storage:link` once to create a symbolic link from `public/storage` to `storage/app/public`.

2.  **Database Seeding**: Convert your `sample_data.sql` file into Laravel Seeders. This allows you to easily populate your database with test data using a single Artisan command.

    ```bash
    php artisan make:seeder LookupSeeder
    php artisan make:seeder PlanSeeder
    ```

    - In the main `DatabaseSeeder.php`, you will call these individual seeders in the correct order to ensure foreign key constraints are met.
    - The logic from the `GetSkillReferenceId` SQL function will need to be replicated in your `PlanSeeder` using Eloquent's `firstOrCreate()` method to avoid duplicate skill references.
        ```bash
        php artisan db:seed
        ```

---

## 🗂️ Feature Mapping: Old vs. New

| Current Implementation (PHP/MySQL)      | Laravel 11 Equivalent                                    |
| :-------------------------------------- | :------------------------------------------------------- |
| `uma_musume_planner.sql` schema file    | `database/migrations/` files                             |
| PHP `mysqli_connect` / `PDO` connection | Eloquent Models in `app/Models/`                         |
| `includes/db_connect.php`               | `config/database.php` and `.env` file                    |
| `handle_plan_crud.php`, `get_*.php`     | `app/Http/Controllers/` with specific methods            |
| Direct URL access to `.php` files       | Defined routes in `routes/web.php` & `routes/api.php`    |
| `components/` & HTML in `.php` files    | Blade Templates in `resources/views/`                    |
| Manual SQL `INSERT`, `UPDATE` queries   | Eloquent ORM methods (`Plan::create()`, `$plan->save()`) |
| Manual file uploads to `uploads/`       | `Storage` facade (`storage/app/public/`)                 |
| `composer.json` (for Monolog)           | `composer.json` (managed by Laravel)                     |
| `sample_data.sql` for initial data      | Database Seeders in `database/seeders/`                  |
| To-Do: Optional Login                   | `Laravel Breeze` or `Laravel Fortify` starter kits       |
| To-Do: Cloud Sync Support               | `Storage` facade with a cloud driver (S3, etc.)          |
