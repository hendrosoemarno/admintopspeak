<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->integer('unit_number');
            $table->string('title', 100);
            $table->unsignedTinyInteger('part');
            $table->text('outcome')->nullable();
            $table->timestamps();

            $table->unique('unit_number');
            $table->index('part');
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->integer('lesson_number');
            $table->string('title', 150);
            $table->enum('difficulty', ['Easy', 'Medium', 'Difficult'])->default('Medium');
            $table->timestamps();

            $table->unique(['unit_id', 'lesson_number']);
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->text('question_text');
            $table->text('model_answer');
            $table->string('key_point', 100);
            $table->timestamps();

            $table->index('lesson_id');
        });

        Schema::create('user_lesson_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained('lessons')->onDelete('cascade');
            $table->enum('status', ['NOT_PASSED', 'PASSED'])->default('NOT_PASSED');
            $table->timestamps();

            $table->unique(['user_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_lesson_progress');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('units');
    }
};