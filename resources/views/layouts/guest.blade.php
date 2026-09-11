<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'TopSpeak' }} &mdash; TopSpeak</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    <main class="min-h-screen flex items-center justify-center p-4">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>