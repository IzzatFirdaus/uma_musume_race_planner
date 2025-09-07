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
        Schema::create('umamusume', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('nickname')->nullable();
            $table->string('team')->nullable();
            $table->string('release_batch')->nullable();
            $table->string('cv')->nullable();
            $table->string('birthday')->nullable();
            $table->integer('height_cm')->nullable();
            $table->string('weight')->nullable();
            $table->json('three_sizes')->nullable();
            $table->json('images')->nullable();
            $table->unsignedTinyInteger('rarity')->default(3);
            $table->json('growth_rates')->nullable();
            $table->json('aptitudes')->nullable();
            $table->json('base_stats')->nullable();
            $table->json('unique_skill')->nullable();
            $table->json('skills')->nullable();
            $table->json('career_goals')->nullable();
            $table->json('tags')->nullable();
            $table->json('ui')->nullable();
            $table->json('links')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('umamusume');
    }
};
