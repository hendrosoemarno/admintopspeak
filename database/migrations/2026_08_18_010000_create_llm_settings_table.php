<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('llm_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->string('provider', 30)->default('openai');
            $table->string('base_url', 255)->nullable();
            $table->string('api_key', 255)->nullable();
            $table->string('model', 100)->nullable();
            $table->integer('timeout')->default(15);
            $table->timestamps();
        });

        DB::table('llm_settings')->insert([
            'id' => 1,
            'is_enabled' => false,
            'provider' => 'openai',
            'base_url' => null,
            'api_key' => null,
            'model' => null,
            'timeout' => 15,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('llm_settings');
    }
};
