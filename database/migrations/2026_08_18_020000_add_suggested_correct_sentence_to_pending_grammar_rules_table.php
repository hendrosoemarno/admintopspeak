<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_grammar_rules', function (Blueprint $table) {
            $table->text('suggested_correct_sentence')->nullable()->after('suggested_regex');
        });
    }

    public function down(): void
    {
        Schema::table('pending_grammar_rules', function (Blueprint $table) {
            $table->dropColumn('suggested_correct_sentence');
        });
    }
};
