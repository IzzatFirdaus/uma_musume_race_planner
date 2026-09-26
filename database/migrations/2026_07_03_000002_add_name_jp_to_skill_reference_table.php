<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds Japanese name field for bilingual search per REQ-SKILL-1.1
     */
    public function up(): void
    {
        Schema::table('skill_reference', function (Blueprint $table) {
            $table->string('name_jp')->nullable()->after('skill_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skill_reference', function (Blueprint $table) {
            $table->dropColumn('name_jp');
        });
    }
};
