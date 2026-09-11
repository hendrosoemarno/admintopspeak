<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->dropColumn('score_fluency');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_logs', function (Blueprint $table) {
            $table->integer('score_fluency')->default(0)->after('score_grammar');
        });
    }
};
