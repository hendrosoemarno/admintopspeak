<div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ($stats as $stat)
            <a href="{{ route($stat['route']) }}" class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-3xl font-bold text-slate-900">{{ $stat['value'] }}</div>
                        <div class="text-sm text-slate-500 mt-1">{{ $stat['label'] }}</div>
                    </div>
                    <div class="text-3xl">{{ $stat['icon'] }}</div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Distribusi Level CEFR</h2>
            @if ($levelDistribution->isEmpty())
                <p class="text-sm text-slate-500">Belum ada data pengguna.</p>
            @else
                <div class="space-y-2">
                    @foreach ($levelDistribution as $row)
                        <div class="flex items-center gap-3">
                            <span class="w-10 text-sm font-semibold text-slate-700">{{ $row->current_cefr_level }}</span>
                            <div class="flex-1 h-2.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $row->total / max($levelDistribution->max('total'), 1) * 100 }}%"></div>
                            </div>
                            <span class="w-8 text-right text-sm text-slate-500">{{ $row->total }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-900 mb-4">Pengguna Teraktif</h2>
            @if ($topUsers->isEmpty())
                <p class="text-sm text-slate-500">Belum ada pengguna.</p>
            @else
                <ul class="divide-y divide-slate-100">
                    @foreach ($topUsers as $user)
                        <li class="py-2.5 flex items-center justify-between">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-sm font-medium text-indigo-600 hover:underline">
                                {{ $user->name }}
                            </a>
                            <span class="text-xs text-slate-500">{{ $user->conversation_logs_count }} log</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mt-6">
        <h2 class="font-semibold text-slate-900 mb-3">Konfigurasi Aplikasi</h2>
        <div class="flex flex-wrap gap-4 text-sm">
            <div><span class="text-slate-500">Latest:</span> <strong>{{ $config->latest_app_version }}</strong></div>
            <div><span class="text-slate-500">Min Required:</span> <strong>{{ $config->min_required_version }}</strong></div>
            <div>
                <span class="text-slate-500">Force Update:</span>
                <span @class(['px-2 py-0.5 rounded-full text-xs font-semibold', 'bg-green-100 text-green-700' => !$config->is_force_update, 'bg-amber-100 text-amber-700' => $config->is_force_update])>
                    {{ $config->is_force_update ? 'Active' : 'Off' }}
                </span>
            </div>
            <a href="{{ route('admin.app-config.index') }}" class="text-indigo-600 hover:underline">Edit →</a>
        </div>
    </div>
</div>