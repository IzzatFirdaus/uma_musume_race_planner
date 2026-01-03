<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates career_snapshots table for immutable race-day snapshots
     * per requirements FR-12.1, FR-12.2
     */
    public function up(): void
    {
        Schema::create('career_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->foreignId('race_prediction_id')
                ->nullable()
                ->constrained()
                ->onDelete('set null');

            // Snapshot context
            $table->unsignedSmallInteger('turn_number');
            $table->string('race_name')->nullable();

            // Stats at snapshot time
            $table->unsignedSmallInteger('speed')->default(0);
            $table->unsignedSmallInteger('stamina')->default(0);
            $table->unsignedSmallInteger('power')->default(0);
            $table->unsignedSmallInteger('guts')->default(0);
            $table->unsignedSmallInteger('wit')->default(0);

            // Additional state
            $table->integer('total_sp_available')->nullable();
            $table->tinyInteger('stamina_percentage')->nullable();
            $table->string('mood', 50)->nullable();
            $table->text('conditions')->nullable();

            // Skills snapshot as JSON (acquired skills at this point)
            $table->json('skills_snapshot')->nullable();

            // Notes
            $table->text('notes')->nullable();

            // Immutable - only created_at, no updated_at
            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('plan_id', 'idx_career_snapshots_plan_id');
            $table->index('turn_number', 'idx_career_snapshots_turn_number');
            $table->index(['plan_id', 'turn_number'], 'idx_career_snapshots_plan_turn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_snapshots');
    }
};
