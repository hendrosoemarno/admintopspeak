<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TopSpeakInitialSeeder extends Seeder
{
    public function run(): void
    {
        // Data Configuration (Force Update)
        DB::table('app_configurations')->insert([
            'id' => 1,
            'latest_app_version' => '1.0.0',
            'min_required_version' => '1.0.0',
            'is_force_update' => true,
            'play_store_url' => '[https://play.google.com/store/apps/details?id=com.topspeak.app](https://play.google.com/store/apps/details?id=com.topspeak.app)',
            'update_message' => 'Versi baru TopSpeak telah tersedia. Silakan perbarui aplikasi Anda untuk melanjutkan.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Data Master Grammar Rules
        DB::table('grammar_rules')->insert([
            [
                'rule_code' => 'SVA_01',
                'category' => 'Subject-Verb Agreement',
                'cefr_level' => 'A1',
                'regex_pattern' => '\b(he|she|it)\s+(go|like|want|eat)\b',
                'description' => 'Subjek singular (he/she/it) harus diikuti Verb dengan akhiran -s/es pada Present Tense.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'rule_code' => 'PREP_01',
                'category' => 'Preposition Error',
                'cefr_level' => 'B1',
                'regex_pattern' => '\bdiscuss\s+about\b',
                'description' => 'Kata kerja "discuss" tidak membutuhkan preposisi "about" secara langsung.',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // Data Master Question Starter (Free Tier / Level A1)
        DB::table('question_banks')->insert([
            [
                'test_type' => 'ADAPTIVE',
                'part_number' => 1,
                'cefr_level' => 'A1',
                'question_text' => 'Could you tell me your full name and where you currently live?',
                'required_vocab_tags' => json_encode(['name', 'live', 'address', 'city', 'stay', 'from']),
                'is_starter' => true,
                'topic_category' => 'Personal Information',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'test_type' => 'ADAPTIVE',
                'part_number' => 1,
                'cefr_level' => 'A1',
                'question_text' => 'What is your favorite daily activity and why do you like it?',
                'required_vocab_tags' => json_encode(['activity', 'like', 'favorite', 'day', 'enjoy']),
                'is_starter' => true,
                'topic_category' => 'Daily Routine',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}