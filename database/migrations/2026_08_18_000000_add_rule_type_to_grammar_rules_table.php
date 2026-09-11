<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grammar_rules', function (Blueprint $table) {
            $table->string('rule_type', 20)->default('error')
                ->comment('error = pola kesalahan (match -> violation), positive = pola benar (match -> skor benar)')
                ->after('category');
            $table->string('source', 20)->default('manual')
                ->comment('manual, user (auto-capture), llm (dihasilkan AI)')
                ->after('rule_type');
            $table->text('llm_meta')->nullable()
                ->comment('JSON hasil respons LLM saat generate rule')
                ->after('source');
        });
    }

    public function down(): void
    {
        Schema::table('grammar_rules', function (Blueprint $table) {
            $table->dropColumn(['rule_type', 'source', 'llm_meta']);
        });
    }
};
