<div>
    @if (session()->has('status'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama, email, atau device UUID…"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="sm:col-span-3">
                <select wire:model.live="levelFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="">Semua Level CEFR</option>
                    @foreach ($this->levels as $level)
                        <option value="{{ $level->value }}">{{ $level->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3">
                <select wire:model.live="subscriptionFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="">Semua Status Langganan</option>
                    @foreach ($this->subscriptions as $sub)
                        <option value="{{ $sub->value }}">{{ $sub->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-1 text-right text-sm text-slate-500">
                {{ $users->total() }} user
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    @php
                        $sortableHeaders = [
                            'User' => 'name',
                            'Level' => 'current_cefr_level',
                            'Langganan' => 'subscription_status',
                            'Kuota Sesi' => 'remaining_trial_sessions',
                            'Riwayat' => 'level_histories_count',
                            'Log' => 'conversation_logs_count',
                        ];
                    @endphp
                    @foreach ($sortableHeaders as $label => $column)
                        <th class="px-4 py-3 {{ $column === 'remaining_trial_sessions' || $column === 'level_histories_count' || $column === 'conversation_logs_count' ? 'text-center' : '' }}">
                            <button wire:click="sortUsers('{{ $column }}')"
                                    @class(['group inline-flex items-center gap-1 uppercase tracking-wide', 'text-indigo-600' => $this->sortBy === $column])>
                                {{ $label }}
                                <span class="text-[10px] leading-none text-slate-400 group-hover:text-indigo-600">
                                    @if ($this->sortBy === $column)
                                        {{ $this->sortDir === 'asc' ? '▲' : '▼' }}
                                    @else
                                        ⇅
                                    @endif
                                </span>
                            </button>
                        </th>
                    @endforeach
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $user->name }}</div>
                            <div class="text-xs text-slate-500">{{ $user->email }}</div>
                            <div class="text-xs text-slate-400">{{ $user->phone_number ?? '—' }}</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($editingLevelId === $user->id)
                                <div class="flex items-center gap-1 justify-center">
                                    <select wire:model="levelField" class="rounded-lg border-slate-300 border px-2 py-1 text-sm">
                                        @foreach ($this->levels as $levelOption)
                                            <option value="{{ $levelOption->value }}">{{ $levelOption->value }}</option>
                                        @endforeach
                                    </select>
                                    <button wire:click="saveLevel" class="text-xs font-semibold text-indigo-600 hover:underline">Simpan</button>
                                    <button wire:click="cancelEditLevel" class="text-xs text-slate-400 hover:underline">✕</button>
                                </div>
                                @error('levelField') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                            @else
                                <button wire:click="startEditLevel({{ $user->id }})" class="group inline-flex items-center gap-1" title="Ubah level CEFR">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $user->current_cefr_level->value }}</span>
                                    <span class="text-slate-400 group-hover:text-indigo-600 text-xs">✎</span>
                                </button>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($editingSubId === $user->id)
                                <div class="flex flex-col gap-1">
                                    <select wire:model="subPlanId" class="rounded-lg border-slate-300 border px-2 py-1 text-sm">
                                        <option value="">— Pilih Langganan —</option>
                                        <option value="free">Free Tier</option>
                                        @foreach ($this->availablePlans as $plan)
                                            <option value="{{ $plan->id }}">{{ $plan->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" wire:model="subNote" placeholder="Catatan (opsional)"
                                           class="rounded-lg border-slate-300 border px-2 py-1 text-xs">
                                    <div class="flex items-center gap-1">
                                        <button wire:click="saveSub" class="text-xs font-semibold text-indigo-600 hover:underline">Simpan</button>
                                        <button wire:click="cancelEditSub" class="text-xs text-slate-400 hover:underline">✕</button>
                                    </div>
                                    @error('subPlanId') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                                </div>
                            @else
                                <div class="flex items-center gap-1">
                                    <button wire:click="startEditSub({{ $user->id }})" class="group inline-flex items-center gap-1" title="Ubah langganan (Free Tier / paket)">
                                        <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-slate-100 text-slate-600' => $user->subscription_status === \App\Enums\SubscriptionStatus::FREE || !$user->isPremiumActive(), 'bg-green-100 text-green-700' => $user->isPremiumActive()])>
                                            @if ($user->subscription_status === \App\Enums\SubscriptionStatus::FREE)
                                                Free Tier
                                            @elseif ($user->activeSubscription?->plan?->name)
                                                {{ $user->activeSubscription->plan->name }}
                                            @else
                                                Premium
                                            @endif
                                        </span>
                                        <span class="text-slate-400 group-hover:text-indigo-600 text-xs">✎</span>
                                    </button>
                                    @if ($user->activeSubscription && $user->activeSubscription->expires_at)
                                        <span class="text-xs text-slate-500 tabular-nums">s.d. {{ $user->activeSubscription->expires_at->format('d M Y') }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($editingId === $user->id)
                                <div class="flex items-center gap-1 justify-center">
                                    <input type="number" wire:model="remainingSessions" min="0" max="100"
                                           class="w-16 rounded-lg border-slate-300 border px-2 py-1 text-center text-sm">
                                    <button wire:click="saveQuota" class="text-xs font-semibold text-indigo-600 hover:underline">Simpan</button>
                                    <button wire:click="cancelEdit" class="text-xs text-slate-400 hover:underline">✕</button>
                                </div>
                                @error('remainingSessions') <div class="text-xs text-red-500">{{ $message }}</div> @enderror
                            @else
                                <div class="flex items-center justify-center gap-1">
                                    <button wire:click="openQuotaModal({{ $user->id }})" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800" title="Sesuaikan kuota (+/−/=)">±</button>
                                    <button wire:click="startEdit({{ $user->id }})" class="group inline-flex items-center gap-1" title="Ubah kuota">
                                        <span class="tabular-nums">{{ $user->remaining_trial_sessions }}</span>
                                        <span class="text-slate-400 group-hover:text-indigo-600 text-xs">✎</span>
                                    </button>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center tabular-nums">{{ $user->level_histories_count }}</td>
                        <td class="px-4 py-3 text-center tabular-nums">{{ $user->conversation_logs_count }}</td>
                        <td class="px-4 py-3 text-right space-x-2">
                            @if ($user->activeSubscription && $user->activeSubscription->expires_at)
                                <button wire:click="openDurationModal({{ $user->activeSubscription->id }})" class="text-xs font-semibold text-amber-600 hover:underline" title="Ubah sisa masa aktif">± Durasi</button>
                            @endif
                            <button wire:click="openLogModal({{ $user->id }})" class="text-xs font-semibold text-slate-500 hover:text-indigo-600">Log</button>
                            <a href="{{ route('admin.users.show', $user) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Detail →</a>
                            <button wire:click="openDeleteModal({{ $user->id }})" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-500">Tidak ada user ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    {{-- ===== MODAL SESUAIKAN KUOTA SESI ===== --}}
    @if ($showQuotaModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeQuotaModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Sesuaikan Sisa Sesi</h3>
                    <button wire:click="closeQuotaModal" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="p-5 space-y-4">
                    <div class="flex gap-3">
                        @foreach ([
                            'add' => 'Tambah (+)',
                            'subtract' => 'Kurangi (−)',
                            'set' => 'Set (=)',
                        ] as $op => $label)
                            <button wire:click="set('quotaOperation', '{{ $op }}')"
                                    wire:key="op-{{ $op }}"
                                    @class(['flex-1 px-3 py-2 rounded-lg text-sm font-semibold border', 'bg-indigo-600 text-white border-indigo-600' => $quotaOperation === $op, 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' => $quotaOperation !== $op])>
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai</label>
                        <input type="number" min="0" wire:model="quotaValue" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('quotaValue') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Alasan *</label>
                        <textarea wire:model="quotaReason" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. kompensasi, koreksi, hadiah"></textarea>
                        @error('quotaReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
                    <button wire:click="closeQuotaModal" class="px-4 py-2 rounded-lg text-sm text-slate-600 border border-slate-300 hover:bg-slate-50">Batal</button>
                    <button wire:click="applyQuota" class="px-4 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL LOG SISA SESI ===== --}}
    @if ($showLogModal && $logUserId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeLogModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Log Penyesuaian Sesi</h3>
                    <button wire:click="closeLogModal" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="p-5">
                    @php $logs = $this->quotaLogs($logUserId); @endphp
                    @forelse ($logs as $log)
                        <div class="flex items-center justify-between gap-3 py-2 border-b border-slate-100 last:border-0">
                            <div>
                                <div class="text-sm text-slate-700">
                                    <span class="font-semibold {{ $log->delta < 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                        {{ $log->delta < 0 ? '' : '+' }}{{ $log->delta }}
                                    </span>
                                    <span class="mx-1 text-slate-400">•</span>
                                    <span>{{ $log->reason ?? '—' }}</span>
                                </div>
                                <div class="text-xs text-slate-400">
                                    {{ $log->before_count }} → {{ $log->after_count }}
                                    <span class="mx-1">•</span>
                                    {{ $log->created_at->format('d M Y H:i') }}
                                    <span class="mx-1">•</span>
                                    {{ $log->admin?->name ?? 'System' }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-slate-500 py-6">Belum ada log penyesuaian untuk user ini.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL HAPUS USER ===== --}}
    @if ($showDeleteModal)
        @php $deleteTarget = $this->deletingUser; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeDeleteModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Hapus User</h3>
                    <button wire:click="closeDeleteModal" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="p-5 space-y-4">
                    <div class="text-sm text-slate-700">
                        Yakin ingin menghapus <strong class="text-slate-900">{{ $deleteTarget?->name }}</strong>
                        (<span class="text-slate-500">{{ $deleteTarget?->email }}</span>)?
                    </div>
                    <div class="text-xs text-slate-500 bg-red-50 border border-red-100 rounded-lg px-3 py-2">
                        Seluruh data user akan terhapus permanen (tidak bisa dikembalikan):
                        <ul class="mt-1 list-disc list-inside space-y-0.5">
                            <li>{{ $deleteTarget?->conversation_logs_count }} conversation logs + sesi latihan</li>
                            <li>{{ $deleteTarget?->level_histories_count }} riwayat level</li>
                            <li>{{ $deleteTarget?->subscriptions_count }} langganan</li>
                            <li>assessment, evaluasi kurikulum, kuota log, dsb.</li>
                        </ul>
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
                    <button wire:click="closeDeleteModal" class="px-4 py-2 rounded-lg text-sm text-slate-600 border border-slate-300 hover:bg-slate-50">Batal</button>
                    <button wire:click="deleteUser" class="px-4 py-2 rounded-lg text-sm font-semibold bg-red-600 text-white hover:bg-red-700">Hapus Permanen</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== MODAL UBAH SISA MASA AKTIF ===== --}}
    @if ($showDurationModal && $durationSubId)
        @php $durationSub = $this->durationSub; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeDurationModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200">
                    <h3 class="font-semibold text-slate-800">Ubah Sisa Masa Aktif</h3>
                    <button wire:click="closeDurationModal" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="p-5 space-y-4">
                    <div class="text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-2">
                        Langganan aktif: <span class="font-semibold text-slate-700">{{ $durationSub?->plan?->name ?? '—' }}</span>
                        · berakhir <span class="font-semibold text-slate-700">{{ $durationSub?->expires_at?->format('d M Y H:i') ?? '—' }}</span>
                    </div>
                    <div class="flex gap-3">
                        @foreach ([
                            'add' => 'Tambah (+)',
                            'subtract' => 'Kurangi (−)',
                        ] as $op => $label)
                            <button wire:click="set('durationOperation', '{{ $op }}')"
                                    wire:key="dop-{{ $op }}"
                                    @class(['flex-1 px-3 py-2 rounded-lg text-sm font-semibold border', 'bg-indigo-600 text-white border-indigo-600' => $durationOperation === $op, 'bg-white text-slate-600 border-slate-300 hover:bg-slate-50' => $durationOperation !== $op])>
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Nilai</label>
                            <input type="number" min="1" max="3650" wire:model="durationValue" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('durationValue') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">Satuan</label>
                            <select wire:model="durationUnit" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="DAY">Hari</option>
                                <option value="MONTH">Bulan</option>
                            </select>
                            @error('durationUnit') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Catatan (opsional)</label>
                        <input type="text" wire:model="durationNote" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. perpanjangan kompensasi">
                    </div>
                </div>
                <div class="flex justify-end gap-2 px-5 py-4 border-t border-slate-200">
                    <button wire:click="closeDurationModal" class="px-4 py-2 rounded-lg text-sm text-slate-600 border border-slate-300 hover:bg-slate-50">Batal</button>
                    <button wire:click="adjustDuration" class="px-4 py-2 rounded-lg text-sm font-semibold bg-indigo-600 text-white hover:bg-indigo-700">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>