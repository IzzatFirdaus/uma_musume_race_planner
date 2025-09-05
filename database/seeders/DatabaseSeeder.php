<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Always clear out users before inserting test user to avoid duplicate email error.
        DB::table('users')->delete();

        // Create a default user for development and testing.
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Call the other seeders in the correct order.
        $this->call([
            LookupSeeder::class,
            SkillReferenceSeeder::class,
            PlanSeeder::class,
            ActivityLogSeeder::class,
        ]);
    }
}
