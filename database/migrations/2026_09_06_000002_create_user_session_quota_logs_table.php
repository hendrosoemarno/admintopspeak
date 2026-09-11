<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Riwayat penyesuaian sisa sesi user (audit log).
 * Mencatat user_id, admin_id, sebelum/sesudah, delta (+/-), dan alasan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_session_quota_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('before_count')->default(0)->comment('Sisa sesi sebelum');
            $table->integer('after_count')->default(0)->comment('Sisa sesi sesudah');
            $table->integer('delta')->default(0)->comment('Perubahan (+/-)');
            $table->string('reason', 255)->nullable()->comment('Alasan/catatan perubahan');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_session_quota_logs');
    }
};
