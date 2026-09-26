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
        Schema::table('turns', function (Blueprint $table) {
            $table->integer('stamina_percentage')->default(100)->after('wit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('turns', function (Blueprint $table) {
            $table->dropColumn('stamina_percentage');
        });
    }
};
