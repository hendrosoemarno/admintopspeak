<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(true);
            $table->string('merchant_code', 40)->nullable();
            $table->string('api_key', 255)->nullable();
            $table->boolean('sandbox')->nullable();
            $table->string('notify_url', 500)->nullable();
            $table->string('return_url', 500)->nullable();
            $table->timestamps();
        });

        // Nilai kosong (null) berarti memakai fallback dari config() / .env,
        // konsisten dengan pola LlmSetting.
        DB::table('payment_gateway_settings')->insert([
            'id' => 1,
            'is_enabled' => true,
            'merchant_code' => null,
            'api_key' => null,
            'sandbox' => null,
            'notify_url' => null,
            'return_url' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_settings');
    }
};