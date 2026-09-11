<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <h3 class="font-bold text-slate-900 mb-1">Konfigurasi Duitku</h3>
        <p class="text-sm text-slate-500 mb-5">
            Dipakai saat user membeli langganan melalui
            <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">POST /api/v1/subscriptions/purchase</code>.
            Disimpan di database — menimpa setting dari <code class="text-xs bg-slate-100 px-1 py-0.5 rounded">.env</code>.
        </p>

        <form wire:submit="save" class="space-y-5 max-w-2xl">
            <label class="flex items-center gap-3">
                <input type="checkbox" wire:model="is_enabled" class="rounded border-slate-300">
                <div>
                    <div class="text-sm font-medium text-slate-700">Aktifkan Pembayaran</div>
                    <div class="text-xs text-slate-500">Jika mati, endpoint purchase menolak dengan pesan &ldquo;Pembayaran sedang nonaktif&rdquo;.</div>
                </div>
            </label>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Environment</label>
                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="radio" value="1" wire:model="sandbox" class="accent-indigo-600">
                        Sandbox (sandbox.duitku.com)
                    </label>
                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="radio" value="0" wire:model="sandbox" class="accent-indigo-600">
                        Production (payment.duitku.com)
                    </label>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Merchant Code</label>
                <input type="text" wire:model="merchant_code" placeholder="DUITKUXXXXXX"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm font-mono">
                <p class="mt-1 text-xs text-slate-400">Kosongkan untuk memakai merchant code dari .env (
                    {{ \App\Models\PaymentGatewaySetting::current()->effectiveMerchantCode() ?: 'belum ada' }} ).</p>
                @error('merchant_code') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">API Key</label>
                <input type="password" wire:model="api_key" placeholder="api-key"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm font-mono">
                <p class="mt-1 text-xs text-slate-400">
                    Kosongkan untuk memakai API key dari .env ({{ $this->apiKeyConfigured ? 'key tersedia' : 'belum ada' }}).
                    API key tersimpan: {{ $this->api_key !== '' ? 'ya (tersimpan di database)' : ($this->apiKeyConfigured ? 'tidak (pakai .env)' : 'tidak') }}.
                </p>
                @error('api_key') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Notify URL (webhook callback)</label>
                <input type="url" wire:model="notify_url"
                       placeholder="https://api.topspeak.app/api/v1/subscriptions/duitku-callback"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm font-mono">
                <p class="mt-1 text-xs text-slate-400">Harus bisa diakses publik oleh server Duitku. Kosongkan untuk memakai nilai default.</p>
                @error('notify_url') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Return URL (redirect setelah bayar)</label>
                <input type="url" wire:model="return_url"
                       placeholder="https://api.topspeak.app/subscription/result"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm font-mono">
                @error('return_url') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
            </div>

            <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-sm">
                <div class="font-semibold text-slate-700 mb-1">Status</div>
                <div class="text-slate-600">
                    Environment: <span class="font-mono">{{ $this->environment }}</span>
                    &middot;
                    Pembayaran: <span class="font-semibold {{ $this->is_enabled ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $this->is_enabled ? 'Aktif' : 'Nonaktif' }}</span>
                </div>
            </div>

            <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                Simpan Konfigurasi
            </button>
        </form>
    </div>
</div>