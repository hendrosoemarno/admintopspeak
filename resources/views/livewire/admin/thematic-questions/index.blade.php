<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-8">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari teks soal…"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="sm:col-span-4 text-right">
                <button wire:click="openCreate" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    + Tambah Soal
                </button>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-1.5 border-b border-slate-200 pb-2">
            <button wire:click="setTopic(null)"
                    @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $activeTopicId === null, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activeTopicId !== null])>
                Semua Topik
            </button>
            @foreach ($this->topics as $topic)
                <button wire:click="setTopic({{ $topic->id }})"
                        @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $activeTopicId === $topic->id, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activeTopicId !== $topic->id])>
                    {{ $topic->topic_name }} ({{ $topic->questions_count }})
                </button>
            @endforeach
        </div>

        <div class="mt-4 flex flex-wrap gap-1.5">
            @foreach ($this->levels as $level)
                <button wire:click="setLevel('{{ $level->value }}')"
                        @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-slate-800 text-white' => $activeLevel === $level->value, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activeLevel !== $level->value])>
                    {{ $level->value }} ({{ $levelCounts[$level->value] ?? 0 }})
                </button>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-wrap items-center gap-3">
        @if ($activeTopic)
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $activeTopic->topic_name }}</span>
        @else
            <span class="text-sm text-slate-500">Semua topik</span>
        @endif
        @if ($activeLevel !== '')
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $activeLevel }}</span>
        @endif
        <span class="text-xs text-slate-500">{{ $topicTotal }} soal</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse ($questions as $q)
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $q->cefr_level->value }}</span>
                    @if ($q->topic)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $q->topic->topic_name }}</span>
                    @endif
                    @if ($q->key_point)
                        <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700">KP: {{ $q->key_point }}</span>
                    @endif
                </div>
                <p class="text-sm text-slate-800 flex-1">{{ $q->question_text }}</p>
                @if ($q->standard_answer)
                    <div class="mt-3 rounded-lg bg-emerald-50 border border-emerald-100 p-3">
                        <div class="text-xs font-semibold text-emerald-700 mb-1">Jawaban Standar</div>
                        <p class="text-xs text-emerald-800 leading-relaxed">{{ $q->standard_answer }}</p>
                    </div>
                @endif
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-end text-xs text-slate-500 gap-3">
                    <button wire:click="openEdit({{ $q->id }})" class="text-indigo-600 hover:underline">Edit</button>
                    <button wire:click="delete({{ $q->id }})" wire:confirm="Hapus soal tematik ini?"
                            class="text-red-500 hover:underline">Hapus</button>
                </div>
            </div>
        @empty
            <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                Tidak ada soal tematik ditemukan.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $questions->links() }}
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">{{ $editingId ? 'Edit Soal Tematik' : 'Tambah Soal Tematik Baru' }}</h3>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Topik Tematik</label>
                            <select wire:model="topic_id" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @foreach ($this->topics as $topic)
                                    <option value="{{ $topic->id }}">{{ $topic->topic_name }}</option>
                                @endforeach
                            </select>
                            @error('topic_id') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Level CEFR</label>
                            <select wire:model="cefr_level" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @foreach ($this->levels as $level)
                                    <option value="{{ $level->value }}">{{ $level->label() }}</option>
                                @endforeach
                            </select>
                            @error('cefr_level') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Teks Soal</label>
                        <textarea wire:model="question_text" rows="3" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm"></textarea>
                        @error('question_text') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Jawaban Standar
                            <span class="font-normal text-slate-400">(min: A1=12, A2=20, B1=35, B2=55, C1=75, C2=90 kata)</span>
                        </label>
                        <textarea wire:model="standard_answer" rows="5" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm"></textarea>
                        @error('standard_answer') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Key Point</label>
                        <input type="text" wire:model="key_point" placeholder="mis. work experience" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        @error('key_point') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
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