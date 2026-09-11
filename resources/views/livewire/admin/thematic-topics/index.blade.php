<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-8">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari nama topik atau persona…"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-4 text-right">
                <button wire:click="openCreate" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    + Tambah Topik
                </button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($topics as $topic)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col {{ $topic->is_active ? '' : 'opacity-50' }}">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $topic->selected_level }}</span>
                    <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-green-100 text-green-700' => $topic->is_active, 'bg-slate-100 text-slate-500' => !$topic->is_active])>
                        {{ $topic->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <h3 class="font-semibold text-slate-900">{{ $topic->topic_name }}</h3>
                <p class="mt-1 text-sm text-slate-600 flex-1"><strong class="text-slate-500">Persona:</strong> {{ $topic->roleplay_persona }}</p>
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach ($topic->context_vocab_tags ?? [] as $tag)
                        <span class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-600">{{ $tag }}</span>
                    @endforeach
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400">Dibuat: {{ $topic->creator?->name ?? 'Admin N/A' }}</span>
                    <div class="space-x-2">
                        <button wire:click="toggleActive({{ $topic->id }})" class="text-amber-600 hover:underline">{{ $topic->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</button>
                        <button wire:click="openEdit({{ $topic->id }})" class="text-indigo-600 hover:underline">Edit</button>
                        <button wire:click="delete({{ $topic->id }})" wire:confirm="Hapus topik ini?" class="text-red-500 hover:underline">Hapus</button>
                    </div>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                Tidak ada topik ditemukan.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $topics->links() }}
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">{{ $editingId ? 'Edit Topik' : 'Tambah Topik Baru' }}</h3>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nama Topik</label>
                            <input type="text" wire:model="topic_name" placeholder="Job Interview Simulation" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                            @error('topic_name') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Level</label>
                            <select wire:model="selected_level" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @foreach ($levels as $level)
                                    <option value="{{ $level }}">{{ $level }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Persona Roleplay</label>
                        <textarea wire:model="roleplay_persona" rows="2" placeholder="HR Manager at a Tech Startup" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm"></textarea>
                        @error('roleplay_persona') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Vocab Tags (koma) — dari <a href="{{ route('admin.vocabulary.index') }}" class="text-indigo-600 hover:underline">Vocabulary Bank</a>
                        </label>
                        <input type="text" wire:model="vocabTags" placeholder="skills, experience, strengths" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        <div class="mt-1.5 flex flex-wrap gap-1">
                            @foreach ($this->vocabWords as $word)
                                <a href="javascript:void(0)" wire:click="$set('vocabTags', '{{ $word }}')"
                                   class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600">
                                    {{ $word }}
                                </a>
                            @endforeach
                        </div>
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