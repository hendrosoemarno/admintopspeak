<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hapus seluruh paket bertipe QUOTA dari Master Paket Langganan.
 * Paket langganan hanya bertipe TIME (durasi). user_subscriptions yang
 * menunjuk paket tersebut otomatis plan_id-nya jadi NULL (FK set null).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('subscription_plans')->where('type', 'QUOTA')->delete();
    }

    public function down(): void
    {
        // Tidak bisa dikembalikan (data paket QUOTA sudah dihapus).
    }
};