<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds 3-state status and turn_acquired per requirements FR-4.2, FR-4.3, FR-4.5
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            // FR-4.2, FR-4.3: 3-state status enum replacing binary 'acquired'
            $table->enum('status', ['acquired', 'skipped', 'suggested'])
                ->default('suggested')
                ->after('acquired');

            // FR-4.5: Turn when skill was acquired (required when status = acquired)
            $table->unsignedSmallInteger('turn_acquired')->nullable()->after('status');

            // Add soft deletes per NFR-4
            $table->softDeletes();

            // Add timestamps for tracking
            $table->timestamps();
        });

        // Migrate existing 'acquired' data to new 'status' field
        DB::statement("UPDATE skills SET status = CASE WHEN acquired = 'yes' THEN 'acquired' ELSE 'suggested' END");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['status', 'turn_acquired', 'created_at', 'updated_at']);
        });
    }
};
