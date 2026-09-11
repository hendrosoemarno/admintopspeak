<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppConfigurationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'latest_version' => $this->latest_app_version,
            'min_required_version' => $this->min_required_version,
            'is_force_update' => $this->is_force_update,
            'play_store_url' => $this->play_store_url,
            'update_message' => $this->update_message,
        ];
    }
}