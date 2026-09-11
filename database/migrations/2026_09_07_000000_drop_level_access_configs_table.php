<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus tabel level_access_configs.
 * Konsep "Level Akses" diganti menjadi daftar Level CEFR statis (A1-C2)
 * yang berasal dari enum App\Enums\CefrLevel, bukan lagi konfigurasi max-level.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('level_access_configs');
    }

    public function down(): void
    {
        Schema::create('level_access_configs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->string('max_cefr_level', 10)->default('ALL');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
};
