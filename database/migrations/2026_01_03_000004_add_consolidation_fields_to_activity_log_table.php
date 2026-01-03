<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds user scoping and metadata per requirements FR-8.1, FR-8.4
     */
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // FR-8.1: User scoping (nullable for anonymous/system actions)
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained()
                ->onDelete('set null');

            // FR-8.4: Polymorphic model reference
            $table->string('model_type', 100)->nullable()->after('description');
            $table->unsignedBigInteger('model_id')->nullable()->after('model_type');

            // FR-8.4: Additional metadata as JSON
            $table->json('metadata')->nullable()->after('model_id');

            // Add indexes for common queries
            $table->index('user_id', 'idx_activity_log_user_id');
            $table->index('timestamp', 'idx_activity_log_timestamp');
            $table->index(['model_type', 'model_id'], 'idx_activity_log_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex('idx_activity_log_user_id');
            $table->dropIndex('idx_activity_log_timestamp');
            $table->dropIndex('idx_activity_log_model');
            $table->dropColumn(['user_id', 'model_type', 'model_id', 'metadata']);
        });
    }
};
