<?php

namespace Tests\Feature\Api;

use App\Enums\SessionStepState;
use App\Models\ConversationLog;
use App\Models\ConversationSession;
use App\Models\User;
use App\Models\VocabularyBank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VocabularyApiTest extends TestCase
{
    use RefreshDatabase;

    private function seedBank(): void
    {
        foreach ([
            ['word' => 'neighbour', 'part_of_speech' => 'noun', 'cefr_level' => 'A2', 'topic_category' => 'Daily Routine'],
            ['word' => 'accomplish', 'part_of_speech' => 'verb', 'cefr_level' => 'B1', 'topic_category' => 'Career'],
            ['word' => 'cook', 'part_of_speech' => 'verb', 'cefr_level' => 'A1', 'topic_category' => 'Daily Routine'],
        ] as $row) {
            VocabularyBank::create($row);
        }
    }

    public function test_bank_returns_paginated_words_with_filters(): void
    {
        $this->seedBank();
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('api/v1/vocabulary')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 3)
            ->assertJsonCount(3, 'data.items')
            ->assertJsonStructure([
                'data' => ['items' => [['id', 'word', 'part_of_speech', 'cefr_level', 'topic_category']]],
            ]);

        $this->getJson('api/v1/vocabulary?cefr_level=A2')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 1)
            ->assertJsonPath('data.items.0.word', 'neighbour');

        $this->getJson('api/v1/vocabulary?q=cook')
            ->assertOk()
            ->assertJsonPath('data.items.0.word', 'cook');
    }

    public function test_learned_returns_corrected_phrases_with_mastery(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $session = ConversationSession::create([
            'user_id' => $user->id,
            'mode' => 'ADAPTIVE',
            'start_level' => 'A1',
            'current_level' => 'A1',
            'total_turns_planned' => 5,
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);

        // Pernah salah lalu diulang benar.
        ConversationLog::create([
            'user_id' => $user->id,
            'session_id' => $session->id,
            'turn_number' => 1,
            'user_response_text' => 'I go to market.',
            'user_said_text' => 'I go to market.',
            'correct_way_text' => 'I went to the market.',
            'has_error' => true,
            'repetition_success' => true,
            'step_state' => SessionStepState::NORMAL,
        ]);

        // Salah tapi belum diulang.
        ConversationLog::create([
            'user_id' => $user->id,
            'session_id' => $session->id,
            'turn_number' => 2,
            'user_response_text' => 'She have a cat.',
            'user_said_text' => 'She have a cat.',
            'correct_way_text' => 'She has a cat.',
            'has_error' => true,
            'repetition_success' => false,
            'step_state' => SessionStepState::NORMAL,
        ]);

        $this->getJson('api/v1/vocabulary/learned')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.phrase', 'I went to the market.')
            ->assertJsonPath('data.0.mastered', true)
            ->assertJsonPath('data.1.phrase', 'She has a cat.')
            ->assertJsonPath('data.1.mastered', false);
    }

    public function test_vocabulary_requires_authentication(): void
    {
        $this->getJson('api/v1/vocabulary')->assertUnauthorized();
        $this->getJson('api/v1/vocabulary/learned')->assertUnauthorized();
    }
}