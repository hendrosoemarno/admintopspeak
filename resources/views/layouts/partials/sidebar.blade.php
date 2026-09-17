<aside id="app-sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-slate-200 flex flex-col transition-transform duration-300">
    <div class="flex items-center gap-3 px-5 h-16 border-b border-slate-800">
        <div class="w-8 h-8 rounded-lg bg-indigo-500 flex items-center justify-center font-black text-white">T</div>
        <div class="flex-1 min-w-0">
            <div class="font-bold text-white leading-tight">TopSpeak</div>
            <div class="text-xs text-slate-400">Admin Dashboard</div>
        </div>
        <button onclick="sidebarToggle()"
                class="text-slate-400 hover:text-white text-xl leading-none" title="Tutup sidebar">✕</button>
    </div>

    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
        <a href="{{ route('admin.dashboard') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.dashboard'), 'hover:bg-slate-800' => !request()->routeIs('admin.dashboard')])>
            <span>◎</span> Dashboard
        </a>

        <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Master Data</div>

        <a href="{{ route('admin.users.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.users.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.users.*')])>
            <span>👥</span> Users
        </a>
        <a href="{{ route('admin.question-banks.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.question-banks.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.question-banks.*')])>
            <span>📝</span> Question Banks
        </a>
        <a href="{{ route('admin.grammar-rules.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.grammar-rules.*') || request()->routeIs('admin.pending-rules.*'), 'hover:bg-slate-800' => !(request()->routeIs('admin.grammar-rules.*') || request()->routeIs('admin.pending-rules.*'))])>
            <span>🧩</span> Grammar Rules
        </a>
        <a href="{{ route('admin.ielts-curriculum.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.ielts-curriculum.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.ielts-curriculum.*')])>
            <span>🎓</span> IELTS Curriculum
        </a>
        <a href="{{ route('admin.data-transformation.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.data-transformation.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.data-transformation.*')])>
            <span>🔄</span> Data Transformation
        </a>
        <a href="{{ route('admin.thematic-topics.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.thematic-topics.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.thematic-topics.*')])>
            <span>🎭</span> Thematic Topics
        </a>
        <a href="{{ route('admin.thematic-questions.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.thematic-questions.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.thematic-questions.*')])>
            <span>💬</span> Soal Tematik
        </a>
        <a href="{{ route('admin.vocabulary.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.vocabulary.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.vocabulary.*')])>
            <span>📚</span> Vocabulary Bank
        </a>
        <a href="{{ route('admin.filler-words.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.filler-words.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.filler-words.*')])>
            <span>🗣️</span> Filler Words
        </a>
        <a href="{{ route('admin.data-import.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.data-import.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.data-import.*')])>
            <span>📥</span> Export Import Data
        </a>
        <a href="{{ route('admin.test-chatbot.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.test-chatbot.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.test-chatbot.*')])>
            <span>🤖</span> Test Chatbot
        </a>

        <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Aktivitas</div>

        <a href="{{ route('admin.conversation-logs.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.conversation-logs.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.conversation-logs.*')])>
            <span>💬</span> Conversation Logs
        </a>

        <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Pembayaran</div>

        <a href="{{ route('admin.subscriptions.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.subscriptions.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.subscriptions.*')])>
            <span>💳</span> Subscriptions
        </a>
        <a href="{{ route('admin.payment-gateway.settings') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.payment-gateway.settings'), 'hover:bg-slate-800' => !request()->routeIs('admin.payment-gateway.settings')])>
            <span>🔌</span> Payment Gateway
        </a>
        <a href="{{ route('admin.payment-test.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.payment-test.index'), 'hover:bg-slate-800' => !request()->routeIs('admin.payment-test.index')])>
            <span>🧪</span> Payment Test
        </a>

        <div class="pt-4 pb-1 px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Sistem</div>

        <a href="{{ route('admin.app-config.index') }}" @class(['flex items-center gap-3 px-3 py-2 rounded-lg', 'bg-slate-800 text-white' => request()->routeIs('admin.app-config.*'), 'hover:bg-slate-800' => !request()->routeIs('admin.app-config.*')])>
            <span>⚙️</span> App Configuration
        </a>
    </nav>

    <div class="px-5 py-4 border-t border-slate-800 text-xs text-slate-400">
        <div class="flex items-center justify-between">
            <span>Engine v{{ \App\Models\AppConfiguration::current()->latest_app_version }}</span>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="text-slate-300 hover:text-white font-medium">Keluar</button>
            </form>
        </div>
    </div>
</aside>