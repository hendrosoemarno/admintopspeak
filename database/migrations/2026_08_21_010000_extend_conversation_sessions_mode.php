<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_sessions', function (Blueprint $table) {
            $table->enum('mode', ['ADAPTIVE', 'THEMATIC', 'IELTS_SPEAKING', 'TOEFL_IBT'])
                ->default('ADAPTIVE')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('conversation_sessions', function (Blueprint $table) {
            $table->enum('mode', ['ADAPTIVE', 'THEMATIC'])->default('ADAPTIVE')->change();
        });
    }
};
