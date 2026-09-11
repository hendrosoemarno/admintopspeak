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

        {{-- Tab Test Type --}}
        <div class="mt-4 flex flex-wrap gap-1.5 border-b border-slate-200 pb-2">
            <button wire:click="setTestType('ADAPTIVE')"
                    @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $activeTestType === 'ADAPTIVE', 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activeTestType !== 'ADAPTIVE'])>
                Adaptive ({{ $testTypeCounts['ADAPTIVE'] ?? 0 }})
            </button>
            <button wire:click="setTestType('IELTS_SPEAKING')"
                    @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $activeTestType === 'IELTS_SPEAKING', 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activeTestType !== 'IELTS_SPEAKING'])>
                IELTS Speaking ({{ $testTypeCounts['IELTS_SPEAKING'] ?? 0 }})
            </button>
            <button wire:click="setTestType('TOEFL_IBT')"
                    @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $activeTestType === 'TOEFL_IBT', 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activeTestType !== 'TOEFL_IBT'])>
                TOEFL iBT ({{ $testTypeCounts['TOEFL_IBT'] ?? 0 }})
            </button>
        </div>

        {{-- Sub-tab Part (khusus IELTS/TOEFL) --}}
        @if ($isExamTab)
            <div class="mt-3 flex flex-wrap gap-1.5">
                <button wire:click="setPart(null)"
                        @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-slate-800 text-white' => $activePart === null, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activePart !== null])>
                    Semua Part
                </button>
                @foreach ($partCounts->keys()->sort() as $part)
                    <button wire:click="setPart({{ $part }})"
                            @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-slate-800 text-white' => (int) $activePart === (int) $part, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => (int) $activePart !== (int) $part])>
                        Part {{ $part }} ({{ $partCounts[$part] }})
                    </button>
                @endforeach
            </div>
        @endif

        <div class="mt-4 flex flex-wrap gap-1.5 border-b border-slate-200 pb-2">
            @foreach ($this->levels as $level)
                <button wire:click="setLevel('{{ $level->value }}')"
                        @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $activeLevel === $level->value, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activeLevel !== $level->value])>
                    {{ $level->value }} ({{ $levelCounts[$level->value] ?? 0 }})
                </button>
            @endforeach
        </div>
    </div>

    {{-- Mode exam: daftar soal langsung (part + level), tanpa kartu topik --}}
    @if ($isExamTab)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-wrap items-center gap-3">
            <span class="text-sm text-slate-500">{{ $activeTestType === 'IELTS_SPEAKING' ? 'IELTS Speaking' : 'TOEFL iBT' }}</span>
            @if ($activePart !== null)
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-white">Part {{ $activePart }}</span>
            @endif
            @if ($activeLevel !== '')
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $activeLevel }}</span>
            @endif
            <span class="text-xs text-slate-500">{{ $questions->total() }} soal</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($questions as $q)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-white">Part {{ $q->part_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $q->cefr_level->value }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $q->topic_category }}</span>
                    </div>
                    <p class="text-sm text-slate-800 flex-1">{{ $q->question_text }}</p>
                    @if ($q->standard_answer)
                        <div class="mt-3 rounded-lg bg-emerald-50 border border-emerald-100 p-3">
                            <div class="text-xs font-semibold text-emerald-700 mb-1">Jawaban Standar</div>
                            <p class="text-xs text-emerald-800 leading-relaxed">{{ $q->standard_answer }}</p>
                        </div>
                    @endif
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $q->conversation_logs_count }} log terpakai</span>
                        <div class="space-x-2">
                            <button wire:click="openEdit({{ $q->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="delete({{ $q->id }})" wire:confirm="Hapus soal ini?"
                                    class="text-red-500 hover:underline">Hapus</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                    Tidak ada soal ditemukan.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $questions->links() }}
        </div>
    @elseif ($activeTopic === '')
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($topics as $topic)
                <button wire:click="setTopic('{{ $topic->topic_category }}')"
                        class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 text-left hover:border-indigo-300 hover:shadow transition group">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-800 group-hover:text-indigo-700">{{ $topic->topic_category }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $topic->total }} soal</span>
                    </div>
                    <div class="mt-2 text-xs text-slate-400">Klik untuk melihat soal level {{ $activeLevel }}</div>
                </button>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                    Belum ada topik pada level {{ $activeLevel }}.
                </div>
            @endforelse
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-wrap items-center gap-3">
            <button wire:click="setTopic('')" class="text-sm text-indigo-600 hover:underline">← Kembali ke topik</button>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $activeLevel }}</span>
            <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $activeTopic }}</span>
            <span class="text-xs text-slate-500">{{ $topicTotal }} soal</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($questions as $q)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $q->cefr_level->value }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $q->test_type->label() }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $q->topic_category }}</span>
                        @if ($q->is_starter)
                            <span class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700">Starter</span>
                        @endif
                    </div>
                    <p class="text-sm text-slate-800 flex-1">{{ $q->question_text }}</p>
                    @if ($q->standard_answer)
                        <div class="mt-3 rounded-lg bg-emerald-50 border border-emerald-100 p-3">
                            <div class="text-xs font-semibold text-emerald-700 mb-1">Jawaban Standar</div>
                            <p class="text-xs text-emerald-800 leading-relaxed">{{ $q->standard_answer }}</p>
                        </div>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-1">
                        @foreach ($q->required_vocab_tags ?? [] as $tag)
                            <span class="text-xs px-2 py-0.5 rounded bg-indigo-50 text-indigo-600">{{ $tag }}</span>
                        @endforeach
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span>{{ $q->conversation_logs_count }} log terpakai</span>
                        <div class="space-x-2">
                            <button wire:click="toggleStarter({{ $q->id }})" title="Toggle starter"
                                    class="text-amber-600 hover:underline">{{ $q->is_starter ? 'Unstarter' : 'Jadikan starter' }}</button>
                            <button wire:click="openEdit({{ $q->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="delete({{ $q->id }})" wire:confirm="Hapus soal ini?"
                                    class="text-red-500 hover:underline">Hapus</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                    Tidak ada soal ditemukan.
                </div>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $questions->links() }}
        </div>
    @endif

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">{{ $editingId ? 'Edit Soal' : 'Tambah Soal Baru' }}</h3>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipe Tes</label>
                            <select wire:model="test_type" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @foreach ($this->testTypes as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Part</label>
                            <input type="number" wire:model="part_number" min="1" max="4" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Level CEFR</label>
                            <select wire:model="cefr_level" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @foreach ($this->levels as $level)
                                    <option value="{{ $level->value }}">{{ $level->label() }}</option>
                                @endforeach
                            </select>
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Vocab Tags (pisahkan koma)</label>
                            <input type="text" wire:model="vocabTags" placeholder="name, greet, hobby"
                                   class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Kategori Topik</label>
                            <input type="text" wire:model="topic_category" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Audio URL</label>
                        <input type="url" wire:model="audioUrl" placeholder="https://…"
                               class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        @error('audioUrl') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" wire:model="is_starter" class="rounded border-slate-300">
                        Soal pembuka sesi (starter)
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
