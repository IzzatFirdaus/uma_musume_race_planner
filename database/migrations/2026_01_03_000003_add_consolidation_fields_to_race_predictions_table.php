<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds sort_order and position fields per requirements FR-5.3, FR-5.4
     */
    public function up(): void
    {
        Schema::table('race_predictions', function (Blueprint $table) {
            // FR-5.4: Sort order for manual reordering
            $table->unsignedSmallInteger('sort_order')->default(0)->after('id');

            // FR-5.3: Predicted and actual placement positions
            $table->unsignedTinyInteger('predicted_pos')->nullable()->after('comment');
            $table->unsignedTinyInteger('actual_pos')->nullable()->after('predicted_pos');

            // Add timestamps for tracking
            $table->timestamps();

            // Add index for sort order
            $table->index(['plan_id', 'sort_order'], 'idx_race_predictions_plan_sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('race_predictions', function (Blueprint $table) {
            $table->dropIndex('idx_race_predictions_plan_sort');
            $table->dropColumn(['sort_order', 'predicted_pos', 'actual_pos', 'created_at', 'updated_at']);
        });
    }
};
