<?php

namespace App\Livewire\Admin\AppConfig;

use App\Models\AppConfiguration;
use App\Models\LlmSetting;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Edit extends Component
{
    public string $latest_app_version = '';

    public string $min_required_version = '';

    public bool $is_force_update = false;

    public string $play_store_url = '';

    public string $update_message = '';

    public int $free_tier_initial_sessions = 1;

    public bool $llm_enabled = false;

    public string $llm_provider = 'openai';

    public string $llm_base_url = '';

    public string $llm_api_key = '';

    public string $llm_model = '';

    public string $llm_model_custom = '';

    public int $llm_timeout = 30;

    public array $providers = [
        'openai' => 'OpenAI',
        'deepseek' => 'DeepSeek',
        'gemini' => 'Google Gemini',
        'groq' => 'Groq',
        'custom' => 'Custom (OpenAI-compatible)',
    ];

    /** Base URL default per provider. */
    public array $providerBaseUrls = [
        'openai' => 'https://api.openai.com/v1',
        'deepseek' => 'https://api.deepseek.com/v1',
        'gemini' => 'https://generativelanguage.googleapis.com/v1beta/openai',
        'groq' => 'https://api.groq.com/openai/v1',
        'custom' => '',
    ];

    /** Daftar model umum per provider untuk dropdown saran. */
    public array $modelSuggestions = [
        'openai' => [
            'gpt-4o', 'gpt-4o-mini', 'gpt-4.1', 'gpt-4.1-mini', 'gpt-4.1-nano',
            'gpt-4-turbo', 'gpt-4', 'gpt-3.5-turbo',
        ],
        'deepseek' => [
            'deepseek-chat', 'deepseek-reasoner',
        ],
        'gemini' => [
            'gemini-2.5-pro', 'gemini-2.5-flash', 'gemini-2.0-flash', 'gemini-1.5-pro', 'gemini-1.5-flash',
        ],
        'groq' => [
            'llama-3.3-70b-versatile', 'llama-3.1-8b-instant', 'gemma2-9b-it',
            'mixtral-8x7b-32768', 'whisper-large-v3',
        ],
        'custom' => [],
    ];

    /** Opsi dropdown: daftar model provider + nilai terpilih (bila custom) + sentinel "custom". */
    #[Computed]
    public function modelOptions(): array
    {
        $options = $this->modelSuggestions[$this->llm_provider] ?? [];

        // Tambahkan nilai model yang sedang disimpan bila tidak ada di daftar.
        if ($this->llm_model !== '' && $this->llm_model !== '__custom__' && ! in_array($this->llm_model, $options, true)) {
            $options[] = $this->llm_model;
        }

        return array_values(array_unique($options));
    }

    /** True bila nilai model adalah sentinel custom (input teks ditampilkan). */
    public function isCustomModel(): bool
    {
        return $this->llm_model === '__custom__';
    }

    public function mount(): void
    {
        $this->load(AppConfiguration::current());
        $this->loadLlm(LlmSetting::current());
        $this->syncProviderDefaults($this->llm_provider);
    }

    protected function rules(): array
    {
        return [
            'latest_app_version' => 'required|string|max:20',
            'min_required_version' => 'required|string|max:20',
            'is_force_update' => 'boolean',
            'play_store_url' => 'required|url',
            'update_message' => 'required|string',
            'free_tier_initial_sessions' => 'required|integer|min:0|max:10000',
            'llm_enabled' => 'boolean',
            'llm_provider' => 'required|string|max:30',
            'llm_base_url' => 'nullable|url|max:255',
            'llm_api_key' => 'nullable|string|max:255',
            'llm_model' => 'nullable|string|max:100',
            'llm_timeout' => 'required|integer|min:5|max:300',
        ];
    }

    public function load(AppConfiguration $config): void
    {
        $this->latest_app_version = $config->latest_app_version;
        $this->min_required_version = $config->min_required_version;
        $this->is_force_update = $config->is_force_update;
        $this->play_store_url = $config->play_store_url;
        $this->update_message = $config->update_message;
        $this->free_tier_initial_sessions = (int) $config->free_tier_initial_sessions;
    }

    /** Dipanggil otomatis saat provider berubah di UI (wire:model.live). */
    public function updatedLlmProvider(string $value): void
    {
        $this->syncProviderDefaults($value);
    }

    /**
     * Isi Base URL (dan model default bila belum diisi) sesuai provider.
     * Base URL diisi ulang bila user belum pernah menimpa manual (berisi default lama).
     */
    public function syncProviderDefaults(string $provider): void
    {
        $defaultUrl = $this->providerBaseUrls[$provider] ?? '';

        // Jangan timpa bila user sudah mengetik URL khusus (bukan default dari daftar).
        $isStillDefault = $this->llm_base_url === '' || in_array($this->llm_base_url, array_values($this->providerBaseUrls), true);

        if ($defaultUrl !== '' && $isStillDefault) {
            $this->llm_base_url = $defaultUrl;
        }

        // Model: bila belum diisi atau masih model default, isi model pertama provider.
        $suggestions = $this->modelSuggestions[$provider] ?? [];
        if ($suggestions !== [] && ($this->llm_model === '' || $this->llm_model === '__custom__')) {
            $this->setModel($suggestions[0]);
        }
    }

    public function loadLlm(LlmSetting $setting): void
    {
        $this->llm_enabled = $setting->is_enabled;
        $this->llm_provider = $setting->provider ?: config('llm.provider', 'openai');
        $this->llm_base_url = $setting->base_url ?: (string) config('llm.base_url', '');
        $this->llm_api_key = $setting->api_key ?: (string) config('llm.api_key', '');
        $this->llm_timeout = $setting->timeout ?: (int) config('llm.timeout', 30);

        $model = $setting->model ?: (string) config('llm.model', 'gpt-4o-mini');
        $this->setModel($model);
    }

    public function setModel(string $model): void
    {
        $this->llm_model_custom = $model;

        if ($model === '' || in_array($model, $this->modelOptions(), true)) {
            $this->llm_model = $model;

            return;
        }

        $this->llm_model = '__custom__';
    }

    public function save(): void
    {
        $this->validate();

        AppConfiguration::current()->update([
            'latest_app_version' => trim($this->latest_app_version),
            'min_required_version' => trim($this->min_required_version),
            'is_force_update' => $this->is_force_update,
            'play_store_url' => $this->play_store_url,
            'update_message' => $this->update_message,
            'free_tier_initial_sessions' => $this->free_tier_initial_sessions,
        ]);

        $model = $this->llm_model === '__custom__'
            ? trim($this->llm_model_custom)
            : trim($this->llm_model);

        LlmSetting::current()->update([
            'is_enabled' => $this->llm_enabled,
            'provider' => $this->llm_provider,
            'base_url' => trim($this->llm_base_url) !== '' ? trim($this->llm_base_url) : null,
            'api_key' => trim($this->llm_api_key) !== '' ? trim($this->llm_api_key) : null,
            'model' => $model !== '' ? $model : null,
            'timeout' => $this->llm_timeout,
        ]);

        $this->dispatch('flash', message: 'Konfigurasi aplikasi berhasil disimpan.');
    }

    #[On('flash')]
    public function flash(string $message): void
    {
        session()->flash('status', $message);
    }

    public function render()
    {
        return view('livewire.admin.app-config.edit')
            ->layout('layouts.app', ['title' => 'App Configuration']);
    }
}
