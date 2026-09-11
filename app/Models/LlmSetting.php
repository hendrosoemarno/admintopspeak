<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmSetting extends Model
{
    protected $fillable = ['is_enabled', 'provider', 'base_url', 'api_key', 'model', 'timeout'];
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'timeout' => 'integer',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate(
            ['id' => 1],
            [
                'is_enabled' => false,
                'provider' => 'openai',
                'base_url' => null,
                'api_key' => null,
                'model' => null,
                'timeout' => 15,
            ]
        );
    }

    /** Nilai efektif: setting DB lebih diutamakan, fallback ke .env/config. */
    public function effectiveProvider(): string
    {
        return $this->provider ?: config('llm.provider', 'openai');
    }

    public function effectiveBaseUrl(): string
    {
        return $this->base_url ?: config('llm.base_url', 'https://api.openai.com/v1');
    }

    public function effectiveApiKey(): string
    {
        return $this->api_key ?: (string) config('llm.api_key', '');
    }

    public function effectiveModel(): string
    {
        return $this->model ?: (string) config('llm.model', 'gpt-4o-mini');
    }

    public function effectiveTimeout(): int
    {
        return $this->timeout ?: (int) config('llm.timeout', 15);
    }
}
