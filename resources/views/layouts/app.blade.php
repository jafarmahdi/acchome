<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app_direction() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Noto Sans Arabic', 'sans-serif'],
                    },
                    colors: {
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a5f' },
                        dark: { 800:'#1a1f2e', 900:'#111827', 950:'#0d1117' },
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Arabic:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        [dir="rtl"] { font-family: 'Noto Sans Arabic', 'Inter', sans-serif; }
        [dir="ltr"] { font-family: 'Inter', 'Noto Sans Arabic', sans-serif; }
        body { background: linear-gradient(135deg, #f0f4ff 0%, #f5f3ff 50%, #fdf2f8 100%); min-height: 100vh; }
        .glass-card { background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.6); }
        .sidebar-gradient { background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%); }
        .nav-item { transition: all 0.2s ease; border-radius: 12px; margin: 2px 0; }
        .nav-item:hover { background: rgba(255,255,255,0.08); }
        .nav-item.active { background: linear-gradient(135deg, rgba(59,130,246,0.2), rgba(99,102,241,0.15)); color: #93c5fd; }
        .nav-item.active .nav-icon { color: #60a5fa; }
        .stat-card { transition: all 0.3s ease; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 40px -8px rgba(0,0,0,0.12); }
        .shimmer { background: linear-gradient(110deg, transparent 33%, rgba(255,255,255,0.4) 50%, transparent 66%); background-size: 200% 100%; }
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(148,163,184,0.3); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(148,163,184,0.5); }
        .badge-pulse { animation: pulse-ring 2s ease-out infinite; }
        @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); } 70% { box-shadow: 0 0 0 8px rgba(239,68,68,0); } 100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); } }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen antialiased" x-data="{ sidebarOpen: false, profileOpen: false }">

    <div class="flex min-h-screen">
        <!-- Mobile Overlay -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '{{ is_rtl() ? "translate-x-full" : "-translate-x-full" }}'"
               class="fixed inset-y-0 {{ is_rtl() ? 'right-0' : 'left-0' }} z-50 w-[272px] sidebar-gradient shadow-2xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto lg:z-auto flex flex-col">

            <!-- Brand -->
            <div class="flex items-center justify-between h-[72px] px-6">
                <a href="{{ url('/dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/30">
                        <i class="fas fa-wallet text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-white font-bold text-lg tracking-tight">AccHome</span>
                        <p class="text-slate-400 text-[10px] -mt-0.5 font-medium">{{ __('Finance Monitor') }}</p>
                    </div>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <!-- User Card -->
            <div class="mx-4 mb-4 p-3 rounded-2xl bg-white/5 border border-white/10">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <img src="{{ auth()->user()->avatar_url }}" alt="" class="w-11 h-11 rounded-xl object-cover ring-2 ring-blue-500/30">
                        <div class="absolute -bottom-0.5 {{ is_rtl() ? '-left-0.5' : '-right-0.5' }} w-3.5 h-3.5 bg-emerald-400 rounded-full border-2 border-slate-900"></div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                        <p class="text-slate-400 text-xs capitalize">{{ auth()->user()->role }} &bull; {{ auth()->user()->family->name ?? '' }}</p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 overflow-y-auto space-y-0.5">
                <p class="px-3 pt-2 pb-2 text-[10px] font-bold text-slate-500 uppercase tracking-[0.15em]">{{ __('Main') }}</p>

                @php
                $navItems = [
                    ['url' => '/dashboard', 'icon' => 'fas fa-house', 'label' => __('Dashboard'), 'match' => 'dashboard*', 'color' => ''],
                    ['url' => '/expenses', 'icon' => 'fas fa-receipt', 'label' => __('Expenses'), 'match' => 'expenses*', 'color' => 'text-rose-400'],
                    ['url' => '/incomes', 'icon' => 'fas fa-coins', 'label' => __('Income'), 'match' => 'incomes*', 'color' => 'text-emerald-400'],
                    ['url' => '/transfers', 'icon' => 'fas fa-right-left', 'label' => __('Transfers'), 'match' => 'transfers*', 'color' => 'text-violet-400'],
                    ['url' => '/loans', 'icon' => 'fas fa-hand-holding-dollar', 'label' => __('Loans'), 'match' => 'loans*', 'color' => 'text-orange-400'],
                    ['url' => '/alerts', 'icon' => 'fas fa-bell', 'label' => __('Alerts'), 'match' => 'alerts*', 'color' => '', 'badge' => $unreadAlerts ?? 0],
                ];
                $moreItems = [
                    ['url' => '/accounts', 'icon' => 'fas fa-layer-group', 'label' => __('Accounts'), 'match' => 'accounts*', 'color' => ''],
                    ['url' => '/budgets', 'icon' => 'fas fa-chart-pie', 'label' => __('Budgets'), 'match' => 'budgets*', 'color' => 'text-amber-400'],
                    ['url' => '/savings-goals', 'icon' => 'fas fa-piggy-bank', 'label' => __('Savings Goals'), 'match' => 'savings-goals*', 'color' => 'text-teal-400'],
                    ['url' => '/recurring-transactions', 'icon' => 'fas fa-calendar-check', 'label' => __('Recurring Transactions'), 'match' => 'recurring-transactions*', 'color' => 'text-cyan-400'],
                    ['url' => '/categories', 'icon' => 'fas fa-tags', 'label' => __('Categories'), 'match' => 'categories*', 'color' => 'text-indigo-400'],
                    ['url' => '/reports', 'icon' => 'fas fa-chart-line', 'label' => __('Reports'), 'match' => 'reports*', 'color' => ''],
                    ['url' => '/exchange-rates', 'icon' => 'fas fa-money-bill-transfer', 'label' => __('Exchange Rates'), 'match' => 'exchange-rates*', 'color' => ''],
                    ['url' => '/account-adjustments', 'icon' => 'fas fa-scale-balanced', 'label' => __('Account Adjustments'), 'match' => 'account-adjustments*', 'color' => ''],
                    ['url' => '/audit-logs', 'icon' => 'fas fa-clock-rotate-left', 'label' => __('Audit Logs'), 'match' => 'audit-logs*', 'color' => ''],
                    ['url' => '/settings', 'icon' => 'fas fa-gear', 'label' => __('Settings'), 'match' => 'settings*', 'color' => ''],
                ];
                @endphp

                @foreach($navItems as $item)
                <a href="{{ url($item['url']) }}" class="nav-item flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-300 {{ request()->is(ltrim($item['match'], '/')) ? 'active' : '' }}">
                    <i class="{{ $item['icon'] }} nav-icon w-5 text-center text-base {{ $item['color'] ?: 'text-slate-400' }}"></i>
                    <span>{{ $item['label'] }}</span>
                </a>
                @endforeach

                <div class="px-3 pt-5">
                    <details class="rounded-2xl bg-white/5 border border-white/5" {{ collect($moreItems)->contains(fn ($item) => request()->is(ltrim($item['match'], '/'))) ? 'open' : '' }}>
                        <summary class="list-none cursor-pointer px-3 py-3 flex items-center justify-between text-sm font-semibold text-slate-200">
                            <span>{{ __('More Tools') }}</span>
                            <i class="fas fa-chevron-down text-[10px] text-slate-400"></i>
                        </summary>
                        <div class="px-2 pb-2">
                            @foreach($moreItems as $item)
                            <a href="{{ url($item['url']) }}" class="nav-item flex items-center gap-3 px-3 py-2.5 text-sm font-medium text-slate-300 {{ request()->is(ltrim($item['match'], '/')) ? 'active' : '' }}">
                                <i class="{{ $item['icon'] }} nav-icon w-5 text-center text-base {{ $item['color'] ?: 'text-slate-400' }}"></i>
                                <span>{{ $item['label'] }}</span>
                            </a>
                            @endforeach
                        </div>
                    </details>
                </div>
            </nav>

            <!-- Logout -->
            <div class="p-3 border-t border-white/5">
                <form action="{{ url('/logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="nav-item flex items-center gap-3 w-full px-3 py-2.5 text-sm font-medium text-red-400 hover:bg-red-500/10">
                        <i class="fas fa-arrow-right-from-bracket w-5 text-center text-base"></i>
                        <span>{{ __('Logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Bar -->
            <header class="sticky top-0 z-20 glass-card shadow-sm">
                <div class="flex items-center justify-between h-[72px] px-4 sm:px-8">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true" class="lg:hidden w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 transition-colors">
                            <i class="fas fa-bars"></i>
                        </button>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">@yield('page-title', __('Dashboard'))</h1>
                            <p class="text-xs text-gray-400 hidden sm:block">@yield('page-subtitle', now()->format('l, F j, Y'))</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Quick Add -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="w-10 h-10 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-shadow">
                                <i class="fas fa-plus text-sm"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute {{ is_rtl() ? 'left-0' : 'right-0' }} mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                                <a href="{{ url('/expenses/create') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <span class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center"><i class="fas fa-receipt text-red-500 text-xs"></i></span>
                                    {{ __('New Expense') }}
                                </a>
                                <a href="{{ url('/incomes/create') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <span class="w-8 h-8 rounded-lg bg-green-50 flex items-center justify-center"><i class="fas fa-coins text-green-500 text-xs"></i></span>
                                    {{ __('New Income') }}
                                </a>
                                <a href="{{ url('/transfers/create') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <span class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center"><i class="fas fa-right-left text-violet-500 text-xs"></i></span>
                                    {{ __('New Transfer') }}
                                </a>
                            </div>
                        </div>

                        <!-- Language -->
                        <a href="{{ url('/language/' . (app()->getLocale() === 'en' ? 'ar' : 'en')) }}"
                           class="w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all text-xs font-bold">
                            {{ app()->getLocale() === 'en' ? 'ع' : 'EN' }}
                        </a>

                        <!-- Alerts -->
                        <a href="{{ url('/alerts') }}" class="relative w-10 h-10 rounded-xl bg-white shadow-sm border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all">
                            <i class="fas fa-bell text-sm"></i>
                            @if(isset($unreadAlerts) && $unreadAlerts > 0)
                                <span class="absolute -top-1 {{ is_rtl() ? '-left-1' : '-right-1' }} w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center badge-pulse">{{ $unreadAlerts > 9 ? '9+' : $unreadAlerts }}</span>
                            @endif
                        </a>

                        <!-- Profile -->
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="flex items-center gap-2 {{ is_rtl() ? 'pr-1 pl-3' : 'pl-1 pr-3' }} py-1 rounded-xl bg-white shadow-sm border border-gray-200 hover:border-gray-300 transition-all">
                                <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-lg object-cover">
                                <span class="text-sm font-medium text-gray-700 hidden sm:inline">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-[10px] text-gray-400"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute {{ is_rtl() ? 'left-0' : 'right-0' }} mt-2 w-52 bg-white rounded-2xl shadow-xl border border-gray-100 py-2 z-50">
                                <div class="px-4 py-2 border-b border-gray-100">
                                    <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-400">{{ auth()->user()->email }}</p>
                                </div>
                                <a href="{{ url('/profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-user-circle w-4 text-gray-400"></i>{{ __('Profile') }}
                                </a>
                                <a href="{{ url('/settings') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                    <i class="fas fa-gear w-4 text-gray-400"></i>{{ __('Settings') }}
                                </a>
                                <hr class="my-1 border-gray-100">
                                <form action="{{ url('/logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="fas fa-arrow-right-from-bracket w-4"></i>{{ __('Logout') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-5 py-4 rounded-2xl flex items-center justify-between shadow-sm">
                        <span class="flex items-center gap-3"><i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}</span>
                        <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors"><i class="fas fa-times"></i></button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show"
                         class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl flex items-center justify-between shadow-sm">
                        <span class="flex items-center gap-3"><i class="fas fa-exclamation-circle text-red-500"></i>{{ session('error') }}</span>
                        <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors"><i class="fas fa-times"></i></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl shadow-sm">
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                                <li class="flex items-center gap-2 text-sm"><i class="fas fa-circle text-[4px] text-red-400"></i>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="py-4 px-8 text-center">
                <p class="text-xs text-gray-400">&copy; {{ date('Y') }} {{ config('app.name') }}</p>
            </footer>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
