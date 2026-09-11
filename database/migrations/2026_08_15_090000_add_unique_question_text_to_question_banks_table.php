<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // MySQL tidak mengizinkan UNIQUE index pada kolom TEXT tanpa panjang prefix.
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE question_banks ADD UNIQUE question_banks_question_text_unique (question_text(191))');

            return;
        }

        Schema::table('question_banks', function (Blueprint $table) {
            $table->unique('question_text', 'question_banks_question_text_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE question_banks DROP INDEX question_banks_question_text_unique');

            return;
        }

        Schema::table('question_banks', function (Blueprint $table) {
            $table->dropUnique('question_banks_question_text_unique');
        });
    }
};
