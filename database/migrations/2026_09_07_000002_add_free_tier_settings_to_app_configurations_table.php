<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pengaturan Free Tier pada app_configurations:
 * - free_tier_initial_sessions       : sesi awal untuk guest baru (default 1)
 * - free_tier_wa_verification_bonus  : bonus sesi setelah verifikasi WA (default 4)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_configurations', function (Blueprint $table) {
            $table->unsignedInteger('free_tier_initial_sessions')->default(1)->after('update_message');
            $table->unsignedInteger('free_tier_wa_verification_bonus')->default(4)->after('free_tier_initial_sessions');
        });
    }

    public function down(): void
    {
        Schema::table('app_configurations', function (Blueprint $table) {
            $table->dropColumn(['free_tier_initial_sessions', 'free_tier_wa_verification_bonus']);
        });
    }
};