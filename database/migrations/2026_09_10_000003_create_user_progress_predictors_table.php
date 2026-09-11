<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_progress_predictors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('completion_score', 5, 2)->default(0);
            $table->decimal('mastery_score', 5, 2)->default(0);
            $table->decimal('accuracy_score', 5, 2)->default(0);
            $table->decimal('overall_index', 5, 2)->default(0);
            $table->string('predicted_band', 20)->default('NEED_MORE_DATA');
            $table->string('status_label', 50)->default('Need More Data');
            $table->string('color_code', 9)->default('#9CA3AF');
            $table->unsignedInteger('passed_lessons_count')->default(0);
            $table->boolean('is_ready')->default(false);
            $table->timestamp('last_calculated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_progress_predictors');
    }
};