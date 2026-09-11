<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Konfigurasi Level Akses (terpisah dari paket). Mendefinisikan tier akses
 * yang tersedia beserta level CEFR maksimal yang dibuka (misal: ALL, A1-B1, dst).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('level_access_configs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique()->comment('Kode tier, contoh: FREE, PREMIUM');
            $table->string('label', 100)->comment('Nama tampilan');
            $table->text('description')->nullable();
            $table->string('max_cefr_level', 10)->default('ALL')->comment('Level CEFR maksimal atau ALL');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_access_configs');
    }
};
