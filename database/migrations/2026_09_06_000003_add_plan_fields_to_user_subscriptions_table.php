<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan referensi paket & sumber pada UserSubscription agar assignment manual
 * (hadiah/komplain/offline) dapat dilacak dan dibedakan dari pembayaran gateway.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('user_id')
                ->constrained('subscription_plans')->onDelete('set null');
            $table->string('source', 20)->default('GATEWAY')->after('plan_id')
                ->comment('GATEWAY | MANUAL');
            $table->string('admin_note', 255)->nullable()->after('source')
                ->comment('Catatan admin utk assignment manual');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn(['source', 'admin_note']);
        });
    }
};
