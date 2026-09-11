<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('filler_words', function (Blueprint $table) {
            $table->id();
            $table->string('phrase', 100)->unique()->comment('Kata/ekspresi filler, mis. "um", "you know"');
            $table->string('category', 50)->default('hesitation')->comment('hesitation, discourse, phrase');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('filler_words');
    }
};
