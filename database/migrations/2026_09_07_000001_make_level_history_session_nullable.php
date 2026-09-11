<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jadikan session_id & trigger_score nullable pada user_level_histories.
 * Perubahan level manual oleh admin tidak memiliki sesi/skor trigger.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_level_histories', function (Blueprint $table) {
            $table->string('session_id', 36)->nullable()->change();
            $table->integer('trigger_score')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_level_histories', function (Blueprint $table) {
            $table->string('session_id', 36)->change();
            $table->integer('trigger_score')->change();
        });
    }
};