<div class="w-full max-w-md">
    <div class="bg-white rounded-xl shadow-lg border border-slate-200 p-8">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-slate-900 text-white text-2xl font-bold mb-4">T</div>
            <h1 class="text-xl font-bold text-slate-900">TopSpeak Admin</h1>
            <p class="text-sm text-slate-500 mt-1">Masuk untuk mengelola platform</p>
        </div>

        <form wire:submit="login" class="space-y-4">
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                <input wire:model="email" id="email" type="email" required
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
                <input wire:model="password" id="password" type="password" required
                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <button type="submit"
                class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-2.5 rounded-lg transition">
                Masuk
            </button>
        </form>
    </div>
</div>