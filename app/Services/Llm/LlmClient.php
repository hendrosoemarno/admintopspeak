<?php

namespace App\Services\Llm;

use App\Models\LlmSetting;
use Illuminate\Support\Facades\Http;

/**
 * Klien LLM ringan untuk endpoint chat-completions standar (OpenAI-compatible).
 * Mendukung OpenAI, DeepSeek, Gemini (openai endpoint), Groq, dst.
 *
 * Konfigurasi diambil dari tabel llm_settings (dikelola via App Configuration),
 * dengan fallback ke config/.env bila setting DB kosong.
 */
class LlmClient
{
    public function configured(): bool
    {
        return $this->apiKey() !== '';
    }

    public function enabled(): bool
    {
        return LlmSetting::current()->is_enabled && $this->configured();
    }

    public function apiKey(): string
    {
        return LlmSetting::current()->effectiveApiKey();
    }

    public function provider(): string
    {
        return LlmSetting::current()->effectiveProvider();
    }

    public function model(): string
    {
        return LlmSetting::current()->effectiveModel();
    }

    /**
     * Kirim prompt ke LLM, minta hasil JSON. Mengembalikan array decoded
     * atau null jika gagal / respons bukan JSON valid.
     */
    public function chatJson(string $system, string $user, float $temperature = 0.2): ?array
    {
        if (! $this->configured()) {
            return null;
        }

        $settings = LlmSetting::current();

        try {
            $response = Http::withToken($this->apiKey())
                ->acceptJson()
                ->timeout($settings->effectiveTimeout())
                ->post(rtrim($settings->effectiveBaseUrl(), '/').'/chat/completions', [
                    'model' => $this->model(),
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'temperature' => $temperature,
                    'response_format' => ['type' => 'json_object'],
                ]);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! is_string($content) || $content === '') {
            return null;
        }

        try {
            $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

            return is_array($decoded) ? $decoded : null;
        } catch (\JsonException) {
            return null;
        }
    }

    /**
     * Kirim beberapa prompt sekaligus. Di production memakai Http::pool
     * (paralel), saat Http::fake aktif (test) jatuh ke jalur sequential karena
     * fake tidak mendukung respons pool terindeks.
     *
     * Setiap item adalah array [system, user]; hasilnya array decoded per item
     * dengan urutan sama. Item yang gagal/timeout bernilai null.
     *
     * @param  array<int, array{0: string, 1: string}>  $calls
     * @return array<int, ?array>
     */
    public function chatJsonMany(array $calls): array
    {
        if (count($calls) < 2 || Http::isFake()) {
            return array_map(
                fn (array $call) => $this->chatJson($call[0], $call[1]),
                $calls,
            );
        }

        if (! $this->configured()) {
            return array_map(fn () => null, $calls);
        }

        $settings = LlmSetting::current();
        $baseUrl = rtrim($settings->effectiveBaseUrl(), '/').'/chat/completions';

        try {
            $responses = Http::pool(function ($pool) use ($calls, $baseUrl, $settings) {
                $requests = [];

                foreach ($calls as [$system, $user]) {
                    $requests[] = $pool->withToken($this->apiKey())
                        ->acceptJson()
                        ->timeout($settings->effectiveTimeout())
                        ->post($baseUrl, [
                            'model' => $this->model(),
                            'messages' => [
                                ['role' => 'system', 'content' => $system],
                                ['role' => 'user', 'content' => $user],
                            ],
                            'temperature' => 0.2,
                            'response_format' => ['type' => 'json_object'],
                        ]);
                }

                return $requests;
            });
        } catch (\Throwable $e) {
            report($e);

            return array_map(fn () => null, $calls);
        }

        $results = [];

        foreach ($calls as $i => $_) {
            $response = $responses[$i] ?? null;
            $content = $response instanceof \Illuminate\Http\Client\Response && $response->successful()
                ? data_get($response->json(), 'choices.0.message.content')
                : null;

            if (! is_string($content) || $content === '') {
                $results[] = null;
                continue;
            }

            try {
                $decoded = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
                $results[] = is_array($decoded) ? $decoded : null;
            } catch (\JsonException) {
                $results[] = null;
            }
        }

        return $results;
    }
}
