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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            // This links the plan to a user and deletes the plan if the user is deleted.
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('plan_title')->nullable();
            $table->integer('turn_before')->nullable();
            $table->string('race_name')->nullable();
            $table->string('name')->index();
            $table->enum('career_stage', ['predebut', 'junior', 'classic', 'senior', 'finale'])->nullable();
            $table->enum('class', ['debut', 'maiden', 'beginner', 'bronze', 'silver', 'gold', 'platinum', 'star', 'legend'])->nullable();
            $table->string('time_of_day', 50)->nullable();
            $table->string('month', 50)->nullable();
            $table->integer('total_available_skill_points')->nullable();
            $table->enum('acquire_skill', ['YES', 'NO'])->default('NO');
            $table->foreignId('mood_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('condition_id')->nullable()->constrained()->onDelete('set null');
            $table->tinyInteger('energy')->nullable();
            $table->enum('race_day', ['yes', 'no'])->default('no');
            $table->string('goal')->nullable();
            $table->foreignId('strategy_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('growth_rate_speed')->default(0);
            $table->integer('growth_rate_stamina')->default(0);
            $table->integer('growth_rate_power')->default(0);
            $table->integer('growth_rate_guts')->default(0);
            $table->integer('growth_rate_wit')->default(0);
            $table->enum('status', ['Planning', 'Active', 'Finished', 'Draft', 'Abandoned'])->default('Planning')->index();
            $table->string('source')->nullable();
            $table->string('trainee_image_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
