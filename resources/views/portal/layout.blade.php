<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/icon.svg">
    <title>@yield('title', 'locolie') - locolie Portal</title>
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <style>
        html { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
        body.gl-bg {
            background:
                radial-gradient(60rem 60rem at 110% -10%, rgba(5,150,105,.08), transparent 60%),
                radial-gradient(50rem 50rem at -10% 0%, rgba(15,45,56,.06), transparent 55%),
                #f8fafc;
        }
        .gl-glass { background: rgba(255,255,255,.72); backdrop-filter: saturate(180%) blur(14px); -webkit-backdrop-filter: saturate(180%) blur(14px); }
        ::selection { background: rgba(5,150,105,.18); }
        .mono { font-family: 'JetBrains Mono', monospace; }
        [x-cloak] { display:none !important; }

        /* Animated map-pin "o"s in the locolie wordmark */
        .gl-logo .gl-pin {
            transform-origin: 50% 92%;
            animation: gl-pin-bob 3.2s ease-in-out infinite;
            will-change: transform;
            filter: drop-shadow(0 1px 2px rgba(5,150,105,.35));
            transition: filter .25s ease;
        }
        .gl-logo .gl-pin:nth-of-type(2) { animation-delay: .55s; }
        .gl-logo:hover .gl-pin { animation: gl-pin-hop .6s ease-in-out; }
        .gl-logo:hover .gl-pin:nth-of-type(2) { animation-delay: .08s; }
        .gl-logo:hover .gl-pin { filter: drop-shadow(0 3px 5px rgba(5,150,105,.5)); }
        @keyframes gl-pin-bob {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            45%      { transform: translateY(-0.16em) rotate(-4deg); }
            70%      { transform: translateY(-0.03em) rotate(2deg); }
        }
        @keyframes gl-pin-hop {
            0%   { transform: translateY(0) scale(1); }
            35%  { transform: translateY(-0.32em) scale(1.06); }
            65%  { transform: translateY(0.02em) scale(.97); }
            100% { transform: translateY(0) scale(1); }
        }
        @media (prefers-reduced-motion: reduce) {
            .gl-logo .gl-pin, .gl-logo:hover .gl-pin { animation: none; }
        }
    </style>
    @stack('head')
    {{-- Custom head scripts (analytics / pixels), managed in admin Settings. --}}
    {!! \App\Support\HeadScripts::head() !!}
</head>
<body class="gl-bg h-full text-slate-800 antialiased @yield('bodyClass')">
    @if (session('portal_authed'))
    @php
        $primary = [
            ['route' => 'portal.home', 'label' => 'Home'],
            ['route' => 'portal.plan', 'label' => 'Business Plan'],
        ];
        $designChildren = [
            ['route' => 'portal.brand', 'label' => 'Logos & Names', 'hash' => ''],
            ['route' => 'portal.brand', 'label' => 'Style Directions', 'hash' => '#styles'],
            ['route' => 'portal.design', 'label' => 'App Screens', 'hash' => ''],
        ];
        $trailing = [
            ['route' => 'portal.admin', 'label' => 'Admin'],
            ['route' => 'messaging.studio', 'label' => 'Messaging'],
            ['route' => 'social.calendar', 'label' => 'Social', 'match' => 'social.*'],
            ['route' => 'portal.reports', 'label' => 'Reports'],
            ['route' => 'portal.setup', 'label' => 'Setup'],
            ['route' => 'portal.settings', 'label' => 'Settings'],
            ['route' => 'portal.mockups', 'label' => 'Mockups'],
            ['route' => 'portal.ideas', 'label' => 'Ideas'],
        ];
        $designActive = request()->routeIs('portal.brand') || request()->routeIs('portal.design');
    @endphp
    <header class="gl-glass border-b border-white/60 shadow-[0_1px_0_rgba(0,0,0,.02)] sticky top-0 z-30" x-data="{ mobile:false, design:false }">
        <div class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 h-16 flex items-center gap-3">
            @php $ppin = '<svg class="gl-pin" style="height:0.78em;width:auto;display:inline-block;vertical-align:-0.04em;margin:0 -0.01em" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>'; @endphp
            <a href="{{ route('portal.home') }}" class="gl-logo font-extrabold lowercase text-lg tracking-tight text-slate-900 shrink-0 inline-flex items-center">l{!! $ppin !!}c{!! $ppin !!}lie</a>

            {{-- Desktop nav --}}
            <nav class="hidden md:flex items-center gap-1 text-sm flex-1">
                @foreach ($primary as $item)
                    <a href="{{ route($item['route']) }}"
                       class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs($item['route']) ? 'font-semibold text-emerald-700 bg-emerald-50' : 'text-slate-600 hover:bg-slate-100' }}">{{ $item['label'] }}</a>
                @endforeach

                {{-- Design dropdown --}}
                <div class="relative" @mouseenter="design=true" @mouseleave="design=false">
                    <button @click="design=!design"
                        class="px-3 py-1.5 rounded-lg transition flex items-center gap-1 {{ $designActive ? 'font-semibold text-emerald-700 bg-emerald-50' : 'text-slate-600 hover:bg-slate-100' }}">
                        Design
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                    </button>
                    <div x-show="design" x-transition x-cloak
                         class="absolute left-0 top-full pt-2 w-56">
                        <div class="rounded-xl bg-white shadow-xl ring-1 ring-slate-900/5 p-1.5">
                            @foreach ($designChildren as $c)
                                <a href="{{ route($c['route']).$c['hash'] }}"
                                   class="block px-3 py-2 rounded-lg text-sm text-slate-600 hover:bg-slate-50 hover:text-emerald-700">{{ $c['label'] }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>

                @foreach ($trailing as $item)
                    <a href="{{ route($item['route']) }}"
                       class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs($item['match'] ?? $item['route']) ? 'font-semibold text-emerald-700 bg-emerald-50' : 'text-slate-600 hover:bg-slate-100' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex-1 md:hidden"></div>

            <form method="POST" action="{{ route('portal.logout') }}" class="shrink-0 hidden md:block">
                @csrf
                <button class="px-3 py-1.5 rounded-lg text-sm text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition">Log out</button>
            </form>

            {{-- Mobile hamburger --}}
            <button @click="mobile=!mobile" class="md:hidden shrink-0 p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                <svg x-show="!mobile" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                <svg x-show="mobile" x-cloak class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        {{-- Mobile menu --}}
        <div x-show="mobile" x-transition x-cloak class="md:hidden border-t border-slate-200/70 bg-white/95 backdrop-blur px-4 py-3">
            @foreach ($primary as $item)
                <a href="{{ route($item['route']) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs($item['route']) ? 'font-semibold text-emerald-700 bg-emerald-50' : 'text-slate-700' }}">{{ $item['label'] }}</a>
            @endforeach
            <div class="px-3 pt-3 pb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Design</div>
            @foreach ($designChildren as $c)
                <a href="{{ route($c['route']).$c['hash'] }}" class="block px-5 py-2 rounded-lg text-slate-600">{{ $c['label'] }}</a>
            @endforeach
            <div class="border-t border-slate-100 my-2"></div>
            @foreach ($trailing as $item)
                <a href="{{ route($item['route']) }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs($item['match'] ?? $item['route']) ? 'font-semibold text-emerald-700 bg-emerald-50' : 'text-slate-700' }}">{{ $item['label'] }}</a>
            @endforeach
            <form method="POST" action="{{ route('portal.logout') }}" class="px-3 pt-2">
                @csrf
                <button class="text-sm text-slate-500">Log out</button>
            </form>
        </div>
    </header>
    @endif

    <main class="@yield('mainClass', 'mx-auto w-full max-w-[1400px] px-4 sm:px-6 py-6 sm:py-10')">
        @if (session('status'))
            <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm shadow-sm">
                {{ session('status') }}
            </div>
        @endif
        @yield('content')
    </main>

    @if (session('portal_authed'))
    <footer class="mx-auto w-full max-w-[1400px] px-4 sm:px-6 py-10 text-xs text-slate-400">
        locolie Portal - private working space. Not for public distribution.
    </footer>
    @endif
</body>
</html>
