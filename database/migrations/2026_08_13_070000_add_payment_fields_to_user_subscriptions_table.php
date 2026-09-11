<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->string('merchant_order_id', 64)->nullable()->unique()->index();
            $table->decimal('amount', 12, 2)->default(0);
            $table->enum('payment_status', ['PENDING', 'PAID', 'EXPIRED', 'FAILED'])->default('PENDING');
            $table->string('payment_method', 30)->nullable()->comment('Kanal pembayaran Duitku: VA, QR, dll.');
            $table->string('checkout_url', 500)->nullable()->comment('paymentUrl dari Duitku');
        });
    }

    public function down(): void
    {
        Schema::table('user_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['merchant_order_id', 'amount', 'payment_status', 'payment_method', 'checkout_url']);
        });
    }
};