<div>
    @if (session()->has('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- ===== TAB BAR ===== --}}
    <div class="mb-4 flex flex-wrap gap-2">
        <button wire:click="setTab('plans')"
                class="px-4 py-2 rounded-lg text-sm font-semibold {{ $activeTab === 'plans' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Paket &amp; Level
        </button>
        <button wire:click="setTab('subscriptions')"
                class="px-4 py-2 rounded-lg text-sm font-semibold {{ $activeTab === 'subscriptions' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}">
            Langganan User
        </button>
    </div>

    @if ($activeTab === 'plans')
        {{-- ===================== TAB: PAKET & LEVEL AKSES ===================== --}}

        {{-- Master Paket --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-800">Master Paket Langganan</h2>
                <button wire:click="openCreatePlan"
                        class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">+ Tambah Paket</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Nama Paket</th>
                            <th class="px-4 py-3">Durasi</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($plans as $plan)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-slate-800">{{ $plan->name }}</div>
                                    @if ($plan->badge_promo)
                                        <span class="text-xs text-pink-600 font-semibold">{{ $plan->badge_promo }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $plan->durationLabel() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    {{ $plan->displayPrice() }}
                                    @if ($plan->price_discount)
                                        <div class="text-xs text-slate-400 line-through">Rp {{ number_format((float) $plan->price_original, 0, ',', '.') }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $plan->status->value === 'ACTIVE' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                        {{ $plan->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button wire:click="openEditPlan({{ $plan->id }})" class="text-xs text-indigo-600 hover:underline mr-3">Edit</button>
                                    <button wire:click="deletePlan({{ $plan->id }})" wire:confirm="Hapus paket ini?" class="text-xs text-red-600 hover:underline">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-slate-500">Belum ada paket. Klik "+ Tambah Paket".</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Level CEFR (statis dari enum) --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-semibold text-slate-800">Level CEFR</h2>
                <span class="text-xs text-slate-400">Daftar level dari adaptive leveling</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">Kode</th>
                            <th class="px-4 py-3">Nama Level</th>
                            <th class="px-4 py-3">Urutan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($levels as $index => $level)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-indigo-600">{{ $level->value }}</td>
                                <td class="px-4 py-3">{{ $level->label() }}</td>
                                <td class="px-4 py-3 text-xs text-slate-400">{{ $index + 1 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ===== MODAL PLAN FORM ===== --}}
        @if ($showPlanForm)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closePlanForm">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <h3 class="font-semibold text-slate-800">{{ $editingPlanId ? 'Edit Paket' : 'Tambah Paket' }}</h3>
                        <button wire:click="closePlanForm" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                    </div>
                    <div class="p-5 grid grid-cols-1 sm:grid-cols-12 gap-4">
                        <div class="sm:col-span-8">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Paket *</label>
                            <input type="text" wire:model="plan_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('plan_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Tipe</label>
                            <div class="text-sm font-medium text-slate-700 py-2 px-3 rounded-lg bg-slate-100 border border-slate-200">Time (Durasi)</div>
                        </div>
                        <div class="sm:col-span-12">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi</label>
                            <textarea wire:model="plan_description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="sm:col-span-12">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Fitur (satu per baris / pisahkan koma)</label>
                            <textarea wire:model="plan_features" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Asli *</label>
                            <input type="number" min="0" wire:model="plan_price_original" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('plan_price_original') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Harga Diskon</label>
                            <input type="number" min="0" wire:model="plan_price_discount" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('plan_price_discount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Badge Promo</label>
                            <input type="text" wire:model="plan_badge_promo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>

                        <div class="sm:col-span-6">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Durasi *</label>
                            <input type="number" min="1" max="3650" wire:model="plan_duration_value" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. 1">
                            @error('plan_duration_value') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan Durasi *</label>
                            <select wire:model="plan_duration_unit" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="DAY">Hari</option>
                                <option value="MONTH">Bulan</option>
                                <option value="YEAR">Tahun</option>
                            </select>
                            @error('plan_duration_unit') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="sm:col-span-6">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                            <select wire:model="plan_status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="ACTIVE">Aktif</option>
                                <option value="ARCHIVED">Arsip</option>
                            </select>
                        </div>
                        <div class="sm:col-span-6">
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Urutan</label>
                            <input type="number" wire:model="plan_sort_order" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
                        <button wire:click="closePlanForm" class="px-4 py-2 rounded-lg text-sm text-slate-600 border border-slate-300 hover:bg-slate-50">Batal</button>
                        <button wire:click="savePlan" class="px-4 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
                    </div>
                </div>
            </div>
        @endif

        @else
        {{-- ===================== TAB: LANGANAN USER ===================== --}}

        {{-- Manajemen Langganan User --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
            <h2 class="font-semibold text-slate-800 mb-3">Manajemen Langganan User</h2>
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center mb-3">
                <div class="sm:col-span-5">
                    <input type="search" wire:model.live.debounce.300ms="userSearch" placeholder="Cari nama, email, atau telepon…"
                           class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                </div>
                <div class="sm:col-span-3">
                    <select wire:model.live="subStatusFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        <option value="">Semua Status</option>
                        <option value="active">Aktif</option>
                        <option value="pending">Pending Payment</option>
                        <option value="expired">Expired</option>
                        <option value="canceled">Canceled</option>
                        <option value="free">Free</option>
                    </select>
                </div>
                <div class="sm:col-span-3">
                    <select wire:model.live="subPlanFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        <option value="">Semua Paket</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-1 text-right">
                    <button wire:click="clearUserFilters" class="text-xs text-slate-500 hover:text-indigo-600">Bersihkan</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Paket</th>
                            <th class="px-4 py-3">Berakhir</th>
                            <th class="px-4 py-3 text-right">Sisa Sesi</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            @php $status = $this->userDisplayStatus($user); @endphp
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-indigo-600">{{ $user->name }}</div>
                                    <div class="text-xs text-slate-400">{{ $user->email }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $status[2] }}">{{ $status[1] }}</span>
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-600">
                                    {{ $user->activeSubscription?->plan?->name ?? $user->activeSubscription?->status?->label() ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">
                                    {{ $user->subscription_expires_at?->format('d M Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-right text-slate-600">{{ $user->remaining_trial_sessions }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <button wire:click="openAssign({{ $user->id }})" class="text-xs text-indigo-600 hover:underline mr-3">Assign</button>
                                    @if ($user->activeSubscription && $user->activeSubscription->expires_at)
                                        <button wire:click="openExpiry({{ $user->activeSubscription->id }})" class="text-xs text-amber-600 hover:underline mr-3">Ubah Expiry</button>
                                    @endif
                                    @if ($user->activeSubscription)
                                        <button wire:click="cancelSubscription({{ $user->activeSubscription->id }})" wire:confirm="Batalkan langganan aktif user ini?" class="text-xs text-red-600 hover:underline">Cancel</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">Tidak ada user.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $users->links() }}
            </div>
        </div>

        {{-- Rekaman Pembayaran --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h2 class="font-semibold text-slate-800 mb-3">Rekaman Pembayaran</h2>
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center mb-3">
                <div class="sm:col-span-6">
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari user, merchant order, atau ref…"
                           class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                </div>
                <div class="sm:col-span-4">
                    <select wire:model.live="paymentFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        <option value="">Semua Status Pembayaran</option>
                        @foreach (\App\Enums\PaymentStatus::cases() as $status)
                            <option value="{{ $status->value }}">{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2 text-right">
                    <button wire:click="clearFilters" class="text-xs text-slate-500 hover:text-indigo-600">Bersihkan filter</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                        <tr>
                            <th class="px-4 py-3">User</th>
                            <th class="px-4 py-3">Paket</th>
                            <th class="px-4 py-3">Sumber</th>
                            <th class="px-4 py-3">Merchant Order</th>
                            <th class="px-4 py-3 text-right">Harga</th>
                            <th class="px-4 py-3 text-center">Pembayaran</th>
                            <th class="px-4 py-3 text-center">Aktif</th>
                            <th class="px-4 py-3">Berakhir</th>
                            <th class="px-4 py-3 text-right">Waktu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($subscriptions as $subscription)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3">
                                    <div class="font-medium text-indigo-600">{{ $subscription->user?->name ?? '—' }}</div>
                                    <div class="text-xs text-slate-400">{{ $subscription->user?->email ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                        {{ $subscription->status->label() }}
                                    </span>
                                    @if ($subscription->plan)
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $subscription->plan->name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($subscription->source->isManual())
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-100 text-emerald-700">Manual</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $subscription->source->label() }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-600">{{ $subscription->merchant_order_id ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">Rp {{ number_format((float) $subscription->amount, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @php
                                        $badge = match ($subscription->payment_status) {
                                            \App\Enums\PaymentStatus::PAID => 'bg-green-100 text-green-700',
                                            \App\Enums\PaymentStatus::PENDING => 'bg-amber-100 text-amber-700',
                                            \App\Enums\PaymentStatus::EXPIRED => 'bg-slate-200 text-slate-600',
                                            \App\Enums\PaymentStatus::FAILED => 'bg-red-100 text-red-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ $subscription->payment_status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if ($subscription->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-500">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $subscription->expires_at?->format('d M Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right text-xs text-slate-500">{{ $subscription->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-10 text-center text-slate-500">Tidak ada data langganan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $subscriptions->links() }}
            </div>
        </div>

        {{-- ===== MODAL ASSIGN ===== --}}
        @if ($showAssignModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeAssign">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <h3 class="font-semibold text-slate-800">Assign Langganan</h3>
                        <button wire:click="closeAssign" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Paket *</label>
                            <select wire:model="assignPlanId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">— Pilih Paket —</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                @endforeach
                            </select>
                            @error('assignPlanId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Berakhir (untuk paket Time; kosongkan = pakai durasi paket)</label>
                            <input type="date" wire:model="assignExpiresAt" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan Admin</label>
                            <textarea wire:model="assignNote" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. kompensasi via customer care"></textarea>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
                        <button wire:click="closeAssign" class="px-4 py-2 rounded-lg text-sm text-slate-600 border border-slate-300 hover:bg-slate-50">Batal</button>
                        <button wire:click="assignPlan" class="px-4 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">Assign</button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ===== MODAL UBAH EXPIRY ===== --}}
        @if ($showExpiryModal)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeExpiry">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                        <h3 class="font-semibold text-slate-800">Ubah Tanggal Kadaluarsa</h3>
                        <button wire:click="closeExpiry" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                    </div>
                    <div class="p-5">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Berakhir pada *</label>
                        <input type="datetime-local" wire:model="expiryAt" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('expiryAt') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
                        <button wire:click="closeExpiry" class="px-4 py-2 rounded-lg text-sm text-slate-600 border border-slate-300 hover:bg-slate-50">Batal</button>
                        <button wire:click="updateExpiry" class="px-4 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
                    </div>
                </div>
            </div>
        @endif

    @endif
</div>
