<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('total_free_sessions_granted')
                ->default(0)
                ->after('remaining_trial_sessions');
        });

        // Backfill: denominator minimal = sisa sesi saat ini.
        DB::table('users')->update([
            'total_free_sessions_granted' => DB::raw('remaining_trial_sessions'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('total_free_sessions_granted');
        });
    }
};