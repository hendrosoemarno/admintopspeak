<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curriculum_evaluation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('session_id', 40)->index();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('user_transcript');
            $table->unsignedSmallInteger('score')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->boolean('key_point_detected')->nullable();
            $table->string('key_point_target', 100)->nullable();
            $table->text('grammar_feedback')->nullable();
            $table->text('vocabulary_feedback')->nullable();
            $table->text('suggested_answer')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_evaluation_logs');
    }
};