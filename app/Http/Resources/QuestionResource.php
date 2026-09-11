<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $metadata = $this->metadata ?? [];

        return [
            'question_id' => $this->id,
            'question_text' => $this->question_text,
            'cefr_level' => $this->cefr_level->value,
            'test_type' => $this->test_type->value,
            'required_vocab_tags' => $this->required_vocab_tags ?? [],
            'audio_url' => $metadata['audio_url'] ?? null,
        ];
    }
}