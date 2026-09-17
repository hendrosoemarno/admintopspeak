<?php

namespace App\Models;

use App\Enums\SessionStepState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationLog extends Model
{
    protected $fillable = [
    'user_id', 'session_id', 'turn_number', 'question_id', 'curriculum_question_id',
    'thematic_question_id',
    'user_response_text', 'score_word_count', 'score_grammar',
    'total_turn_score', 'curriculum_score', 'key_point_detected', 'key_point_target',
    'grammar_feedback', 'vocabulary_feedback', 'suggested_answer', 'has_error',
    'user_said_text', 'correct_way_text', 'step_state',
    'expected_repetition_text', 'repetition_attempts', 'repetition_success',
];
    protected function casts(): array
    {
        return [
            'session_id' => 'string',
            'has_error' => 'boolean',
            'step_state' => SessionStepState::class,
            'repetition_success' => 'boolean',
            'curriculum_score' => 'integer',
            'key_point_detected' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ConversationSession::class, 'session_id', 'id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(QuestionBank::class, 'question_id');
    }

    public function curriculumQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'curriculum_question_id');
    }

    public function thematicQuestion(): BelongsTo
    {
        return $this->belongsTo(ThematicQuestion::class, 'thematic_question_id');
    }
}