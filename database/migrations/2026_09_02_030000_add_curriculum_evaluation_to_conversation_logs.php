<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Penilaian IELTS berdasarkan kurikulum (Key Point 50%, Grammar 25%,
        // Lexical 25%) disimpan per turn di conversation_logs.
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->unsignedSmallInteger('curriculum_score')->nullable()->after('total_turn_score');
            $table->boolean('key_point_detected')->nullable()->after('curriculum_score');
            $table->string('key_point_target', 100)->nullable()->after('key_point_detected');
            $table->text('grammar_feedback')->nullable()->after('key_point_target');
            $table->text('vocabulary_feedback')->nullable()->after('grammar_feedback');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->dropColumn(['vocabulary_feedback', 'grammar_feedback', 'key_point_target', 'key_point_detected', 'curriculum_score']);
        });
    }
};