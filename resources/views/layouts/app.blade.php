<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'TopSpeak Admin' }} &mdash; TopSpeak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    <script>
        function sidebarToggle() {
            const sidebar = document.getElementById('app-sidebar');
            const content = document.getElementById('app-content');
            const overlay = document.getElementById('sidebar-overlay');
            const collapsed = sidebar.classList.toggle('-translate-x-full');

            if (window.innerWidth < 1024) {
                content.classList.toggle('lg:pl-64', !collapsed);
                overlay.classList.toggle('hidden', collapsed);
                document.body.classList.toggle('overflow-hidden', !collapsed);
            } else {
                content.classList.toggle('lg:pl-64', !collapsed);
            }
        }
    </script>

    <div class="flex min-h-screen">
        @include('layouts.partials.sidebar')

        <div id="sidebar-overlay" class="hidden fixed inset-0 z-20 bg-black/40 lg:hidden" onclick="sidebarToggle()"></div>

        <div id="app-content" class="flex-1 flex flex-col lg:pl-64">
            <header class="sticky top-0 z-20 bg-white border-b border-slate-200 shadow-sm">
                <div class="flex items-center justify-between px-4 sm:px-6 h-16">
                    <div class="flex items-center gap-3">
                        <button onclick="sidebarToggle()"
                                class="text-slate-500 hover:text-slate-900 text-xl leading-none" title="Buka/tutup sidebar">☰</button>
                        <h1 class="text-lg font-semibold text-slate-900">{{ $title ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="text-sm text-slate-500">
                        {{ count(\App\Enums\CefrLevel::cases()) }} CEFR Levels
                    </div>
                </div>
            </header>

            <main class="flex-1 p-4 sm:p-6">
                {{ $slot }}
            </main>

            <footer class="px-4 sm:px-6 py-4 text-xs text-slate-400 border-t border-slate-200">
                &copy; {{ date('Y') }} TopSpeak &mdash; AI Speaking Learning Engine
            </footer>
        </div>
    </div>

    @livewireScripts
</body>
</html>