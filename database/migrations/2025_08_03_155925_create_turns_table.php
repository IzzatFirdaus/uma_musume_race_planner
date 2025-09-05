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
        Schema::create('turns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->integer('turn_number')->index();
            $table->integer('speed')->default(0);
            $table->integer('stamina')->default(0);
            $table->integer('power')->default(0);
            $table->integer('guts')->default(0);
            $table->integer('wit')->default(0);
            $table->unique(['plan_id', 'turn_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turns');
    }
};
