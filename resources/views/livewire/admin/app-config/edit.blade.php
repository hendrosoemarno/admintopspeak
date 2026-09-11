<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-900 mb-1">Force Update & Version Control</h3>
        <p class="text-sm text-slate-500 mb-5">
            Dikelola oleh Splash Screen aplikasi Android melalui <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">GET /api/v1/app-version</code>.
        </p>

        <form wire:submit="save" class="space-y-5 max-w-2xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Latest App Version</label>
                    <input type="text" wire:model="latest_app_version" placeholder="1.3.0" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    @error('latest_app_version') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Min Required Version</label>
                    <input type="text" wire:model="min_required_version" placeholder="1.2.0" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    @error('min_required_version') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                </div>
            </div>

            <label class="flex items-center gap-3">
                <input type="checkbox" wire:model="is_force_update" class="rounded border-slate-300">
                <div>
                    <div class="text-sm font-medium text-slate-700">Aktifkan Force Update</div>
                    <div class="text-xs text-slate-500">UI aplikasi akan terkunci penuh jika versi < min required dan opsi ini aktif.</div>
                </div>
            </label>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Play Store URL</label>
                <input type="url" wire:model="play_store_url" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                @error('play_store_url') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Pesan Update</label>
                <textarea wire:model="update_message" rows="3" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm"></textarea>
                @error('update_message') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                Simpan Konfigurasi
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mt-6">
        <h3 class="font-bold text-slate-900 mb-1">Free Tier Settings</h3>
        <p class="text-sm text-slate-500 mb-5">
            Mengontrol kuota sesi trial gratis yang diberikan saat user baru mendaftar perangkat (via
            <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">POST /api/v1/auth/register-device</code>).
        </p>

        <form wire:submit="save" class="space-y-5 max-w-2xl">
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Sesi Awal Guest Baru</label>
                <input type="number" wire:model="free_tier_initial_sessions" min="0" max="10000"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-400">Jumlah sesi trial untuk perangkat yang baru mendaftar (default: 1).</p>
                @error('free_tier_initial_sessions') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
            </div>

            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                Simpan Konfigurasi
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mt-6">
        <h3 class="font-bold text-slate-900 mb-1">LLM Settings (Grammar Rule AI)</h3>
        <p class="text-sm text-slate-500 mb-5">
            Dipakai saat menyetujui <a href="{{ route('admin.pending-rules.index') }}" class="text-indigo-600 hover:underline">Pending Grammar Rules</a>
            untuk membuat regex otomatis. Disimpan di database — menimpa setting dari <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">.env</code>.
        </p>

        <form wire:submit="save" class="space-y-5 max-w-2xl">
            <label class="flex items-center gap-3">
                <input type="checkbox" wire:model="llm_enabled" class="rounded border-slate-300">
                <div>
                    <div class="text-sm font-medium text-slate-700">Aktifkan LLM</div>
                    <div class="text-xs text-slate-500">Perlu API key terisi. Jika mati, sistem memakai regex heuristic.</div>
                </div>
            </label>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Provider</label>
                    <select wire:model.live="llm_provider" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        @foreach ($providers as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Model</label>
                    @if ($this->isCustomModel())
                        <div class="flex gap-2">
                            <input type="text" wire:model="llm_model_custom" placeholder="nama-model-custom"
                                   class="flex-1 rounded-lg border-slate-300 border px-3 py-2 text-sm">
                            <button type="button" wire:click="setModel('{{ $this->modelOptions[0] ?? '' }}')"
                                    class="px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-600 hover:bg-slate-50 whitespace-nowrap">
                                ← Pilih lagi
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-slate-400">Sedang mode custom. Simpan untuk memakai model ini.</p>
                    @else
                        <select wire:model="llm_model"
                                class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                            @foreach ($this->modelOptions as $model)
                                <option value="{{ $model }}">{{ $model }}</option>
                            @endforeach
                            <option value="__custom__">✏️ Custom… (ketik sendiri)</option>
                        </select>
                        <p class="mt-1 text-xs text-slate-400">Pilih model, atau "Custom…" untuk mengetik model lain.</p>
                    @endif
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
                <input type="password" wire:model="llm_api_key" placeholder="sk-..." class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai API key dari .env ({{ $llm_api_key === '' ? 'belum ada' : 'key tersimpan' }}).</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Base URL</label>
                <input type="text" wire:model="llm_base_url" placeholder="https://api.openai.com/v1"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm font-mono">
                <p class="mt-1 text-xs text-slate-400">
                    Default: OpenAI <code class="bg-slate-100 px-1 rounded">https://api.openai.com/v1</code>,
                    DeepSeek <code class="bg-slate-100 px-1 rounded">https://api.deepseek.com/v1</code>,
                    Gemini <code class="bg-slate-100 px-1 rounded">https://generativelanguage.googleapis.com/v1beta/openai</code>,
                    Groq <code class="bg-slate-100 px-1 rounded">https://api.groq.com/openai/v1</code>.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Timeout (detik)</label>
                    <input type="number" wire:model="llm_timeout" min="5" max="300" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    @error('llm_timeout') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                </div>
                <div class="flex items-end">
                    <div class="text-sm text-slate-500">
                        Status:
                        @if ($llm_enabled && $llm_api_key !== '')
                            <span class="font-semibold text-emerald-600">Aktif &amp; siap dipakai</span>
                        @else
                            <span class="font-semibold text-amber-600">Belum aktif (butuh enable + API key)</span>
                        @endif
                    </div>
                </div>
            </div>

            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                Simpan Konfigurasi
            </button>
        </form>
    </div>
</div>