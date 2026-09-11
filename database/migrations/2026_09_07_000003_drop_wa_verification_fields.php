<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus fitur verifikasi WhatsApp:
 * - users.is_wa_verified
 * - app_configurations.free_tier_wa_verification_bonus (bonus sesi verifikasi WA)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_wa_verified');
        });

        Schema::table('app_configurations', function (Blueprint $table) {
            $table->dropColumn('free_tier_wa_verification_bonus');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_wa_verified')->default(false);
        });

        Schema::table('app_configurations', function (Blueprint $table) {
            $table->unsignedInteger('free_tier_wa_verification_bonus')->default(4);
        });
    }
};