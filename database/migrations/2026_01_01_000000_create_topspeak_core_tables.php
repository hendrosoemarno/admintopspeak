<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Catatan: Tabel users SUDAH dibuat oleh migration default Laravel
        // (0001_01_01_000000_create_users_table.php) beserta kolom-kolom khusus TopSpeak.

        // 1. Tabel Grammar Rules
        Schema::create('grammar_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_code', 50)->unique()->comment('Contoh: SVA_01, PREP_01');
            $table->string('category', 100)->comment('Subject-Verb Agreement, Preposition, Tenses, etc.');
            $table->string('cefr_level', 2)->default('A1');
            $table->text('regex_pattern')->comment('Pola RegEx untuk evaluasi teks');
            $table->text('description')->comment('Penjelasan kesalahan untuk rujukan UI/Tutor');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['rule_code', 'cefr_level']);
        });

        // 2. Tabel Question Banks
        Schema::create('question_banks', function (Blueprint $table) {
            $table->id();
            $table->enum('test_type', ['ADAPTIVE', 'IELTS_SPEAKING', 'TOEFL_IBT'])->default('ADAPTIVE');
            $table->integer('part_number')->default(1)->comment('Khusus IELTS Part 1-3 atau TOEFL Task 1-4');
            $table->string('cefr_level', 2)->default('A1');
            $table->text('question_text');
            $table->json('required_vocab_tags')->comment('Tag kosa kata wajib untuk penilaian Fluency/Relevansi');
            $table->boolean('is_starter')->default(false)->comment('True jika digunakan sebagai soal pembuka Sesi 1 / Free Tier');
            $table->string('topic_category', 100)->default('General Conversation');
            $table->json('metadata')->nullable()->comment('Untuk audio URL, reading passage, atau cue card prompt');
            $table->timestamps();

            $table->index(['cefr_level', 'is_starter', 'test_type']);
        });

        // 3. Tabel Vocabulary Bank
        Schema::create('vocabulary_bank', function (Blueprint $table) {
            $table->id();
            $table->string('word', 100)->unique();
            $table->string('part_of_speech', 30)->comment('noun, verb, adjective, adverb');
            $table->string('cefr_level', 2)->default('A1');
            $table->string('topic_category', 100);
            $table->timestamps();

            $table->index(['word', 'cefr_level', 'topic_category']);
        });

        // 4. Tabel Thematic Topics
        Schema::create('thematic_topics', function (Blueprint $table) {
            $table->id();
            $table->string('topic_name', 150);
            $table->text('roleplay_persona')->comment('Instruksi instruktur AI (misal: HR Manager, Waiter)');
            $table->string('selected_level', 50)->default('Beginner');
            $table->json('context_vocab_tags')->comment('Tag kata yang relevan dengan skenario');
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 5. Tabel Conversation Logs
        Schema::create('conversation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->uuid('session_id')->comment('UUID unik per 1 sesi latihan (5-8 turn)');
            $table->integer('turn_number')->comment('Urutan turn (1, 2, 3, dst.)');
            $table->foreignId('question_id')->nullable()->constrained('question_banks')->onDelete('set null');
            $table->text('user_response_text')->nullable()->comment('Hasil transkripsi Native STT');

            // Evaluasi Skor Turn (0-3 Poin)
            $table->integer('score_word_count')->default(0)->comment('1 jika words >= 20, else 0');
            $table->integer('score_grammar')->default(0)->comment('1 jika errors <= 1, else 0');
            $table->integer('score_fluency')->default(0)->comment('1 jika intent match & non-fallback, else 0');
            $table->integer('total_turn_score')->default(0)->comment('Akumulasi 0-3 poin');

            // Fitur Koreksi & Repetition Loop
            $table->boolean('has_error')->default(false);
            $table->text('user_said_text')->nullable()->comment('Bagian kesalahan user');
            $table->text('correct_way_text')->nullable()->comment('Kalimat plaintext perbaikan');
            $table->enum('step_state', ['NORMAL', 'WAITING_REPETITION'])->default('NORMAL');
            $table->text('expected_repetition_text')->nullable();
            $table->integer('repetition_attempts')->default(0);
            $table->boolean('repetition_success')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'session_id', 'turn_number']);
        });

        // 6. Tabel User Level Histories
        Schema::create('user_level_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->uuid('session_id');
            $table->string('previous_level', 2);
            $table->string('new_level', 2);
            $table->integer('trigger_score')->comment('Total akumulasi skor 4-turn (misal: >= 6)');
            $table->text('promotion_reason')->nullable()->comment('Alasan promosi level');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        // 7. Tabel Pending Grammar Rules
        Schema::create('pending_grammar_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grammar_rule_id')->nullable()->constrained('grammar_rules')->onDelete('set null');
            $table->text('raw_user_input');
            $table->text('detected_error');
            $table->text('suggested_regex')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->timestamps();
        });

        // 8. Tabel User Subscriptions
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['FREE', 'PREMIUM_MONTHLY', 'PREMIUM_YEARLY'])->default('FREE');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->string('payment_provider', 30)->nullable()->comment('Duitku');
            $table->string('payment_ref', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'status', 'is_active']);
        });

        // 9. Tabel App Configurations (Force Update)
        Schema::create('app_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('latest_app_version', 20)->default('1.0.0');
            $table->string('min_required_version', 20)->default('1.0.0');
            $table->boolean('is_force_update')->default(true);
            $table->text('play_store_url');
            $table->text('update_message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_configurations');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('pending_grammar_rules');
        Schema::dropIfExists('user_level_histories');
        Schema::dropIfExists('conversation_logs');
        Schema::dropIfExists('thematic_topics');
        Schema::dropIfExists('vocabulary_bank');
        Schema::dropIfExists('question_banks');
        Schema::dropIfExists('grammar_rules');
    }
};