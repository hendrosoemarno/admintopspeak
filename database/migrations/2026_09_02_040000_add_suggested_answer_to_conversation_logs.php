<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Saran jawaban benar untuk turn IELTS yang belum benar, dibangun dari
        // konten jawaban user + key point target (dihasilkan LLM).
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->text('suggested_answer')->nullable()->after('correct_way_text');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->dropColumn('suggested_answer');
        });
    }
};