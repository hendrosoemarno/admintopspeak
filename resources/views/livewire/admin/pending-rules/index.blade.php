<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex items-center justify-between mb-4">
        <p class="text-sm text-slate-500">
            Usulan aturan grammar dari hasil evaluasi percakapan. Menyetujui akan otomatis membuat aturan baru di <a href="{{ route('admin.grammar-rules.index') }}" class="text-indigo-600 hover:underline">Grammar Rules</a>.
            @if ($llmConfigured)
                <span class="inline-flex items-center gap-1 ml-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">🤖 LLM aktif — regex dibuat otomatis</span>
            @else
                <span class="inline-flex items-center gap-1 ml-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">⚠️ LLM belum dikonfigurasi — pakai regex heuristic</span>
            @endif
        </p>
        <div class="flex gap-1 bg-white rounded-lg border border-slate-200 p-1 text-sm">
            @foreach (['PENDING' => 'Pending', 'APPROVED' => 'Disetujui', 'REJECTED' => 'Ditolak'] as $key => $label)
                <button wire:click="$set('statusFilter', '{{ $key }}')"
                        @class(['px-3 py-1 rounded-md font-medium', 'bg-indigo-600 text-white' => $statusFilter === $key, 'text-slate-600 hover:bg-slate-100' => $statusFilter !== $key])>
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Masukan User</th>
                    <th class="px-4 py-3">Kalimat Benar</th>
                    <th class="px-4 py-3">Error Terdeteksi</th>
                    <th class="px-4 py-3">Hasil Grammar Rule</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($pendingList as $pending)
                    <tr>
                        <td class="px-4 py-3 italic text-slate-600 min-w-[220px] max-w-md">"{{ $pending->raw_user_input }}"</td>
                        <td class="px-4 py-3 max-w-md">
                            @if ($pending->suggested_correct_sentence)
                                <span class="text-emerald-700">{{ $pending->suggested_correct_sentence }}</span>
                            @else
                                <span class="text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($pending->grammarRule)
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-semibold text-indigo-700">{{ $pending->grammarRule->rule_code }}</span>
                                    <span class="text-[11px] text-slate-400">#{{ $pending->grammarRule->id }}</span>
                                    <span @class(['px-2 py-0.5 rounded-full text-[11px] font-semibold', 'bg-red-100 text-red-700' => $pending->grammarRule->rule_type === 'error', 'bg-emerald-100 text-emerald-700' => $pending->grammarRule->rule_type === 'positive'])>
                                        {{ $pending->grammarRule->rule_type === 'positive' ? 'pola benar' : 'pola error' }}
                                    </span>
                                    @if ($pending->grammarRule->source === 'llm')
                                        <span class="text-[11px] text-slate-400">🤖 LLM</span>
                                    @endif
                                    <button wire:click="viewDetail({{ $pending->grammarRule->id }})" class="text-indigo-600 hover:underline">Detil</button>
                                </div>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-amber-100 text-amber-700' => $pending->status->value === 'PENDING', 'bg-green-100 text-green-700' => $pending->status->value === 'APPROVED', 'bg-red-100 text-red-700' => $pending->status->value === 'REJECTED'])>
                                {{ $pending->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                            @if ($pending->status->value === 'PENDING')
                                <button wire:click="approve({{ $pending->id }})" class="text-green-600 hover:underline">Setujui</button>
                                <button wire:click="reject({{ $pending->id }})" wire:confirm="Tolak usulan ini?" class="text-red-500 hover:underline">Tolak</button>
                            @else
                                <button wire:click="reopen({{ $pending->id }})" class="text-indigo-600 hover:underline">Buka lagi</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-500">Tidak ada usulan pada filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pendingList->links() }}
    </div>

    @if ($previewSuggestion)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="cancelPreview">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">Pratinjau Rule dari LLM</h3>
                    <button wire:click="cancelPreview" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                            <p class="text-xs uppercase text-slate-400 mb-1">Kategori</p>
                            <p class="text-sm font-medium">{{ $previewSuggestion['category'] }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                            <p class="text-xs uppercase text-slate-400 mb-1">Level</p>
                            <p class="text-sm font-medium">{{ $previewSuggestion['cefr_level'] }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                            <p class="text-xs uppercase text-slate-400 mb-1">Tipe</p>
                            <p class="text-sm font-medium">
                                <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-red-100 text-red-700' => $previewSuggestion['rule_type'] === 'error', 'bg-emerald-100 text-emerald-700' => $previewSuggestion['rule_type'] === 'positive'])>
                                    {{ $previewSuggestion['rule_type'] === 'positive' ? 'Pola benar' : 'Pola error' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs uppercase text-slate-400 mb-1">Pola Regex</p>
                        <code class="block bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 font-mono text-sm text-slate-800 break-all">{{ $previewSuggestion['regex_pattern'] }}</code>
                    </div>

                    <div>
                        <p class="text-xs uppercase text-slate-400 mb-1">Deskripsi</p>
                        <p class="text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">{{ $previewSuggestion['description'] }}</p>
                    </div>

                    @if (!empty($previewSuggestion['correct_sentence']))
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                            <p class="text-xs uppercase text-emerald-600 mb-1">Kalimat Benar (saran LLM)</p>
                            <p class="text-sm font-medium text-emerald-800">{{ $previewSuggestion['correct_sentence'] }}</p>
                        </div>
                    @endif

                    <p class="text-xs text-slate-500">
                        Rule akan disimpan ke Grammar Rules dengan kode
                        <code class="font-mono text-indigo-700">USER_{{ str_pad((string) $previewPendingId, 3, '0', STR_PAD_LEFT) }}</code>
                        dan status usulan menjadi <b>Disetujui</b>.
                    </p>

                    <div class="flex justify-end gap-2 pt-2">
                        <button wire:click="cancelPreview" class="px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                        <button wire:click="saveRule" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Simpan Rule</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($detailRule)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeDetail">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">Detil Grammar Rule — {{ $detailRule->rule_code }} <span class="text-sm text-slate-400">(#{{ $detailRule->id }})</span></h3>
                    <button wire:click="closeDetail" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                            <p class="text-xs uppercase text-slate-400 mb-1">Kategori</p>
                            <p class="text-sm font-medium">{{ $detailRule->category }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                            <p class="text-xs uppercase text-slate-400 mb-1">Level</p>
                            <p class="text-sm font-medium">{{ $detailRule->cefr_level->value }}</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">
                            <p class="text-xs uppercase text-slate-400 mb-1">Tipe</p>
                            <p class="text-sm font-medium">
                                <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-red-100 text-red-700' => $detailRule->rule_type === 'error', 'bg-emerald-100 text-emerald-700' => $detailRule->rule_type === 'positive'])>
                                    {{ $detailRule->rule_type === 'positive' ? 'Pola benar' : 'Pola error' }}
                                </span>
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs uppercase text-slate-400 mb-1">Pola Regex</p>
                        <code class="block bg-slate-100 border border-slate-200 rounded-lg px-3 py-2 font-mono text-sm text-slate-800 break-all">{{ $detailRule->regex_pattern }}</code>
                    </div>

                    <div>
                        <p class="text-xs uppercase text-slate-400 mb-1">Deskripsi</p>
                        <p class="text-sm text-slate-700 bg-slate-50 border border-slate-200 rounded-lg px-3 py-2">{{ $detailRule->description }}</p>
                    </div>

                    @if ($detailCorrectSentence)
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">
                            <p class="text-xs uppercase text-emerald-600 mb-1">Kalimat Benar (saran LLM)</p>
                            <p class="text-sm font-medium text-emerald-800">{{ $detailCorrectSentence }}</p>
                        </div>
                    @endif

                    @if ($detailRule->source === 'llm')
                        <div>
                            <p class="text-xs uppercase text-slate-400 mb-1">Meta LLM</p>
                            <pre class="bg-slate-900 text-emerald-200 rounded-lg px-3 py-2 text-xs overflow-x-auto">{{ json_encode($detailRule->llm_meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    @endif

                    <div class="flex justify-end gap-2 pt-2">
                        <button wire:click="closeDetail" class="px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Tutup</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>