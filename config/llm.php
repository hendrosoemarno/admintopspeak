<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi LLM untuk Grammar Rule Suggestion
    |--------------------------------------------------------------------------
    |
    | Provider mendukung format chat-completions standar (OpenAI-compatible).
    | Base URL disesuaikan dengan provider:
    |   - OpenAI    : https://api.openai.com/v1
    |   - DeepSeek  : https://api.deepseek.com/v1
    |   - Gemini    : https://generativelanguage.googleapis.com/v1beta/openai
    |   - Groq      : https://api.groq.com/openai/v1
    |
    */
    'provider' => env('LLM_PROVIDER', 'openai'),

    'base_url' => env('LLM_BASE_URL', 'https://api.openai.com/v1'),

    'api_key' => env('LLM_API_KEY', ''),

    'model' => env('LLM_MODEL', 'gpt-4o-mini'),

    'timeout' => (int) env('LLM_TIMEOUT', 15),

];
