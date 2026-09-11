<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ThematicTopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'topic_name' => $this->topic_name,
            'selected_level' => $this->selected_level,
            'roleplay_persona' => $this->roleplay_persona,
            'context_vocab_tags' => $this->context_vocab_tags ?? [],
            'is_active' => $this->is_active,
        ];
    }
}