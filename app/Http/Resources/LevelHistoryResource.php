<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LevelHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'previous_level' => $this->previous_level,
            'new_level' => $this->new_level,
            'trigger_score' => $this->trigger_score,
            'promoted_at' => $this->created_at?->toIso8601String(),
        ];
    }
}