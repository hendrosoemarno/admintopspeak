<div>
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $user->name }}</h2>
                    <div class="text-sm text-slate-500">{{ $user->email }}</div>
                    <div class="mt-1 text-xs text-slate-400 font-mono">{{ $user->device_uuid }}</div>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-end sm:items-center gap-2">
                <div class="flex gap-2 order-2 sm:order-1">
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">{{ $user->current_cefr_level->label() }}</span>
                    <span @class(['px-3 py-1 rounded-full text-xs font-semibold', 'bg-slate-100 text-slate-600' => $user->subscription_status === \App\Enums\SubscriptionStatus::FREE || !$user->isPremiumActive(), 'bg-green-100 text-green-700' => $user->isPremiumActive()])>
                        @if ($user->subscription_status === \App\Enums\SubscriptionStatus::FREE)
                            Free Tier
                        @elseif ($user->activeSubscription?->plan?->name)
                            {{ $user->activeSubscription->plan->name }}
                        @else
                            Premium
                        @endif
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Kuota: {{ $user->remaining_trial_sessions }} sesi</span>
                </div>
                <div class="flex gap-2 order-1 sm:order-2">
                    <a href="{{ route('admin.users.index') }}" class="px-3 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200">← Kembali</a>
                    @unless ($user->is_admin)
                        <button wire:click="deleteUser" wire:confirm="Hapus user ini beserta seluruh datanya? Tindakan ini tidak bisa dibatalkan." class="px-3 py-1 rounded-lg text-xs font-medium bg-red-50 text-red-600 hover:bg-red-100 border border-red-200">Hapus User</button>
                    @endunless
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex gap-2 text-sm">
        @foreach ([['history', "Level History ({$user->level_histories_count})"], ['subscriptions', "Langganan ({$user->subscriptions_count})"], ['logs', "Conversation Logs ({$user->conversation_logs_count})"]] as [$key, $label])
            <button wire:click="setTab('{{ $key }}')"
                    @class(['px-4 py-2 rounded-lg font-medium', 'bg-indigo-600 text-white' => $activeTab === $key, 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' => $activeTab !== $key])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($activeTab === 'history')
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Sebelum</th>
                        <th class="px-4 py-3">Sesudah</th>
                        <th class="px-4 py-3">Skor 4-Turn</th>
                        <th class="px-4 py-3">Sesi</th>
                        <th class="px-4 py-3">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($levelHistories as $history)
                    <tr>
                        <td class="px-4 py-3 font-semibold">{{ $history->previous_level }}</td>
                        <td class="px-4 py-3 text-green-600 font-semibold">{{ $history->new_level }} ↑</td>
                        <td class="px-4 py-3 tabular-nums">{{ $history->trigger_score }} / 9</td>
                        <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $history->session_id }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $history->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada riwayat kenaikan level.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($activeTab === 'subscriptions')
        <div class="mt-4 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Mulai</th>
                        <th class="px-4 py-3">Berakhir</th>
                        <th class="px-4 py-3">Provider</th>
                        <th class="px-4 py-3">Ref</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($subscriptions as $sub)
                    <tr>
                        <td class="px-4 py-3">
                            <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-slate-100 text-slate-600' => !$sub->isActivePremium(), 'bg-green-100 text-green-700' => $sub->isActivePremium()])>
                                {{ $sub->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{ $sub->started_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $sub->expires_at?->format('d M Y') ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $sub->payment_provider ?? '—' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $sub->payment_ref ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data langganan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    @endif

    @if ($activeTab === 'logs')
        <div class="mt-4 space-y-3">
            @forelse ($conversationLogs as $log)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100">Turn {{ $log->turn_number }}</span>
                            <span @class(['px-2 py-0.5 rounded-full font-semibold', 'bg-red-50 text-red-600' => $log->has_error, 'bg-green-50 text-green-600' => !$log->has_error])>
                                {{ $log->has_error ? 'Koreksi' : 'Normal' }}
                            </span>
                            <span class="font-mono">{{ $log->session_id }}</span>
                        </div>
                        <span>Skor {{ $log->total_turn_score }}/3 · {{ $log->created_at->diffForHumans() }}</span>
                    </div>
                    @if ($log->question)
                        <div class="mt-2 text-sm text-slate-600"><strong>Soal:</strong> {{ $log->question->question_text }}</div>
                    @endif
                    <p class="mt-1 text-sm text-slate-800 italic">"{{ $log->user_response_text }}"</p>
                    @if ($log->has_error && $log->correct_way_text)
                        <div class="mt-2 text-sm bg-amber-50 border border-amber-200 rounded-lg p-2">
                            <strong class="text-amber-800">Perbaikan:</strong>
                            <span class="text-amber-700">{{ $log->user_said_text }}</span>
                            <span class="text-slate-400">→</span>
                            <span class="text-green-700">{{ $log->correct_way_text }}</span>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center text-slate-500">
                    Belum ada log percakapan. <a href="{{ route('admin.conversation-logs.index') }}" class="text-indigo-600 hover:underline">Lihat semua log →</a>
                </div>
            @endforelse
        </div>
    @endif
</div>