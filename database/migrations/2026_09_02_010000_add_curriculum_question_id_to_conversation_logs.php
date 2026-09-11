<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->foreignId('curriculum_question_id')
                ->nullable()
                ->after('question_id')
                ->constrained('questions')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('curriculum_question_id');
        });
    }
};