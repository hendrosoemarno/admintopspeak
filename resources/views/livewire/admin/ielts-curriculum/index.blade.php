<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    {{-- ======================= MODE DAFTAR UNIT ======================= --}}
    @if ($activeLessonId === null && $activeUnitId === null)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
                <div class="sm:col-span-8">
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari unit…"
                           class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div class="sm:col-span-4 text-right">
                    <button wire:click="openCreateUnit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                        + Tambah Unit
                    </button>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap gap-1.5 border-b border-slate-200 pb-2">
                <button wire:click="setPart(null)"
                        @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $activePart === null, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activePart !== null])>
                    Semua ({{ collect($this->partCounts)->sum() }})
                </button>
                @foreach ([1, 2, 3] as $part)
                    <button wire:click="setPart({{ $part }})"
                            @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => (int) $activePart === $part, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => (int) $activePart !== $part])>
                        Part {{ $part }} ({{ $this->partCounts[$part] ?? 0 }})
                    </button>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($units as $unit)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-white">Part {{ $unit->part }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">Unit {{ $unit->unit_number }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $unit->lessons_count }} lesson</span>
                    </div>
                    <h3 class="font-semibold text-slate-900">{{ $unit->title }}</h3>
                    @if ($unit->outcome)
                        <p class="mt-1 text-xs text-slate-500 italic flex-1">{{ \Illuminate\Support\Str::limit($unit->outcome, 120) }}</p>
                    @endif
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <button wire:click="openUnit({{ $unit->id }})" class="text-indigo-600 font-semibold hover:underline">Lihat Lessons →</button>
                        <div class="space-x-2">
                            <button wire:click="openEditUnit({{ $unit->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="deleteUnit({{ $unit->id }})" wire:confirm="Hapus unit beserta seluruh lesson & soalnya?"
                                    class="text-red-500 hover:underline">Hapus</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                    Belum ada unit. Jalankan seeder IeltsCurriculumSeeder untuk data default.
                </div>
            @endforelse
        </div>
    @endif

    {{-- ======================= MODE LESSONS UNIT ======================= --}}
    @if ($activeLessonId === null && $activeUnitId !== null && $unit)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="backToUnits" class="text-sm text-indigo-600 hover:underline">← Kembali ke Units</button>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-white">Part {{ $unit->part }}</span>
                <h3 class="font-semibold text-slate-900">{{ $unit->title }}</h3>
                <span class="text-xs text-slate-500">Unit {{ $unit->unit_number }}</span>
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari lesson…"
                       class="flex-1 min-w-[200px] rounded-lg border-slate-300 border px-3 py-2 text-sm">
                <button wire:click="openCreateLesson" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    + Tambah Lesson
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($lessons as $lesson)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col">
                    <div class="flex items-center gap-2 mb-2">
                        <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold w-full', 'bg-green-100 text-green-700' => $lesson->difficulty->value === 'Easy', 'bg-amber-100 text-amber-700' => $lesson->difficulty->value === 'Medium', 'bg-red-100 text-red-700' => $lesson->difficulty->value === 'Difficult'])>
                            {{ $lesson->difficulty->label() }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $lesson->questions_count }} soal</span>
                        <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-green-100 text-green-700' => ($passCounts[$lesson->id] ?? 0) > 0, 'bg-slate-100 text-slate-500' => ($passCounts[$lesson->id] ?? 0) === 0])>
                            {{ $passCounts[$lesson->id] ?? 0 }} lulus
                        </span>
                    </div>
                    <h3 class="font-semibold text-slate-900">Lesson {{ $lesson->lesson_number }}: {{ $lesson->title }}</h3>
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                        <button wire:click="openLesson({{ $lesson->id }})" class="text-indigo-600 font-semibold hover:underline">Lihat Soal →</button>
                        <div class="space-x-2">
                            <button wire:click="openEditLesson({{ $lesson->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="deleteLesson({{ $lesson->id }})" wire:confirm="Hapus lesson beserta seluruh soalnya?" class="text-red-500 hover:underline">Hapus</button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                    Belum ada lesson di unit ini.
                </div>
            @endforelse
        </div>
    @endif

    {{-- ======================= MODE SOAL LESSON ======================= --}}
    @if ($activeLessonId !== null && $lesson)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="backToLessons" class="text-sm text-indigo-600 hover:underline">← Kembali ke Lessons</button>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-white">Unit {{ $lesson->unit->unit_number }}</span>
                <h3 class="font-semibold text-slate-900">{{ $lesson->title }}</h3>
                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">{{ $lesson->difficulty->label() }}</span>
                @if ($passCount > 0)
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">{{ $passCount }} user lulus</span>
                @endif
            </div>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari soal…"
                       class="flex-1 min-w-[200px] rounded-lg border-slate-300 border px-3 py-2 text-sm">
                <button wire:click="openCreateQuestion" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                    + Tambah Soal
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @forelse ($questions as $q)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-col">
                    <span class="self-start px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 mb-2">Key Point: {{ $q->key_point }}</span>
                    <p class="text-sm text-slate-800 flex-1">{{ $q->question_text }}</p>
                    @if ($q->model_answer)
                        <div class="mt-3 rounded-lg bg-emerald-50 border border-emerald-100 p-3">
                            <div class="text-xs font-semibold text-emerald-700 mb-1">Model Answer</div>
                            <p class="text-xs text-emerald-800 leading-relaxed">{{ $q->model_answer }}</p>
                        </div>
                    @endif
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-end text-xs space-x-2">
                        <button wire:click="openEditQuestion({{ $q->id }})" class="text-indigo-600 hover:underline">Edit</button>
                        <button wire:click="deleteQuestion({{ $q->id }})" wire:confirm="Hapus soal ini?" class="text-red-500 hover:underline">Hapus</button>
                    </div>
                </div>
            @empty
                <div class="md:col-span-2 xl:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-10 text-center text-slate-500">
                    Belum ada soal di lesson ini.
                </div>
            @endforelse
        </div>
    @endif

    {{-- ======================= MODAL FORM (dinamis) ======================= --}}
    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">{{ $editingId ? 'Edit' : 'Tambah' }} {{ ucfirst($entityType) }}</h3>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    @if ($entityType === 'unit')
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nomor Unit</label>
                                <input type="number" wire:model="unit_number" min="1" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @error('unit_number') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Part</label>
                                <select wire:model="unit_part" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                    @foreach ([1, 2, 3] as $part)
                                        <option value="{{ $part }}">Part {{ $part }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Judul</label>
                                <input type="text" wire:model="unit_title" placeholder="Home & Family" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @error('unit_title') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Outcome (target pembelajaran)</label>
                            <textarea wire:model="unit_outcome" rows="3" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm"></textarea>
                        </div>
                    @elseif ($entityType === 'lesson')
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nomor Lesson</label>
                                <input type="number" wire:model="lesson_number" min="1" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @error('lesson_number') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Difficulty</label>
                                <select wire:model="lesson_difficulty" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                    @foreach ($this->difficulties as $difficulty)
                                        <option value="{{ $difficulty->value }}">{{ $difficulty->label() }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Judul Lesson</label>
                                <input type="text" wire:model="lesson_title" placeholder="Hometown & Living Place" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                @error('lesson_title') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Teks Soal</label>
                            <textarea wire:model="question_text" rows="3" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm"></textarea>
                            @error('question_text') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Key Point (frasa/kolokasi wajib)</label>
                            <input type="text" wire:model="key_point" placeholder="a close-knit family" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                            @error('key_point') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Model Answer (referensi AI)</label>
                            <textarea wire:model="model_answer" rows="5" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm"></textarea>
                            @error('model_answer') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeForm" class="px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>