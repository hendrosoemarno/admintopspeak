<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 px-4 py-3 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-800 text-sm">
        <strong>Legacy:</strong> daftar filler word untuk Fluency Score lama.
        Scoring fluency dihapus — penilaian percakapan kini hanya
        <strong>Word Count + Grammar (LLM)</strong>, tanpa komponen fluency.
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-5">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari filler word…"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-3">
                <select wire:model.live="categoryFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach ($this->categoryLabels as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-4 text-right">
                <button wire:click="openCreate" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    + Tambah Filler Word
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Phrase</th>
                    <th class="px-4 py-3">Kategori</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($fillerWords as $word)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $word->phrase }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $this->categoryLabels[$word->category] ?? $word->category }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleActive({{ $word->id }})"
                                    class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $word->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                {{ $word->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            <button wire:click="openEdit({{ $word->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="delete({{ $word->id }})" wire:confirm="Hapus filler word ini?" class="text-red-500 hover:underline">Hapus</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-10 text-center text-slate-500">Tidak ada filler word ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $fillerWords->links() }}
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">{{ $editingId ? 'Edit Filler Word' : 'Tambah Filler Word Baru' }}</h3>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Phrase</label>
                        <input type="text" wire:model="phrase" placeholder="you know" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        @error('phrase') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                        <select wire:model="category" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                            @foreach ($this->categoryLabels as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" wire:model="is_active" class="rounded border-slate-300">
                            Aktif
                        </label>
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
