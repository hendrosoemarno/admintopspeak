<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('session_id');
            $table->unsignedTinyInteger('total_questions')->default(0);
            $table->unsignedTinyInteger('correct_count')->default(0);
            $table->decimal('score', 6, 2)->default(0)->comment('Rata-rata skor 5 soal (0-100)');
            $table->boolean('is_passed')->default(false);
            $table->unsignedInteger('total_keypoints')->default(0);
            $table->unsignedInteger('correct_keypoints')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'session_id']);
            $table->index(['user_id', 'is_passed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_sessions');
    }
};