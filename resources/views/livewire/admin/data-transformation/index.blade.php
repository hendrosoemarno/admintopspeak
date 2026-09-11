<div>
    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-4 px-4 py-3 rounded-lg bg-indigo-50 border border-indigo-200 text-indigo-800 text-sm">
        Dipakai oleh <strong>Word Transformation Engine</strong> (lookup O(1) dari file JSON
        <code class="bg-indigo-100 px-1 rounded">resources/data/grammar/</code>) untuk substitusi
        infleksi/deklinasi sebelum <em>CorrectiveTextService</em> (LLM).
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-4">
        <div class="flex flex-wrap gap-1.5 p-4 border-b border-slate-200">
            @foreach ($this->tabs as $key => $tab)
                <button wire:click="setTab('{{ $key }}')"
                        @class(['px-4 py-2 rounded-lg text-sm font-semibold transition', 'bg-indigo-600 text-white shadow-sm' => $activeTab === $key, 'text-slate-600 bg-slate-100 hover:bg-slate-200' => $activeTab !== $key])>
                    {{ $tab['icon'] }} {{ $tab['label'] }} ({{ count($this->items($key)) }})
                </button>
            @endforeach
        </div>

        <div class="p-4">
            @if ($activeTab === 'pronouns')
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach (['pronouns', 'demonstratives'] as $section)
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                                <h3 class="font-bold text-slate-800">{{ $this->collections[$section]['label'] }}</h3>
                                <button wire:click="openCreate('{{ $section }}')" class="px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                                    + Tambah
                                </button>
                            </div>
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                                    <tr>
                                        @foreach ($this->collections[$section]['fields'] as $field)
                                            <th class="px-4 py-2.5">{{ $field['label'] }}</th>
                                        @endforeach
                                        <th class="px-4 py-2.5 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($this->items($section) as $index => $item)
                                        <tr class="hover:bg-slate-50">
                                            @foreach ($this->collections[$section]['fields'] as $field)
                                                <td class="px-4 py-2.5 text-slate-700">{{ $item[$field['name']] ?? '' }}</td>
                                            @endforeach
                                            <td class="px-4 py-2.5 text-right space-x-2 whitespace-nowrap">
                                                <button wire:click="openEdit({{ $index }}, '{{ $section }}')" class="text-indigo-600 hover:underline">Edit</button>
                                                <button wire:click="delete({{ $index }}, '{{ $section }}')" wire:confirm="Hapus data ini?" class="text-red-500 hover:underline">Hapus</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($this->collections[$section]['fields']) + 1 }}" class="px-4 py-8 text-center text-slate-500">
                                                Tidak ada data {{ $this->collections[$section]['label'] }}.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-slate-800">{{ $this->collections[$activeTab]['label'] }} — {{ count($this->items()) }} data</h3>
                    <button wire:click="openCreate('{{ $activeTab }}')" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                        + Tambah {{ $this->collections[$activeTab]['label'] }}
                    </button>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500">
                            <tr>
                                @foreach ($this->collections[$activeTab]['fields'] as $field)
                                    <th class="px-4 py-3">{{ $field['label'] }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($this->items() as $index => $item)
                                <tr class="hover:bg-slate-50">
                                    @foreach ($this->collections[$activeTab]['fields'] as $field)
                                        <td class="px-4 py-3 text-slate-700">{{ $item[$field['name']] ?? '' }}</td>
                                    @endforeach
                                    <td class="px-4 py-3 text-right space-x-2 whitespace-nowrap">
                                        <button wire:click="openEdit({{ $index }}, '{{ $activeTab }}')" class="text-indigo-600 hover:underline">Edit</button>
                                        <button wire:click="delete({{ $index }}, '{{ $activeTab }}')" wire:confirm="Hapus data ini?" class="text-red-500 hover:underline">Hapus</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($this->collections[$activeTab]['fields']) + 1 }}" class="px-4 py-10 text-center text-slate-500">
                                        Tidak ada data {{ $this->collections[$activeTab]['label'] }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    @if ($showForm)
        <div class="fixed inset-0 z-40 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeForm">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200">
                    <h3 class="font-bold text-slate-900">
                        {{ $editingIndex !== null ? 'Edit' : 'Tambah' }} {{ $this->collections[$formCollection]['label'] }}
                    </h3>
                    <button wire:click="closeForm" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">
                    @foreach ($this->collections[$formCollection]['fields'] as $field)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ $field['label'] }}</label>
                            @if (($field['type'] ?? 'text') === 'select')
                                <select wire:model="form.{{ $field['name'] }}" class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                                    @foreach ($this->levels as $level)
                                        <option value="{{ $level }}">{{ $level }}</option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" wire:model="form.{{ $field['name'] }}" placeholder="{{ $field['placeholder'] ?? '' }}"
                                       class="w-full rounded-lg border-slate-300 border px-3 py-2 text-sm">
                            @endif
                            @error('form.'.$field['name']) <div class="text-xs text-red-500 mt-1">{{ $message }}</div> @enderror
                        </div>
                    @endforeach

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeForm" class="px-4 py-2 rounded-lg border border-slate-300 text-sm text-slate-600 hover:bg-slate-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
