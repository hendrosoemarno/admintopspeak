<div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <div class="sm:col-span-6">
                <input type="search" wire:model.live.debounce.300ms="search" placeholder="Cari teks, user, atau session…"
                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
            </div>
            <div class="sm:col-span-2">
                <select wire:model.live="stateFilter" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                    <option value="">Semua State</option>
                    @foreach (\App\Enums\SessionStepState::cases() as $state)
                        <option value="{{ $state->value }}">{{ $state->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="flex items-center gap-2 text-sm text-slate-600 px-2 py-2">
                    <input type="checkbox" wire:model.live="errorOnly" class="rounded border-slate-300">
                    Hanya koreksi
                </label>
            </div>
            <div class="sm:col-span-2 text-right">
                <button wire:click="clearFilters" class="text-xs text-slate-500 hover:text-indigo-600">Bersihkan filter</button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Sesi / Turn</th>
                    <th class="px-4 py-3">Soal</th>
                    <th class="px-4 py-3">Transkrip</th>
                    <th class="px-4 py-3 text-center">Skor</th>
                    <th class="px-4 py-3 text-center">State</th>
                    <th class="px-4 py-3 text-right">Waktu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($logs as $log)
                    <tr class="{{ $log->has_error ? 'bg-red-50/40' : 'hover:bg-slate-50' }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.users.show', $log->user) }}" class="font-medium text-indigo-600 hover:underline">{{ $log->user?->name }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-mono text-xs text-slate-500">{{ \Illuminate\Support\Str::limit($log->session_id, 10, '…') }}</div>
                            <div class="text-xs text-slate-400">Turn {{ $log->turn_number }}</div>
                        </td>
                        <td class="px-4 py-3 max-w-[220px]">
                            <span class="text-slate-700">{{ \Illuminate\Support\Str::limit($log->question?->question_text, 60) ?? '—' }}</span>
                        </td>
                        <td class="px-4 py-3 max-w-[240px] text-slate-600 italic truncate" title="{{ $log->user_response_text }}">
                            "{{ \Illuminate\Support\Str::limit($log->user_response_text, 60) }}"
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-green-100 text-green-700' => $log->total_turn_score >= 2, 'bg-amber-100 text-amber-700' => $log->total_turn_score === 1, 'bg-red-100 text-red-700' => $log->total_turn_score === 0])>
                                {{ $log->total_turn_score }}/3
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($log->has_error)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Koreksi</span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600">Normal</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-slate-500">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-10 text-center text-slate-500">Tidak ada log percakapan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>