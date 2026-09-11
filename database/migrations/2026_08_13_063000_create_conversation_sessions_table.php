<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('mode', ['ADAPTIVE', 'THEMATIC', 'IELTS_SPEAKING', 'TOEFL_IBT'])->default('ADAPTIVE');
            $table->foreignId('topic_id')->nullable()->constrained('thematic_topics')->onDelete('set null');
            $table->string('start_level', 2)->default('A1');
            $table->string('current_level', 2)->default('A1');
            $table->integer('total_turns_planned')->default(5);
            $table->enum('status', ['ACTIVE', 'COMPLETED'])->default('ACTIVE');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_sessions');
    }
};