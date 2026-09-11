<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-6">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari kode, kategori, atau deskripsi…"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-3">
                <a href="{{ route('admin.pending-rules.index') }}"
                   class="block text-center px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">
                    Pending ({{ $this->pendingCount }})
                </a>
            </div>
            <div class="sm:col-span-3 text-right">
                <button wire:click="openCreate" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    + Tambah Aturan
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <button type="button" onclick="document.getElementById('placeholder-hint').classList.toggle('hidden')"
                class="text-xs font-semibold text-indigo-600 hover:underline">
            ℹ️ Lihat placeholder regex (dari Vocabulary Bank &amp; Data Transformation)
        </button>
        <div id="placeholder-hint" class="hidden mt-3">
            <p class="text-xs text-slate-500 mb-2">
                Pola regex dapat memakai placeholder semantik yang di-resolve otomatis menjadi daftar kata.
                Contoh: <code class="bg-slate-100 px-1 rounded">/\b{verb}\s+{adverb_of_frequency}\b/i</code>.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                @foreach ($this->placeholders as $ph => $desc)
                    <div class="text-xs rounded bg-slate-50 border border-slate-200 px-2 py-1.5">
                        <code class="font-semibold text-indigo-700">{{ $ph }}</code>
                        <span class="text-slate-500">— {{ $desc }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Kode</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Level</th>
                    <th class="px-4 py-3">Tipe</th>
                    <th class="px-4 py-3">Regex</th>
                    <th class="px-4 py-3 text-center">Masukan</th>
                    <th class="px-4 py-3 text-center">Aktif</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($rules as $rule)
                    <tr class="{{ $rule->is_active ? '' : 'opacity-50' }}">
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-indigo-700">{{ $rule->rule_code }}</td>
                        <td class="px-4 py-3">{{ $rule->category }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $rule->cefr_level->value }}</span></td>
                        <td class="px-4 py-3">
                            <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-red-100 text-red-700' => $rule->rule_type === 'error', 'bg-emerald-100 text-emerald-700' => $rule->rule_type === 'positive'])>
                                {{ $rule->rule_type === 'positive' ? 'Pola benar' : 'Pola error' }}
                            </span>
                            @if ($rule->source === 'llm') <span class="text-[11px] text-slate-400 ml-1">🤖</span> @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-600 max-w-[220px] truncate">{{ $rule->regex_pattern }}</td>
                        <td class="px-4 py-3 text-center tabular-nums">{{ $rule->pending_rules_count }}</td>
                        <td class="px-4 py-3 text-center">
                            <button wire:click="toggleActive({{ $rule->id }})"
                                    @class(['w-9 h-5 rounded-full relative transition', 'bg-indigo-600' => $rule->is_active, 'bg-slate-300' => !$rule->is_active])
                                    title="Toggle aktif">
                                <span class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white transition-transform {{ $rule->is_active ? 'translate-x-4' : '' }}"></span>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <button wire:click="openEdit({{ $rule->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="delete({{ $rule->id }})" wire:confirm="Hapus aturan ini?" class="text-red-500 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-slate-500">Tidak ada aturan grammar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $rules->links() }}
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">{{ $editingId ? 'Edit Aturan' : 'Tambah Aturan Grammar' }}</h3>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kode</label>
                            <input type="text" wire:model="rule_code" placeholder="SVA_01" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm uppercase">
                            @error('rule_code') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                            <input type="text" wire:model="category" placeholder="Subject-Verb Agreement" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Level</label>
                            <select wire:model="cefr_level" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @foreach ($this->levels as $level)
                                    <option value="{{ $level->value }}">{{ $level->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Rule</label>
                        <select wire:model="rule_type" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                            <option value="error">Pola error — match berarti kesalahan (skor 0)</option>
                            <option value="positive">Pola benar — match berarti benar (skor 1)</option>
                        </select>
                        @error('rule_type') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Pola Regex</label>
                        <input type="text" wire:model="regex_pattern" placeholder="/\\b(he|she|it)\\s+go\\b/i"
                               class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm font-mono">
                        @error('regex_pattern') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                        <textarea wire:model="description" rows="3" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm"></textarea>
                        @error('description') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" wire:model="is_active" class="rounded border-slate-300">
                        Aktif
                    </label>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeForm" class="px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>