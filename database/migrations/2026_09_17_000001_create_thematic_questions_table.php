<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('thematic_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('thematic_topics')->onDelete('cascade');
            $table->text('question_text');
            $table->text('standard_answer')->nullable();
            $table->string('cefr_level', 5);
            $table->string('key_point', 100)->nullable();
            $table->timestamps();

            $table->index('topic_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('thematic_questions');
    }
};