<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('test_type', ['IELTS', 'TOEFL']);
            $table->string('task_type');
            $table->text('prompt_question');
            $table->text('user_transcript');
            $table->integer('duration_seconds')->nullable();

            $table->decimal('overall_score', 4, 1);
            $table->decimal('s_total', 3, 2);
            $table->tinyInteger('final_fluency');
            $table->boolean('is_on_topic')->default(true);

            $table->json('raw_response_json');
            $table->timestamps();

            $table->index(['user_id', 'test_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_logs');
    }
};
