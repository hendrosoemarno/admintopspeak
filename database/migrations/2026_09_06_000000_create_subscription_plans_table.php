<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master paket langganan dinamis (Time-based / Quota-based).
 * Harga asli & diskon, fitur (JSON), status active/archived, badge promo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->comment('Nama paket, contoh: Premium Bulanan');
            $table->text('description')->nullable()->comment('Deskripsi paket');
            $table->json('features')->nullable()->comment('Daftar fitur (list string)');
            $table->decimal('price_original', 12, 2)->default(0)->comment('Harga asli');
            $table->decimal('price_discount', 12, 2)->nullable()->comment('Harga diskon (null = tidak diskon)');
            $table->string('type', 10)->default('TIME')->comment('TIME | QUOTA');
            $table->unsignedInteger('duration_value')->nullable()->comment('Nilai durasi utk TIME');
            $table->string('duration_unit', 10)->nullable()->comment('DAY | MONTH | YEAR utk TIME');
            $table->unsignedInteger('quota_sessions')->nullable()->comment('Jumlah sesi utk QUOTA');
            $table->string('status', 10)->default('ACTIVE')->comment('ACTIVE | ARCHIVED');
            $table->string('badge_promo', 100)->nullable()->comment('Badge promo, contoh: Diskon 30%');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
