<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('race_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->string('race_name')->nullable();
            $table->string('venue')->nullable();
            $table->string('ground', 50)->nullable();
            $table->string('distance', 50)->nullable();
            $table->string('track_condition', 50)->nullable();
            $table->string('direction', 50)->nullable();
            $table->string('speed', 10)->default('○');
            $table->string('stamina', 10)->default('○');
            $table->string('power', 10)->default('○');
            $table->string('guts', 10)->default('○');
            $table->string('wit', 10)->default('○');
            $table->text('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('race_predictions');
    }
};
