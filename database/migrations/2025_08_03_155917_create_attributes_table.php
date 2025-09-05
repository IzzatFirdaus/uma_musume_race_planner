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
        // Make sure that the plans table is created before this migration!
        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            // Ensure plan_id matches the type of plans.id (bigint unsigned)
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->string('attribute_name', 50);
            $table->integer('value');
            $table->string('grade', 10)->nullable();
            $table->unique(['plan_id', 'attribute_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attributes');
    }
};
