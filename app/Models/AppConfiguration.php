<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'latest_app_version', 'min_required_version',
    'is_force_update', 'play_store_url', 'update_message',
    'free_tier_initial_sessions',
])]
class AppConfiguration extends Model
{
    protected function casts(): array
    {
        return [
            'is_force_update' => 'boolean',
            'free_tier_initial_sessions' => 'integer',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => 1],
            [
                'latest_app_version' => '1.0.0',
                'min_required_version' => '1.0.0',
                'is_force_update' => true,
                'play_store_url' => 'https://play.google.com/store/apps/details?id=com.topspeak.app',
                'update_message' => 'Versi baru TopSpeak telah tersedia.',
                'free_tier_initial_sessions' => 1,
            ]
        );
    }

    public static function initialFreeSessions(): int
    {
        return max(0, (int) self::current()->free_tier_initial_sessions);
    }
}