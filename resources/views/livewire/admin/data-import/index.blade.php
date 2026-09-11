<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h2 class="font-semibold text-slate-900 mb-3">Jenis Data</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    @foreach ($templates as $type => $meta)
                        <button wire:click="setType('{{ $type }}')"
                                @class([
                                    'px-4 py-3 rounded-lg border text-left text-sm transition',
                                    'border-indigo-600 bg-indigo-50 text-indigo-700' => $importType === $type,
                                    'border-slate-200 hover:border-slate-300' => $importType !== $type,
                                ])>
                            <div class="font-semibold">{{ $meta['title'] }}</div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-slate-500">Pilih untuk import</span>
                                <span wire:click.stop="exportType('{{ $type }}')"
                                      class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 px-2 py-0.5 rounded hover:bg-indigo-100">
                                    Export
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-slate-900">Konten JSON</h2>
                    <button wire:click="fillExample"
                            class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-600 hover:bg-slate-50">
                        Isi Contoh
                    </button>
                </div>

                <div class="space-y-3">
                    @if ($importType === 'ielts_curriculum')
                        <div class="rounded-lg border border-indigo-200 bg-indigo-50/50 p-3 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-semibold text-slate-800">Target Unit</h3>
                                @error('curriculumUnitId')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Part</label>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ([1, 2, 3] as $part)
                                        <button wire:click="setCurriculumPart({{ $part }})"
                                                @class(['px-3 py-1.5 rounded-lg text-xs font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => (int) $curriculumPart === $part, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => (int) $curriculumPart !== $part])>
                                            Part {{ $part }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-slate-600 mb-1">Pilih Unit</label>
                                <select wire:change="setCurriculumUnit($event.target.value)"
                                        class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm bg-white">
                                    <option value="">— Pilih unit —</option>
                                    @foreach ($this->curriculumUnits as $unit)
                                        <option value="{{ $unit->id }}" @selected((int) $curriculumUnitId === (int) $unit->id)>
                                            Unit {{ $unit->unit_number }}: {{ $unit->title }}
                                        </option>
                                    @endforeach
                                </select>
                                @if ($curriculumUnitId)
                                    <div class="mt-2 text-xs text-slate-500">
                                        Import menambah/meng-update soal ke lesson yang sudah ada di unit ini
                                        ({{ count($this->curriculumLessons) }} lesson tersedia).
                                    </div>
                                @else
                                    <div class="mt-2 text-xs text-slate-400">
                                        Pilih part lalu unit terlebih dahulu. Unit_number &amp; part tidak diubah.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div>
                        <textarea wire:model="jsonContent" rows="14" spellcheck="false"
                                  placeholder='[{ "question_text": "…", "cefr_level": "A1" }]'
                                  class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm font-mono"></textarea>
                        @error('jsonContent') <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                    </div>

                    <button wire:click="import" wire:loading.attr="disabled"
                            class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="import">Preview</span>
                        <span wire:loading wire:target="import">Memproses…</span>
                    </button>
                </div>
            </div>

            @if ($preview)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <h2 class="font-semibold text-slate-900 mb-2">Preview Import</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Total baris</span><span class="font-semibold">{{ $preview['total'] }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Valid</span><span class="font-semibold text-green-600">{{ $preview['valid'] }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Error</span><span class="font-semibold text-red-500">{{ count($preview['errors']) }}</span></div>
                    </div>

                    @if ($preview['errors'])
                        <div class="mt-3">
                            <div class="text-xs font-semibold text-red-600 mb-1">Baris bermasalah:</div>
                            <ul class="space-y-1">
                                @foreach ($preview['errors'] as $error)
                                    <li class="text-xs text-red-500">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (! empty($preview['duplicates_in_json']))
                        <div class="mt-3">
                            <div class="text-xs font-semibold text-amber-600 mb-1">Duplikat dalam JSON (akan di-overwrite, bukan baris baru):</div>
                            <ul class="space-y-1">
                                @foreach ($preview['duplicates_in_json'] as $key)
                                    <li class="text-xs text-amber-500">{{ $key }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="flex justify-end gap-2 pt-3">
                        <button wire:click="cancelPreview" class="px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                        <button wire:click="confirmImport" wire:loading.attr="disabled"
                                class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 disabled:opacity-50">
                            <span wire:loading.remove wire:target="confirmImport">Konfirmasi Import ({{ $preview['valid'] }})</span>
                            <span wire:loading wire:target="confirmImport">Mengimpor…</span>
                        </button>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-slate-900">Export JSON</h2>
                    <button wire:click="exportData" wire:loading.attr="disabled"
                            class="px-3 py-1.5 rounded-lg bg-slate-800 text-white text-xs font-semibold hover:bg-slate-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="exportData">Export Data</span>
                        <span wire:loading wire:target="exportData">Mengekspor…</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-3">Ambil semua data {{ strtolower($templates[$importType]['title']) }} sebagai JSON.</p>

                @if ($exportJson !== '')
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-slate-400">Diekspor: {{ $exportedAt }}</span>
                        <div class="space-x-2">
                            <button onclick="navigator.clipboard.writeText(document.getElementById('export-textarea').value); this.textContent='Tersalin!'; setTimeout(()=>this.textContent='Salin', 1500);"
                                    class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-600 hover:bg-slate-50">Salin</button>
                            <button wire:click="clearExport" class="px-3 py-1.5 rounded-lg border border-slate-300 text-xs text-slate-600 hover:bg-slate-50">Bersihkan</button>
                        </div>
                    </div>
                    <textarea id="export-textarea" readonly rows="14" spellcheck="false"
                              class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm font-mono bg-slate-50">{{ $exportJson }}</textarea>
                @else
                    <div class="text-sm text-slate-400 border border-dashed border-slate-200 rounded-lg p-4 text-center">
                        Klik "Export Data" untuk menghasilkan JSON.
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                <h2 class="font-semibold text-slate-900 mb-2">Format JSON</h2>
                <p class="text-xs text-slate-500 mb-3">Data berupa array objek. Kolom yang sama dengan yang ada di form tambah:</p>
                <pre class="text-xs font-mono bg-slate-50 rounded-lg p-3 overflow-x-auto whitespace-pre-wrap">{{ $this->sampleJson($importType) }}</pre>
            </div>

            @if ($result)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <h2 class="font-semibold text-slate-900 mb-2">Hasil Import</h2>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Baru dibuat</span><span class="font-semibold text-green-600">{{ $result['created'] }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Duplikat (di-update)</span><span class="font-semibold text-amber-600">{{ $result['updated'] }}</span></div>
                    </div>
                    @if (! empty($result['duplicates']))
                        <div class="mt-3">
                            <div class="text-xs font-semibold text-amber-600 mb-1">Data duplikat yang ada di database:</div>
                            <ul class="space-y-1 max-h-40 overflow-y-auto">
                                @foreach ($result['duplicates'] as $key)
                                    <li class="text-xs text-amber-500">{{ $key }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (! empty($result['duplicates_in_json']))
                        <div class="mt-3">
                            <div class="text-xs font-semibold text-orange-600 mb-1">Duplikat di dalam JSON (muncul lebih dari sekali):</div>
                            <ul class="space-y-1 max-h-40 overflow-y-auto">
                                @foreach ($result['duplicates_in_json'] as $key)
                                    <li class="text-xs text-orange-500">{{ $key }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if ($result['errors'])
                        <div class="mt-3">
                            <div class="text-xs font-semibold text-red-600 mb-1">{{ count($result['errors']) }} error:</div>
                            <ul class="space-y-1">
                                @foreach ($result['errors'] as $error)
                                    <li class="text-xs text-red-500">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
