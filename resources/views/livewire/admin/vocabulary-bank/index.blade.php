<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Cari Kata</label>
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari kata…"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Level CEFR</label>
                <select wire:model.live="levelFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="">Semua Level</option>
                    @foreach ($this->levels as $level)
                        <option value="{{ $level->value }}">{{ $level->value }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Jenis Kata</label>
                <select wire:model.live="posFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="">Semua Jenis</option>
                    @foreach ($partsOfSpeech as $pos)
                        <option value="{{ $pos }}">{{ $pos }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-600 mb-1">Topik</label>
                <select wire:model.live="topicFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="">Semua Topik</option>
                    @foreach ($topics as $topic)
                        <option value="{{ $topic }}">{{ $topic }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button wire:click="resetFilters" class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Reset Filter
                </button>
                <button wire:click="openCreate" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    + Tambah Kata
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">
                        <button wire:click="sortBy('word')" class="inline-flex items-center gap-1 hover:text-slate-800 font-semibold">
                            Kata <span>{{ $this->sortIcon('word') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3">
                        <button wire:click="sortBy('part_of_speech')" class="inline-flex items-center gap-1 hover:text-slate-800 font-semibold">
                            POS <span>{{ $this->sortIcon('part_of_speech') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3">
                        <button wire:click="sortBy('cefr_level')" class="inline-flex items-center gap-1 hover:text-slate-800 font-semibold">
                            Level <span>{{ $this->sortIcon('cefr_level') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3">
                        <button wire:click="sortBy('topic_category')" class="inline-flex items-center gap-1 hover:text-slate-800 font-semibold">
                            Topik <span>{{ $this->sortIcon('topic_category') }}</span>
                        </button>
                    </th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($vocabulary as $vocab)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $vocab->word }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $vocab->part_of_speech }}</span>
                        </td>
                        <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $vocab->cefr_level }}</span></td>
                        <td class="px-4 py-3 text-slate-600">{{ $vocab->topic_category }}</td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <button wire:click="openEdit({{ $vocab->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="delete({{ $vocab->id }})" wire:confirm="Hapus kata ini?" class="text-red-500 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-500">Tidak ada kata ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $vocabulary->links() }}
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">{{ $editingId ? 'Edit Kata' : 'Tambah Kata Baru' }}</h3>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kata</label>
                        <input type="text" wire:model="word" placeholder="experience" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        @error('word') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Part of Speech</label>
                            <select wire:model="part_of_speech" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @foreach ($partsOfSpeech as $pos)
                                    <option value="{{ $pos }}">{{ $pos }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Level CEFR</label>
                            <select wire:model="cefr_level" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @foreach ($this->levels as $level)
                                    <option value="{{ $level->value }}">{{ $level->value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Topik / Kategori</label>
                        <input type="text" wire:model="topic_category" placeholder="Job Interview" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        @error('topic_category') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeForm" class="px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
