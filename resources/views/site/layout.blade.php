<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'locolie') - Back your high street. Discover the indies near you</title>
    <meta name="description" content="@yield('meta_description', 'locolie helps you discover real discounts from independent shops near you - and helps the indies fight back against the chains. Free listings for businesses, priority placement from £19/mo. Launching in Newcastle NE1.')">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta name="theme-color" content="#0a0a0a">
    <meta property="og:site_name" content="locolie">
    <meta property="og:title" content="@yield('title', 'locolie')">
    <meta property="og:description" content="Back your high street. Discover real discounts from the independents near you. Launching in Newcastle NE1.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'locolie')">
    <meta name="twitter:description" content="Back your high street. Discover real discounts from the independents near you.">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "Organization",
      "name": "locolie",
      "description": "Discover real discounts from independent local businesses near you, and help bring back the indies. Launching in Newcastle NE1.",
      "url": "{{ url('/') }}",
      "areaServed": "Newcastle upon Tyne, NE1",
      "founders": [{"@@type":"Person","name":"Tom"},{"@@type":"Person","name":"Joe"},{"@@type":"Person","name":"Roddy"}]
    }
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
                    colors: {
                        ink: '#0a0a0a',
                        emerald: { DEFAULT: '#059669', soft: '#d1fae5' },
                        muted: '#737373',
                        hair: '#e5e5e5',
                    },
                    borderRadius: { card: '18px' },
                },
            },
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root { --emerald: #059669; --emerald-soft: #d1fae5; --ink:#0a0a0a; }
        html, body { font-family: 'Inter', system-ui, sans-serif; }
        body { color: #0a0a0a; background: #ffffff; -webkit-font-smoothing: antialiased; overflow-x: hidden; }
        ::selection { background: #05966933; }
        [x-cloak] { display: none !important; }
        .wordmark { font-weight: 800; letter-spacing: -0.03em; display: inline-flex; align-items: center; line-height: 1; }
        .wordmark svg { display: inline-block; }
        .text-balance { text-wrap: balance; }

        /* ---- Glassmorphism ---- */
        .glass { backdrop-filter: saturate(180%) blur(16px); -webkit-backdrop-filter: saturate(180%) blur(16px); }
        .glass-card {
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(20px) saturate(160%);
            -webkit-backdrop-filter: blur(20px) saturate(160%);
            border: 1px solid rgba(255,255,255,0.7);
            box-shadow: 0 18px 50px -18px rgba(0,0,0,0.22), inset 0 1px 0 rgba(255,255,255,0.6);
        }
        .glass-dark {
            background: rgba(255,255,255,0.06);
            backdrop-filter: blur(16px) saturate(140%);
            -webkit-backdrop-filter: blur(16px) saturate(140%);
            border: 1px solid rgba(255,255,255,0.12);
        }

        /* ---- Backgrounds ---- */
        .hero-grid { background-image: radial-gradient(#0000000a 1px, transparent 1px); background-size: 22px 22px; }
        .mesh { position: absolute; inset: -10% -5% auto; height: 720px; pointer-events: none; z-index: 0; filter: blur(60px); opacity: .8; }
        .mesh i { position: absolute; display: block; border-radius: 50%; }
        .mesh .b1 { width: 520px; height: 520px; left: 8%; top: 0; background: radial-gradient(circle at 30% 30%, #6ee7b7, transparent 70%); }
        .mesh .b2 { width: 480px; height: 480px; right: 6%; top: 4%; background: radial-gradient(circle at 60% 40%, #a7f3d0, transparent 70%); }
        .mesh .b3 { width: 420px; height: 420px; left: 38%; top: 18%; background: radial-gradient(circle at 50% 50%, #bbf7d0, transparent 70%); }
        .gradient-text { background: linear-gradient(120deg, #059669, #10b981 40%, #047857); -webkit-background-clip: text; background-clip: text; color: transparent; }

        /* ---- Animations ---- */
        @keyframes floaty { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes floaty2 { 0%,100% { transform: translateY(0) rotate(0); } 50% { transform: translateY(-16px) rotate(2deg); } }
        @keyframes gradientShift { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        .animate-floaty { animation: floaty 6s ease-in-out infinite; }
        .animate-floaty2 { animation: floaty2 7s ease-in-out infinite; }
        .animate-gradient { background-size: 220% 220%; animation: gradientShift 9s ease infinite; }

        /* ---- Scroll reveal (only hides when JS is present, so no-JS still shows everything) ---- */
        .has-js .reveal { opacity: 0; transform: translateY(28px); transition: opacity .8s cubic-bezier(.16,.8,.3,1), transform .8s cubic-bezier(.16,.8,.3,1); }
        .has-js .reveal.in { opacity: 1; transform: none; }
        .reveal[data-d="1"] { transition-delay: .08s; } .reveal[data-d="2"] { transition-delay: .16s; }
        .reveal[data-d="3"] { transition-delay: .24s; } .reveal[data-d="4"] { transition-delay: .32s; }
        @media (prefers-reduced-motion: reduce) { .has-js .reveal { opacity:1 !important; transform:none !important; } .animate-floaty,.animate-floaty2 { animation: none; } }

        .parallax { will-change: transform; }
        .card-hover { transition: transform .3s cubic-bezier(.16,.8,.3,1), box-shadow .3s; }
        .card-hover:hover { transform: translateY(-6px); box-shadow: 0 24px 50px -20px rgba(0,0,0,.25); }

        /* Anchored sections clear the floating nav when jumped to (UX fix). */
        section[id] { scroll-margin-top: 6rem; }
        /* Accessible keyboard focus everywhere. */
        a:focus-visible, button:focus-visible { outline: 2px solid var(--emerald); outline-offset: 3px; border-radius: 6px; }
        /* Google Translate widget: hide its banner/branding, keep our layout clean. */
        .goog-te-banner-frame, .goog-te-gadget-icon, #goog-gt-tt, .goog-te-balloon-frame { display: none !important; }
        .goog-te-gadget { height: 0; overflow: hidden; font-size: 0 !important; }
        body { top: 0 !important; }
        .skiptranslate iframe { display: none !important; }
    </style>
    @stack('head')
</head>
<body class="antialiased text-ink">
{{-- Free Google Translate (website widget) - powers the language switcher, no key/billing --}}
<div id="google_translate_element" aria-hidden="true" style="position:absolute;left:-9999px;height:0;overflow:hidden"></div>
<script>
  window.googleTranslateElementInit = function () {
    new google.translate.TranslateElement({ pageLanguage: 'en', includedLanguages: 'en,pl,es,fr,ur,zh-CN', autoDisplay: false }, 'google_translate_element');
  };
  // Switch language by setting Google's googtrans cookie, then reload.
  window.flTranslate = function (code) {
    try { localStorage.setItem('fl_lang', code); } catch (e) {}
    var map = { en: 'en', pl: 'pl', es: 'es', fr: 'fr', ur: 'ur', zh: 'zh-CN' };
    var g = map[code] || 'en';
    var host = location.hostname;
    var clear = function () {
      ['googtrans=;path=/;expires=Thu, 01 Jan 1970 00:00:00 GMT',
       'googtrans=;path=/;domain=.' + host + ';expires=Thu, 01 Jan 1970 00:00:00 GMT'].forEach(function (c) { document.cookie = c; });
    };
    clear();
    if (g !== 'en') {
      document.cookie = 'googtrans=/en/' + g + ';path=/';
      document.cookie = 'googtrans=/en/' + g + ';path=/;domain=.' + host;
    }
    location.reload();
  };
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

@php
    $pin = '<svg style="height:0.92em;width:auto;display:inline-block;vertical-align:-0.16em;margin:0 -0.015em" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58 2 4 5.58 4 10c0 5.25 8 12 8 12s8-6.75 8-12c0-4.42-3.58-8-8-8Zm0 5.5A2.5 2.5 0 1 0 12 12.5 2.5 2.5 0 0 0 12 7.5Z"/></svg>';
    $wordmark = '<span class="wordmark lowercase text-ink">l'.$pin.'c'.$pin.'lie</span>';
    // Languages we plan to support (English live; rest scaffolded for translation).
    $languages = ['en' => '🇬🇧 English', 'pl' => '🇵🇱 Polski', 'es' => '🇪🇸 Español', 'fr' => '🇫🇷 Français', 'ur' => '🇵🇰 اردو', 'zh' => '🇨🇳 中文'];
    // Roll-out roadmap - a region/county is "live" or "soon".
    $regions = [
        'United Kingdom' => [
            ['Newcastle upon Tyne', 'Tyne & Wear', true],
            ['Durham', 'County Durham', false],
            ['Leeds', 'West Yorkshire', false],
            ['Manchester', 'Greater Manchester', false],
            ['London', 'Greater London', false],
            ['Edinburgh', 'Lothian', false],
        ],
        'Coming soon' => [
            ['Dublin', 'Ireland', false],
            ['Amsterdam', 'Netherlands', false],
        ],
    ];
@endphp

{{-- ===================== NAV (standalone floating glass pill) ===================== --}}
<header
    x-data="{ scrolled: false, open: false }"
    @scroll.window="scrolled = window.scrollY > 16"
    class="fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-5 sm:pt-4">
    <nav aria-label="Primary"
        :class="scrolled ? 'bg-white/85 shadow-xl shadow-black/[0.07]' : 'bg-white/55 shadow-lg shadow-black/[0.04]'"
        class="glass mx-auto flex max-w-7xl 2xl:max-w-[1500px] items-center justify-between gap-2 rounded-full border border-white/60 px-2.5 py-2 transition-all duration-300 sm:px-3">
        <a href="/" class="pl-2 text-lg sm:text-xl">{!! $wordmark !!}</a>

        {{-- Center links --}}
        <div class="hidden items-center lg:flex">
            @php
                $links = [
                    ['/#how', 'How it works'],
                    ['/for-business', 'For Business'],
                    ['/#pricing', 'Pricing'],
                ];
            @endphp
            @foreach ($links as $l)
                <a href="{{ $l[0] }}" class="whitespace-nowrap rounded-full px-3 py-2 text-sm font-medium text-muted transition hover:bg-black/[0.05] hover:text-ink">{{ $l[1] }}</a>
            @endforeach

            {{-- Categories dropdown --}}
            @php $navCategories = \App\Models\Category::orderBy('sort')->get(['name','slug']); @endphp
            <div x-data="{ o: false }" @mouseenter="o=true" @mouseleave="o=false" class="relative">
                <button @click="o=!o" class="group flex items-center gap-1.5 rounded-full px-3.5 py-2 text-sm font-medium text-muted transition hover:bg-black/[0.05] hover:text-ink">
                    <svg class="h-3.5 w-3.5 text-muted/70 transition group-hover:text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    Categories
                    <svg class="h-3 w-3 transition" :class="o && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="o" x-cloak x-transition class="absolute left-0 top-full pt-2">
                    <div class="grid w-[300px] grid-cols-2 gap-1 rounded-2xl border border-white/60 bg-white/95 glass p-2 shadow-xl">
                        @foreach ($navCategories as $c)
                            <a href="/category/{{ $c->slug }}" class="flex items-center gap-2 rounded-xl px-2.5 py-2 text-sm font-medium text-ink hover:bg-black/[0.05]">
                                <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($c->slug) !!}</svg>
                                {{ $c->name }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Right actions --}}
        <div class="hidden items-center gap-1.5 lg:flex">
            {{-- Region + language selector (globe) --}}
            <div x-data="{ o:false, lang:(localStorage.getItem('fl_lang')||'en'), place:(localStorage.getItem('fl_place')||'Newcastle') }"
                 @mouseenter="o=true" @mouseleave="o=false" class="relative">
                <button @click="o=!o" class="flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-2 text-sm font-semibold text-ink transition hover:bg-black/[0.05]" aria-label="Region and language">
                    <svg class="h-4 w-4 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20Z"/></svg>
                    <span class="hidden xl:inline" x-text="place"></span>
                </button>
                <div x-show="o" x-cloak x-transition class="absolute right-0 top-full pt-2">
                    <div class="w-[340px] rounded-2xl border border-white/60 bg-white/95 glass p-4 shadow-2xl">
                        {{-- Location --}}
                        <div class="mb-1 text-[11px] font-bold uppercase tracking-wider text-muted">Back your high street</div>
                        @foreach ($regions as $country => $places)
                            <div class="mb-2">
                                <div class="px-1 py-1 text-xs font-semibold text-ink/50">{{ $country }}</div>
                                @foreach ($places as $p)
                                    <button @click="if({{ $p[2] ? 'true' : 'false' }}){ place='{{ $p[0] }}'; localStorage.setItem('fl_place','{{ $p[0] }}'); o=false }"
                                        class="flex w-full items-center justify-between rounded-lg px-2 py-1.5 text-left text-sm {{ $p[2] ? 'hover:bg-black/[0.05]' : 'cursor-not-allowed opacity-60' }}">
                                        <span><span class="font-semibold text-ink">{{ $p[0] }}</span> <span class="text-xs text-muted">{{ $p[1] }}</span></span>
                                        @if ($p[2])
                                            <span class="rounded-full bg-emerald-soft px-2 py-0.5 text-[10px] font-bold text-emerald">Live</span>
                                        @else
                                            <span class="rounded-full bg-[#f0f0f0] px-2 py-0.5 text-[10px] font-bold text-muted">Soon</span>
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endforeach
                        {{-- Language --}}
                        <div class="mt-3 border-t border-hair pt-3">
                            <div class="mb-1 text-[11px] font-bold uppercase tracking-wider text-muted">Language</div>
                            <div class="grid grid-cols-3 gap-1">
                                @foreach ($languages as $code => $label)
                                    <button @click="flTranslate('{{ $code }}')"
                                        :class="lang==='{{ $code }}' ? 'bg-emerald-soft text-emerald font-semibold' : 'text-ink hover:bg-black/[0.05]'"
                                        class="rounded-lg px-2 py-1.5 text-left text-xs">{{ $label }}</button>
                                @endforeach
                            </div>
                            <p class="mt-2 text-[10px] text-muted">More languages as we back more high streets.</p>
                        </div>
                    </div>
                </div>
            </div>
            <a href="/business/login" class="whitespace-nowrap rounded-full px-3 py-2 text-sm font-semibold text-ink transition hover:bg-black/[0.05]">Business login</a>
            <a href="/app" class="group inline-flex items-center gap-1.5 whitespace-nowrap rounded-full bg-ink px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald">
                Launch app
                <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
            {{-- Team portal - small, subtle, spins on hover --}}
            <a href="/portal" title="Team portal" aria-label="Team portal"
               class="group flex h-8 w-8 items-center justify-center rounded-full text-muted transition hover:bg-black/[0.06] hover:text-ink">
                <svg class="h-4 w-4 transition-transform duration-500 group-hover:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
            </a>
        </div>

        <button @click="open = !open" class="lg:hidden flex h-9 w-9 items-center justify-center rounded-full text-ink hover:bg-black/[0.05]" aria-label="Menu">
            <svg x-show="!open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            <svg x-show="open" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </nav>

    {{-- Mobile menu (floating glass card) --}}
    <div x-show="open" x-cloak x-transition class="lg:hidden mx-auto mt-2 max-h-[80vh] max-w-5xl overflow-y-auto rounded-3xl border border-white/60 bg-white/95 glass p-3 shadow-xl">
        <div class="flex flex-col gap-1">
            @foreach ($links as $l)
                <a href="{{ $l[0] }}" @click="open=false" class="rounded-xl px-3 py-2.5 text-sm font-medium text-ink hover:bg-black/[0.05]">{{ $l[1] }}</a>
            @endforeach
            {{-- Region + language (mobile) --}}
            <div x-data="{ place:(localStorage.getItem('fl_place')||'Newcastle'), lang:(localStorage.getItem('fl_lang')||'en') }" class="mt-1 border-t border-hair pt-2">
                <div class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-muted">Area</div>
                <div class="grid grid-cols-2 gap-1">
                    @foreach ($regions as $country => $places)
                        @foreach ($places as $p)
                            <button @click="if({{ $p[2] ? 'true':'false' }}){ place='{{ $p[0] }}'; localStorage.setItem('fl_place','{{ $p[0] }}') }"
                                class="flex items-center justify-between rounded-xl px-3 py-2 text-left text-sm {{ $p[2] ? 'text-ink hover:bg-black/[0.05]' : 'text-muted opacity-70' }}">
                                {{ $p[0] }} @if($p[2])<span class="text-[9px] font-bold text-emerald">LIVE</span>@else<span class="text-[9px]">SOON</span>@endif
                            </button>
                        @endforeach
                    @endforeach
                </div>
                <div class="mt-2 px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-muted">Language</div>
                <div class="grid grid-cols-3 gap-1">
                    @foreach ($languages as $code => $label)
                        <button @click="flTranslate('{{ $code }}')"
                            :class="lang==='{{ $code }}' ? 'bg-emerald-soft text-emerald font-semibold' : 'text-ink hover:bg-black/[0.05]'"
                            class="rounded-lg px-2 py-1.5 text-left text-xs">{{ $label }}</button>
                    @endforeach
                </div>
            </div>
            {{-- Categories in mobile --}}
            <div class="mt-1 border-t border-hair pt-2">
                <div class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-muted">Categories</div>
                <div class="grid grid-cols-2 gap-1">
                    @foreach ($navCategories as $c)
                        <a href="/category/{{ $c->slug }}" @click="open=false" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-ink hover:bg-black/[0.05]">
                            <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($c->slug) !!}</svg>
                            {{ $c->name }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="mt-2 flex flex-col gap-2 border-t border-hair pt-3">
                <a href="/business/login" class="rounded-full border border-hair px-4 py-2.5 text-center text-sm font-semibold text-ink">Business login</a>
                <a href="/app" class="rounded-full bg-ink px-4 py-2.5 text-center text-sm font-semibold text-white">Launch app</a>
                <a href="/portal" class="flex items-center justify-center gap-1.5 rounded-full px-4 py-2.5 text-center text-sm font-medium text-muted">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                    Team portal
                </a>
            </div>
        </div>
    </div>
</header>

<main>
    @yield('content')
</main>

{{-- ===================== FOOTER ===================== --}}
<footer class="border-t border-hair bg-[#f5f5f5]">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 py-14 sm:px-6">
        <div class="grid gap-10 md:grid-cols-[1.5fr_1fr_1fr_1fr]">
            <div>
                <a href="/" class="text-xl">{!! $wordmark !!}</a>
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-muted">Bringing back the indies. Discover real discounts from the independent shops, pubs and makers on your high street.</p>
                <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-emerald-soft px-3 py-1.5 text-xs font-semibold text-emerald">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C7.58 2 4 5.58 4 10c0 5.25 8 12 8 12s8-6.75 8-12c0-4.42-3.58-8-8-8Zm0 5.5A2.5 2.5 0 1 0 12 12.5 2.5 2.5 0 0 0 12 7.5Z"/></svg>
                    Newcastle NE1 · indies only, always
                </p>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted">Product</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/#how" class="text-ink/80 transition hover:text-emerald">How it works</a></li>
                    <li><a href="/#demo" class="text-ink/80 transition hover:text-emerald">Live demo</a></li>
                    <li><a href="/app" class="text-ink/80 transition hover:text-emerald">Launch web app</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted">Business</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/for-business" class="text-ink/80 transition hover:text-emerald">For business</a></li>
                    <li><a href="/#pricing" class="text-ink/80 transition hover:text-emerald">Pricing</a></li>
                    <li><a href="/business/login" class="text-ink/80 transition hover:text-emerald">Business login</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted">Company</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/#founders" class="text-ink/80 transition hover:text-emerald">Founders</a></li>
                    <li><a href="/#download" class="text-ink/80 transition hover:text-emerald">Get the app</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-hair pt-6 text-xs text-muted sm:flex-row">
            <p>© locolie 2026. All rights reserved.</p>
            <p>Proudly built to back the UK's independents.</p>
        </div>
    </div>
</footer>

<script>
  // Dynamic location badge: reverse-geocode the visitor (free OSM, no key) and
  // recognise the town/postcode they're in. Falls back gracefully if denied.
  function geoArea(initialCount){
    return {
      label: 'Now backing the indies in Newcastle NE1',
      place: 'Newcastle',
      count: initialCount,
      live: true,
      detect(){
        if (!('geolocation' in navigator)) return;
        navigator.geolocation.getCurrentPosition(async (pos) => {
          try {
            const { latitude, longitude } = pos.coords;
            const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&zoom=14&lat=${latitude}&lon=${longitude}`, { headers: { 'Accept': 'application/json' } });
            const a = (await res.json()).address || {};
            const outward = (a.postcode || '').split(' ')[0];
            const town = a.city || a.town || a.village || a.suburb || a.county || '';
            if (!outward && !town) return;
            this.place = town || outward || this.place;

            // Match the detected town to a city we hold data for.
            const cities = window.FL_CITIES || {};
            let key = null;
            for (const k in cities) {
              if (town && (town.toLowerCase().includes(k.toLowerCase()) || k.toLowerCase().includes(town.toLowerCase()))) { key = k; break; }
            }
            const liveHere = /^NE/i.test(outward) || /newcastle/i.test(town);

            if (key) {
              this.count = cities[key].count;
              this.live = cities[key].live;
              this.label = cities[key].live
                ? `Looks like you're in ${outward || key} - we're live and backing the indies here`
                : `Looks like you're in ${key} - ${cities[key].count} independents scouted, launching soon`;
            } else {
              this.live = liveHere;
              this.label = liveHere
                ? `Looks like you're in ${outward || town} - we're live and backing the indies here`
                : `Looks like you're in ${town || outward} - coming soon to back your high street`;
            }
          } catch (e) { /* keep default */ }
        }, () => {}, { timeout: 8000, maximumAge: 600000 });
      }
    };
  }

  // Mark JS active so reveal elements start hidden (no-JS still shows everything).
  document.documentElement.classList.add('has-js');
  document.addEventListener('DOMContentLoaded', () => {
    // Scroll reveal
    const io = new IntersectionObserver((entries) => {
      entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    document.querySelectorAll('.reveal').forEach((el) => io.observe(el));

    // Parallax (data-parallax = speed factor, e.g. 0.15)
    const layers = [...document.querySelectorAll('[data-parallax]')];
    if (layers.length) {
      let ticking = false;
      const onScroll = () => {
        if (ticking) return; ticking = true;
        requestAnimationFrame(() => {
          const y = window.scrollY;
          layers.forEach((l) => { l.style.transform = `translate3d(0, ${y * parseFloat(l.dataset.parallax)}px, 0)`; });
          ticking = false;
        });
      };
      window.addEventListener('scroll', onScroll, { passive: true });
    }

    // Count-up stats
    const cu = new IntersectionObserver((entries) => {
      entries.forEach((e) => {
        if (!e.isIntersecting) return;
        const el = e.target, target = parseInt(el.dataset.count, 10); let n = 0;
        const step = Math.max(1, Math.round(target / 45));
        const tick = () => { n = Math.min(target, n + step); el.textContent = n.toLocaleString(); if (n < target) requestAnimationFrame(tick); };
        tick(); cu.unobserve(el);
      });
    }, { threshold: 0.6 });
    document.querySelectorAll('[data-count]').forEach((el) => cu.observe(el));
  });
</script>
</body>
</html>
