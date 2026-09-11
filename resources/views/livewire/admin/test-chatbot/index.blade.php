<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
    {{-- Panel kiri: kontrol sesi --}}
    <div class="lg:col-span-4 space-y-4">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
            <h2 class="font-bold text-slate-900 mb-1">Kontrol Sesi</h2>
            <p class="text-xs text-slate-500 mb-4">Uji coba alur yang sama dengan API Android: start → evaluate-turn → verify-repetition → complete.</p>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">Mode</label>
                <div class="grid grid-cols-2 gap-2">
                    <button wire:click="setMode('ADAPTIVE')"
                            @class(['px-3 py-2 rounded-lg text-sm font-semibold border', 'bg-indigo-600 text-white border-indigo-600' => $mode === 'ADAPTIVE', 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' => $mode !== 'ADAPTIVE'])>
                        Adaptive Leveling
                    </button>
                    <button wire:click="setMode('THEMATIC')"
                            @class(['px-3 py-2 rounded-lg text-sm font-semibold border', 'bg-indigo-600 text-white border-indigo-600' => $mode === 'THEMATIC', 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' => $mode !== 'THEMATIC'])>
                        Thematic Topics
                    </button>
                    <button wire:click="setMode('IELTS_SPEAKING')"
                            @class(['px-3 py-2 rounded-lg text-sm font-semibold border', 'bg-indigo-600 text-white border-indigo-600' => $mode === 'IELTS_SPEAKING', 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' => $mode !== 'IELTS_SPEAKING'])>
                        IELTS Speaking
                    </button>
                    <button wire:click="setMode('TOEFL_IBT')"
                            @class(['px-3 py-2 rounded-lg text-sm font-semibold border', 'bg-indigo-600 text-white border-indigo-600' => $mode === 'TOEFL_IBT', 'bg-white text-slate-700 border-slate-300 hover:bg-slate-50' => $mode !== 'TOEFL_IBT'])>
                        TOEFL iBT
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-slate-700 mb-1">User Identitas</label>
                <select wire:change="selectUser($event.target.value)"
                        class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    @foreach ($this->users as $user)
                        <option value="{{ $user->id }}" @selected((int) $selectedUserId === $user->id)>
                            {{ $user->name }} — {{ $user->current_cefr_level->value }}
                            ({{ $user->isPremiumActive() ? 'PREMIUM' : ($user->remaining_trial_sessions . ' sesi') }})
                        </option>
                    @endforeach
                </select>
            </div>

            @if ($mode === 'ADAPTIVE')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Level Start</label>
                    <select wire:change="setLevel($event.target.value)"
                            class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        @foreach ($this->levels as $level)
                            <option value="{{ $level->value }}" @selected($selectedLevel === $level->value)>{{ $level->value }} — {{ $level->label() }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-400">Level user diset sementara selama uji coba, lalu dikembalikan.</p>
                </div>
            @elseif ($mode === 'THEMATIC')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Topik Thematic</label>
                    <select wire:change="selectTopic($event.target.value)"
                            class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        @forelse ($this->topics as $topic)
                            <option value="{{ $topic->id }}" @selected((int) $selectedTopicId === $topic->id)>{{ $topic->topic_name }} ({{ $topic->selected_level }})</option>
                        @empty
                            <option value="">Belum ada topik aktif</option>
                        @endforelse
                    </select>
                </div>
            @else
                <div class="mb-4 rounded-lg bg-slate-50 border border-slate-200 p-3 text-xs text-slate-600">
                    @if ($mode === 'IELTS_SPEAKING')
                        Soal diambil dari kurikulum baru. Klik <strong>Pilih Unit &amp; Lesson</strong>,
                        lalu pilih unit &amp; lesson; soal keluar sesuai lesson yang dipilih.
                    @else
                        Simulasi ujian TOEFL iBT Speaking (Task 1–4). Soal dipilih berurutan per part,
                        tanpa promosi level.
                    @endif
                </div>
            @endif

            <label class="flex items-center gap-2 text-sm text-slate-700 mb-4">
                <input type="checkbox" wire:model="restoreLevelAfterTest" class="rounded border-slate-300">
                Kembalikan level user setelah sesi
            </label>

            <div class="flex gap-2">
                <button wire:click="startSession" wire:loading.attr="disabled"
                        @class(['flex-1 px-4 py-2 rounded-lg text-sm font-semibold', 'bg-indigo-600 text-white hover:bg-indigo-700' => $step === 'idle' || $step === 'completed', 'bg-slate-200 text-slate-400 cursor-not-allowed' => in_array($step, ['awaiting_answer', 'awaiting_repetition', 'awaiting_complete', 'selecting_lessons'])])>
                    @if ($step === 'completed')
                        Mulai Sesi Baru
                    @elseif ($mode === 'IELTS_SPEAKING' && $step === 'idle')
                        Pilih Unit &amp; Lesson
                    @elseif ($step === 'selecting_lessons')
                        Pilih di daftar →
                    @else
                        Mulai Sesi
                    @endif
                </button>
                <button wire:click="resetAll" wire:loading.attr="disabled"
                        class="px-4 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                    Reset
                </button>
            </div>
        </div>

        @if ($summary)
            <div class="bg-white rounded-xl shadow-sm border border-emerald-200 p-4">
                <h3 class="font-bold text-emerald-800 mb-3">Hasil Sesi</h3>
                <dl class="space-y-2 text-sm">
                    @if ($summary['exam_report'] ?? null)
                        <div class="flex justify-between"><dt class="text-slate-500">Ujian</dt><dd class="font-semibold">{{ $summary['exam_report']['test_type'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Skor Total</dt><dd class="font-semibold">{{ $summary['exam_report']['total_score'] }}/{{ $summary['exam_report']['max_score'] }} ({{ $summary['exam_report']['score_pct'] }}%)</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Grammar</dt><dd class="font-semibold">{{ $summary['exam_report']['grammar_accuracy'] }}</dd></div>
                        <div class="mt-3 border-t border-emerald-100 pt-2">
                            <div class="text-xs font-semibold text-slate-500 mb-1">Per Part</div>
                            @foreach ($summary['exam_report']['parts'] as $part)
                                <div class="flex justify-between py-0.5">
                                    <dt class="text-slate-500">Part {{ $part['part_number'] }} ({{ $part['turns'] }} turn)</dt>
                                    <dd class="font-semibold">{{ $part['total_score'] }}/{{ $part['max_score'] }} ({{ $part['score_pct'] }}%)</dd>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex justify-between"><dt class="text-slate-500">Level Awal</dt><dd class="font-semibold">{{ $summary['start_level'] ?? $summary['cefr_level_current'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Turn Selesai</dt><dd class="font-semibold">{{ $summary['total_turns_completed'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Skor 4-Turn</dt><dd class="font-semibold">{{ $summary['accumulated_score'] }}/8</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Grammar</dt><dd class="font-semibold">{{ $summary['diagnostic_report']['grammar_accuracy'] }}</dd></div>
                        <div class="flex justify-between"><dt class="text-slate-500">Promosi</dt>
                            <dd>
                                @if ($summary['is_promoted'])
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        {{ $summary['previous_cefr_level'] }} → {{ $summary['new_cefr_level'] }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">Tidak</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                    <div class="flex justify-between"><dt class="text-slate-500">Sesi Trial Tersisa</dt><dd class="font-semibold">{{ $summary['remaining_trial_sessions'] }}</dd></div>
                </dl>
            </div>
        @endif
    </div>

    {{-- Panel kanan: percakapan --}}
    <div class="lg:col-span-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-[600px]">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-sm font-semibold text-slate-700">
                    @switch($mode)
                        @case('THEMATIC') Thematic Topics @break
                        @case('IELTS_SPEAKING') IELTS Speaking @break
                        @case('TOEFL_IBT') TOEFL iBT @break
                        @default Adaptive Leveling
                    @endswitch
                    @if ($sessionId) — Sesi {{ substr($sessionId, 0, 8) }}… @endif
                </span>
                <span class="ml-auto text-xs text-slate-400">
                    @switch($step)
                        @case('idle') Belum dimulai @break
                        @case('selecting_lessons') Pilih Unit &amp; Lesson @break
                        @case('awaiting_answer') Menunggu jawaban @break
                        @case('awaiting_repetition') Menunggu pengulangan @break
                        @case('awaiting_complete') Siap selesai @break
                        @case('completed') Selesai @break
                    @endswitch
                </span>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                @if ($step === 'selecting_lessons')
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 mb-3">IELTS Curriculum — pilih unit &amp; lesson</h3>
                        @forelse ($this->ieltsUnits as $unit)
                            <div class="mb-2">
                                <button wire:click="toggleUnit({{ $unit->id }})"
                                        class="w-full flex items-center justify-between gap-2 rounded-lg border px-3 py-2 text-sm font-semibold text-left
                                               {{ $expandedUnitId === $unit->id ? 'border-indigo-300 bg-indigo-50 text-indigo-800' : 'border-slate-200 bg-white text-slate-800 hover:bg-slate-50' }}">
                                    <span>Unit {{ $unit->unit_number }} — {{ $unit->title }}</span>
                                    <span class="shrink-0 text-xs font-medium {{ $expandedUnitId === $unit->id ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 rounded-full">
                                        Part {{ $unit->part }} {{ $expandedUnitId === $unit->id ? '▲' : '▼' }}
                                    </span>
                                </button>

                                @if ($expandedUnitId === $unit->id)
                                    <div class="ml-3 mt-1 space-y-1 border-l-2 border-indigo-100 pl-3">
                                        @forelse ($unit->lessons as $lesson)
                                            @if ($lesson->questions_count > 0)
                                                <button wire:click="startIeltsLesson({{ $lesson->id }})"
                                                        class="w-full flex items-center justify-between gap-2 rounded-md border border-slate-200 bg-white px-3 py-2 text-sm text-left text-slate-700 hover:bg-indigo-50 hover:border-indigo-200">
                                                    <span>{{ $lesson->lesson_number }}. {{ $lesson->title }}</span>
                                                    <span class="shrink-0 text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">{{ $lesson->questions_count }} soal</span>
                                                </button>
                                            @else
                                                <div class="flex items-center justify-between gap-2 rounded-md border border-slate-100 bg-slate-50 px-3 py-2 text-sm text-slate-400 cursor-not-allowed">
                                                    <span>{{ $lesson->lesson_number }}. {{ $lesson->title }}</span>
                                                    <span class="shrink-0 text-xs bg-slate-100 text-slate-400 px-2 py-0.5 rounded-full">belum ada soal</span>
                                                </div>
                                            @endif
                                        @empty
                                            <div class="text-xs text-slate-400 px-2 py-1">Belum ada lesson.</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="text-sm text-slate-400">Belum ada unit kurikulum. Isi units/lessons/questions dulu.</div>
                        @endforelse
                    </div>
                @else
                    @forelse ($messages as $msg)
                        @if ($msg['role'] === 'user')
                            <div class="flex justify-end">
                                <div class="max-w-[80%] rounded-2xl rounded-br-sm bg-indigo-600 text-white px-4 py-2.5 text-sm">
                                    {{ $msg['label'] }}
                                </div>
                            </div>
                        @elseif ($msg['role'] === 'system')
                            <div class="flex justify-center">
                                <div class="text-xs bg-red-50 text-red-600 border border-red-200 rounded-lg px-3 py-1.5">
                                    {{ $msg['label'] }}
                                </div>
                            </div>
                        @else
                            <div class="flex justify-start">
                                <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-slate-100 px-4 py-2.5 text-sm text-slate-800">
                                    <div class="text-xs font-semibold text-slate-500 mb-1">{{ $msg['label'] }}</div>
                                    @if (! empty($msg['text']))
                                        <p class="leading-relaxed">{{ $msg['text'] }}</p>
                                    @endif
                                    @if (! empty($msg['key_point']))
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">Key Point: {{ $msg['key_point'] }}</span>
                                        </div>
                                    @endif
                                    @if (! empty($msg['topic']))
                                        <div class="mt-1.5 flex flex-wrap gap-1">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-50 text-indigo-700">📌 {{ $msg['topic'] }}</span>
                                            @if (! empty($msg['level']))
                                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600">Level {{ $msg['level'] }}</span>
                                            @endif
                                        </div>
                                    @endif
                                    @if (! empty($msg['repetition']))
                                        <div class="mt-2 rounded-lg bg-amber-50 border border-amber-200 px-3 py-2 text-amber-800">
                                            {{ $msg['text'] }}
                                        </div>
                                    @endif
                                    @if (! empty($msg['score']))
                                        <div class="mt-2 grid grid-cols-2 gap-1.5">
                                            @foreach ($msg['score'] as $line)
                                                <div class="text-xs rounded bg-white border border-slate-200 px-2 py-1">{{ $line }}</div>
                                            @endforeach
                                        </div>
@if (! empty($msg['suggested_answer']))
                                        <div class="mt-2 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-emerald-800">
                                            <div class="text-[11px] font-semibold text-emerald-600 mb-0.5">Saran Jawaban Benar</div>
                                            {{ $msg['suggested_answer'] }}
                                        </div>
                                    @endif
                                    @if (! empty($msg['key_point_target']))
                                        <div class="mt-1.5 text-[11px] text-slate-500">Target: {{ $msg['key_point_target'] }}</div>
                                    @endif
                                    @endif
                                    @if (! empty($msg['summary']))
                                        <p class="text-xs text-slate-500">Lihat ringkasan di panel kiri.</p>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @empty
                        <div class="h-full flex items-center justify-center text-slate-400 text-sm">
                            Pilih mode &amp; user, lalu klik <strong>&nbsp;Mulai Sesi&nbsp;</strong>.
                        </div>
                    @endforelse
                @endif
            </div>

            <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                @if ($step === 'awaiting_answer')
                    <form wire:submit="submitAnswer" class="flex gap-2">
                        <input type="text" wire:model="transcript" placeholder="Ketik transkrip jawaban user… (mis. My name is…)"
                               class="flex-1 rounded-lg border-slate-300 border px-3 py-2 text-sm">
                        <button type="submit" wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                            Kirim
                        </button>
                    </form>
                @elseif ($step === 'awaiting_repetition')
                    <form wire:submit="submitRepetition" class="space-y-2">
                        <div class="text-xs text-amber-700 font-medium">Ulangi kalimat: "{{ $expectedRepetition }}"</div>
                        <div class="flex gap-2">
                            <input type="text" wire:model="repetition" placeholder="Ketik hasil pengulangan…"
                                   class="flex-1 rounded-lg border-slate-300 border px-3 py-2 text-sm">
                            <button type="submit" wire:loading.attr="disabled"
                                    class="px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700">
                                Kirim
                            </button>
                        </div>
                    </form>
                @elseif ($step === 'awaiting_complete')
                    <button wire:click="completeSession" wire:loading.attr="disabled"
                            class="w-full px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                        Selesaikan Sesi &amp; Lihat Diagnosa
                    </button>
                @elseif ($step === 'completed')
                    <div class="text-center text-sm text-emerald-700 font-semibold">Sesi selesai. Klik "Mulai Sesi Baru" untuk uji ulang.</div>
                @else
                    <div class="text-center text-sm text-slate-400">Sesi belum dimulai.</div>
                @endif
            </div>
        </div>
    </div>
</div>
