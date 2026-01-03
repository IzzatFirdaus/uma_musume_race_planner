<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds consolidation fields per requirements FR-9.4, FR-9.7, FR-10.1
     */
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            // FR-10.1: Scenario field with default 'URA'
            $table->string('scenario', 50)->default('URA')->after('status');

            // FR-9.4: Storage mode enum (local or account)
            $table->enum('storage_mode', ['local', 'account'])->default('account')->after('scenario');

            // FR-9.7: Local UUID for local runs (nullable for account runs)
            $table->uuid('local_uuid')->nullable()->after('storage_mode');

            // Schema Canonicalization: stamina_percentage field
            // Note: 'energy' field exists but we add canonical name for clarity
            $table->tinyInteger('stamina_percentage')->nullable()->after('energy');

            // Add index for storage_mode queries
            $table->index('storage_mode', 'idx_plans_storage_mode');
            $table->index('scenario', 'idx_plans_scenario');
            $table->unique('local_uuid', 'idx_plans_local_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex('idx_plans_storage_mode');
            $table->dropIndex('idx_plans_scenario');
            $table->dropIndex('idx_plans_local_uuid');
            $table->dropColumn(['scenario', 'storage_mode', 'local_uuid', 'stamina_percentage']);
        });
    }
};
