<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    @if ($error !== '')
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">
            {{ $error }}
        </div>
    @endif

    @if ($success !== '')
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ $success }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-900 mb-1">1. Pilih User</h3>
                <p class="text-sm text-slate-500 mb-4">User ini yang akan dibuatkan invoice & menerima premium saat callback sukses.</p>

                <div class="flex flex-wrap gap-2 mb-3">
                    <input type="email" wire:model="searchEmail" wire:keydown.enter="findUser"
                           placeholder="email user (mis. test-payment@test.com)"
                           class="flex-1 min-w-[240px] rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <button type="button" wire:click="findUser"
                            class="px-4 py-2 rounded-lg bg-slate-700 text-white text-sm font-semibold hover:bg-slate-800">
                        Cari User
                    </button>
                    <button type="button" wire:click="ensureTestUser"
                            class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        + Buat User Uji
                    </button>
                </div>

                @if ($userInfo !== [])
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-semibold text-slate-900">{{ $userInfo['name'] }}</span>
                            <span class="text-slate-400">&lt;{{ $userInfo['email'] }}&gt;</span>
                            @if ($userInfo['is_guest'])
                                <span class="text-xs px-2 py-0.5 rounded-full bg-slate-200 text-slate-600">guest</span>
                            @endif
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2 text-slate-600">
                            <div>
                                <div class="text-xs text-slate-400">Status Langganan</div>
                                <span class="font-mono font-semibold {{ $userInfo['is_premium'] ? 'text-emerald-600' : 'text-slate-700' }}">
                                    {{ $userInfo['subscription_status'] }}
                                </span>
                            </div>
                            <div>
                                <div class="text-xs text-slate-400">Premium</div>
                                <span class="font-semibold {{ $userInfo['is_premium'] ? 'text-emerald-600' : 'text-slate-400' }}">
                                    {{ $userInfo['is_premium'] ? 'AKTIF' : 'Tidak' }}
                                </span>
                            </div>
                            <div>
                                <div class="text-xs text-slate-400">Berlaku s.d.</div>
                                <span class="font-mono">{{ $userInfo['subscription_expires_at'] ? \Illuminate\Support\Carbon::parse($userInfo['subscription_expires_at'])->format('d M Y H:i') : '-' }}</span>
                            </div>
                            <div>
                                <div class="text-xs text-slate-400">Sisa Sesi Trial</div>
                                <span class="font-mono">{{ $userInfo['remaining_trial_sessions'] }}</span>
                            </div>
                        </div>
                        <button type="button" wire:click="refreshUserStatus"
                                class="mt-3 px-3 py-1.5 rounded-lg border border-slate-300 text-xs font-semibold text-slate-600 hover:bg-white">
                            ↻ Refresh Status
                        </button>
                    </div>
                @else
                    <p class="text-sm text-slate-400">Belum ada user dipilih.</p>
                @endif
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-900 mb-1">2. Pilih Paket & Kanal Pembayaran</h3>
                <p class="text-sm text-slate-500 mb-4">Paket diambil dari <b>Master Paket Langganan</b> (ACTIVE), kanal live dari Duitku
                    (<code class="text-xs bg-slate-100 px-1 py-0.5 rounded">GET /api/v1/subscriptions/payment-methods</code>).</p>

                @if ($plans === [])
                    <p class="text-sm text-amber-600">Tidak ada paket ACTIVE. Buat paket dulu di Subscriptions → tab Plans.</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                        @foreach ($plans as $item)
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer {{ $plan_id === (int) $item['id'] ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:bg-slate-50' }}">
                                <input type="radio" value="{{ $item['id'] }}" wire:model.live="plan_id" class="accent-indigo-600">
                                <div>
                                    <div class="text-sm font-semibold text-slate-800">{{ $item['name'] }}</div>
                                    <div class="text-xs text-slate-500">
                                        Rp{{ number_format($item['price'], 0, ',', '.') }} · {{ $item['duration'] }}
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif

                @if ($methods === [])
                    <p class="text-sm text-amber-600">Kanal pembayaran belum termuat. Periksa konfigurasi gateway / koneksi ke Duitku.</p>
                @else
                    <div class="text-sm font-medium text-slate-700 mb-2">Kanal Pembayaran</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4">
                        @foreach ($methods as $method)
                            <label class="flex items-center gap-3 p-3 rounded-lg border cursor-pointer {{ $payment_method === $method['code'] ? 'border-indigo-400 bg-indigo-50' : 'border-slate-200 hover:bg-slate-50' }}">
                                <input type="radio" value="{{ $method['code'] }}" wire:model="payment_method" class="accent-indigo-600">
                                <img src="{{ $method['image'] }}" alt="{{ $method['name'] }}" class="w-8 h-8 rounded object-contain bg-white">
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-slate-800 truncate">{{ $method['name'] }}</div>
                                    <div class="text-xs text-slate-500 font-mono">{{ $method['code'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif

                <button type="button" wire:click="createCheckout" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50">
                    Buat Checkout Duitku
                </button>
            </div>

            @if ($checkout !== [])
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                    <h3 class="font-bold text-slate-900 mb-1">3. Checkout & Pembayaran</h3>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 mt-3 text-sm">
                        <div>
                            <div class="text-xs text-slate-400">Merchant Order</div>
                            <div class="font-mono text-xs break-all">{{ $checkout['merchant_order_id'] ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Amount</div>
                            <div class="font-semibold">Rp{{ number_format($checkout['amount'] ?? 0, 0, ',', '.') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Payment Status</div>
                            <span class="font-mono font-semibold {{ ($checkout['payment_status'] ?? '') === 'PAID' ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $checkout['payment_status'] ?? '-' }}
                            </span>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Paket</div>
                            <span class="font-semibold">{{ $checkout['plan_name'] ?? $checkout['plan_id'] ?? '-' }}</span>
                        </div>
                        <div>
                            <div class="text-xs text-slate-400">Kanal</div>
                            <span class="font-mono">{{ $checkout['payment_method'] ?? '-' }}</span>
                        </div>
                    </div>

                    @if (! empty($checkout['va_number']) || ! empty($checkout['qr_string']))
                        <div class="mt-3 rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-sm">
                            @if (! empty($checkout['va_number']))
                                <div>VA Number: <span class="font-mono font-semibold">{{ $checkout['va_number'] }}</span></div>
                            @endif
                            @if (! empty($checkout['qr_string']))
                                <div class="truncate">QRIS: <span class="font-mono">{{ $checkout['qr_string'] }}</span></div>
                            @endif
                        </div>
                    @endif

                    <div class="flex flex-wrap gap-2 mt-4">
                        <a href="{{ $checkout['checkout_url'] ?? '#' }}" target="_blank" rel="noopener"
                           class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                            Buka Halaman Pembayaran (tab baru)
                        </a>
                        <button type="button" wire:click="syncPaymentStatus"
                                class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            ↻ Cek Status Payment
                        </button>
                        <button type="button" wire:click="simulateSuccessCallback"
                                class="px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-semibold hover:bg-amber-600">
                            Simulasikan Callback Sukses (00)
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-slate-400">
                        Tips: bayar dulu di halaman Duitku (tab baru), lalu klik "Cek Status Payment". Atau langsung
                        "Simulasikan Callback Sukses" untuk menguji aktivasi premium tanpa menunggu webhook Duitku.
                    </p>
                </div>
            @endif
        </div>

        <div>
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h3 class="font-bold text-slate-900 mb-3">Status Gateway</h3>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Aktif</dt>
                        <dd class="font-semibold {{ $gateway['is_enabled'] ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $gateway['is_enabled'] ? 'Ya' : 'Tidak' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Environment</dt>
                        <dd class="font-mono {{ $gateway['sandbox'] ? 'text-amber-600' : 'text-slate-800' }}">
                            {{ $gateway['sandbox'] ? 'Sandbox' : 'Production' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Merchant Code</dt>
                        <dd class="font-mono">{{ $gateway['merchant_code'] ?: '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">API Key</dt>
                        <dd class="text-slate-600">{{ $gateway['api_key_configured'] ? 'terisi' : 'belum ada' }}</dd>
                    </div>
                </dl>
                <div class="mt-3 text-xs text-slate-400 break-all">Endpoint: {{ $gateway['base_url'] }}</div>

                <a href="{{ route('admin.payment-gateway.settings') }}"
                   class="mt-4 inline-block px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900">
                    ⚙️ Ubah Konfigurasi
                </a>
            </div>
        </div>
    </div>
</div>