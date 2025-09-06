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
        Schema::create('skill_reference', function (Blueprint $table) {
            $table->id();
            $table->string('skill_name')->unique();
            $table->text('description')->nullable();
            $table->string('stat_type', 50)->nullable();
            $table->text('best_for')->nullable();
            $table->string('tag', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_reference');
    }
};
