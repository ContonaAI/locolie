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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
    $pin = '<svg style="height:0.92em;width:auto;display:inline-block;vertical-align:-0.12em;margin:0 -0.012em;filter:drop-shadow(0 1px 3px rgba(5,150,105,.4))" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>';
    $wordmark = '<span class="wordmark text-ink">L'.$pin.'colie</span>';
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
        class="glass mx-auto flex max-w-7xl items-center justify-between gap-2 rounded-full border border-white/60 px-2.5 py-2 transition-all duration-300 sm:px-3">
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

            {{-- Categories mega-menu (parents + sub-categories + a featured local shop) --}}
            @php
                $navParents = \App\Models\Category::whereNull('parent_id')
                    ->with(['children' => fn ($q) => $q->orderBy('sort')])
                    ->orderBy('sort')->get();
                $navFeatured = \App\Models\Business::live()->where('featured', true)->whereNotNull('photos')
                        ->with(['category', 'activeOffers'])->inRandomOrder()->first()
                    ?? \App\Models\Business::live()->whereNotNull('photos')
                        ->with(['category', 'activeOffers'])->inRandomOrder()->first();
                $navFeaturedOffer = $navFeatured?->activeOffers->first();
            @endphp
            <div x-data="{ o: false }" @mouseenter="o=true" @mouseleave="o=false" class="relative">
                <button @click="o=!o" class="group flex items-center gap-1.5 rounded-full px-3.5 py-2 text-sm font-medium text-muted transition hover:bg-black/[0.05] hover:text-ink">
                    <svg class="h-3.5 w-3.5 text-muted/70 transition group-hover:text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
                    Categories
                    <svg class="h-3 w-3 transition" :class="o && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="o" x-cloak x-transition class="absolute left-0 top-full pt-2">
                    <div class="flex w-[740px] gap-1 rounded-2xl border border-white/60 bg-white/95 glass p-2.5 shadow-2xl">
                        <div class="grid flex-1 grid-cols-2 gap-x-2">
                            @foreach ($navParents as $p)
                                <div class="rounded-xl p-2">
                                    <a href="/category/{{ $p->slug }}" class="mb-1.5 flex items-center gap-2 text-sm font-bold text-ink transition hover:text-emerald">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-soft text-emerald">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($p->slug) !!}</svg>
                                        </span>
                                        {{ $p->name }}
                                    </a>
                                    <div class="flex flex-col gap-0.5 pl-9">
                                        @foreach ($p->children->take(4) as $c)
                                            <a href="/category/{{ $c->slug }}" class="text-xs text-muted transition hover:text-emerald">{{ $c->name }}</a>
                                        @endforeach
                                        @if ($p->children->count() > 4)
                                            <a href="/category/{{ $p->slug }}" class="text-xs font-semibold text-emerald">All {{ $p->name }} &rarr;</a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if ($navFeatured)
                            <div class="w-[210px] shrink-0 rounded-xl border-l border-hair pl-3">
                                <div class="mb-2 px-1 text-[11px] font-bold uppercase tracking-wider text-muted">Featured local</div>
                                <a href="/shop/{{ $navFeatured->slug }}" class="group block overflow-hidden rounded-xl border border-hair transition hover:border-emerald">
                                    <div class="h-28 bg-cover bg-center" style="background-image:url('{{ $navFeatured->photos[0] ?? '' }}')"></div>
                                    <div class="p-3">
                                        <div class="truncate text-sm font-bold text-ink group-hover:text-emerald">{{ $navFeatured->name }}</div>
                                        <div class="truncate text-xs text-muted">{{ $navFeatured->category?->name }} · {{ $navFeatured->city ?? 'Newcastle' }}</div>
                                        @if ($navFeaturedOffer)
                                            <div class="mt-2 inline-block rounded-lg bg-emerald-soft px-2 py-1 text-[11px] font-bold text-emerald">{{ $navFeaturedOffer->badge }}</div>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endif
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
            <a href="/business/join" class="whitespace-nowrap rounded-full px-3 py-2 text-sm font-semibold text-ink transition hover:bg-black/[0.05]">Business login</a>
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
            {{-- Categories in mobile (grouped by parent) --}}
            <div class="mt-1 border-t border-hair pt-2">
                <div class="px-3 pb-1 text-xs font-semibold uppercase tracking-wider text-muted">Categories</div>
                @foreach ($navParents as $p)
                    <a href="/category/{{ $p->slug }}" @click="open=false" class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-ink hover:bg-black/[0.05]">
                        <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($p->slug) !!}</svg>
                        {{ $p->name }}
                    </a>
                    <div class="flex flex-wrap gap-1 px-3 pb-2 pl-10">
                        @foreach ($p->children as $c)
                            <a href="/category/{{ $c->slug }}" @click="open=false" class="rounded-full bg-black/[0.04] px-2.5 py-1 text-xs text-muted hover:text-emerald">{{ $c->name }}</a>
                        @endforeach
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex flex-col gap-2 border-t border-hair pt-3">
                <a href="/business/join" class="rounded-full border border-hair px-4 py-2.5 text-center text-sm font-semibold text-ink">Business login</a>
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
    <div class="mx-auto max-w-7xl px-5 py-14 sm:px-6">
        <div class="grid gap-10 md:grid-cols-[1.5fr_1fr_1fr_1fr]">
            <div>
                <a href="/" class="text-xl">{!! $wordmark !!}</a>
                <p class="mt-3 max-w-xs text-sm leading-relaxed text-muted">Bringing back the indies. Discover real discounts from the independent shops, pubs and makers on your high street.</p>
                <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-emerald-soft px-3 py-1.5 text-xs font-semibold text-emerald">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>
                    Newcastle NE1 · indies only, always
                </p>
                <div class="mt-6">
                    <x-seal variant="light" class="h-24 w-24" />
                </div>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted">Product</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/#how" class="text-ink/80 transition hover:text-emerald">How it works</a></li>
                    <li><a href="/#demo" class="text-ink/80 transition hover:text-emerald">Live demo</a></li>
                    <li><a href="/app" class="text-ink/80 transition hover:text-emerald">Launch web app</a></li>
                    <li><a href="{{ route('customer.report.entry') }}" class="text-ink/80 transition hover:text-emerald">Your savings</a></li>
                    <li><a href="{{ route('seo.index') }}" class="text-ink/80 transition hover:text-emerald">Local directory</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted">Business</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/for-business" class="text-ink/80 transition hover:text-emerald">For business</a></li>
                    <li><a href="/#pricing" class="text-ink/80 transition hover:text-emerald">Pricing</a></li>
                    <li><a href="/business/join" class="text-ink/80 transition hover:text-emerald">Business login</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-muted">Company</h4>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="/#founders" class="text-ink/80 transition hover:text-emerald">Founders</a></li>
                    <li><a href="/#download" class="text-ink/80 transition hover:text-emerald">Get the app</a></li>
                    <li><a href="{{ route('legal.terms') }}" class="text-ink/80 transition hover:text-emerald">Terms &amp; Conditions</a></li>
                    <li><a href="{{ route('legal.privacy') }}" class="text-ink/80 transition hover:text-emerald">Privacy Policy</a></li>
                    <li><a href="{{ route('legal.cookies') }}" class="text-ink/80 transition hover:text-emerald">Cookie Policy</a></li>
                    <li><a href="{{ route('subscriptions.preferences') }}" class="text-ink/80 transition hover:text-emerald">Email preferences</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t border-hair pt-6 text-xs text-muted sm:flex-row">
            <p>© {{ config('legal.company') }} 2026. All rights reserved.</p>
            <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
                <a href="{{ route('legal.terms') }}" class="transition hover:text-ink">Terms</a>
                <a href="{{ route('legal.privacy') }}" class="transition hover:text-ink">Privacy</a>
                <a href="{{ route('legal.cookies') }}" class="transition hover:text-ink">Cookies</a>
                <button type="button" onclick="try{localStorage.removeItem('ll_cookie_consent');location.reload();}catch(e){}" class="transition hover:text-ink">Cookie settings</button>
            </div>
        </div>
    </div>
</footer>

{{-- ===================== COOKIE CONSENT (PECR / GDPR) ===================== --}}
<div x-data="{
        show: false,
        init() { try { this.show = !localStorage.getItem('ll_cookie_consent'); } catch (e) { this.show = true; } },
        save(choice) { try { localStorage.setItem('ll_cookie_consent', JSON.stringify({ choice, at: Date.now() })); } catch (e) {} this.show = false; }
     }"
     x-show="show" x-cloak x-transition.opacity
     class="fixed inset-x-0 bottom-0 z-[60] p-3 sm:p-4">
    <div class="glass-card mx-auto flex max-w-3xl flex-col gap-4 rounded-3xl p-5 sm:flex-row sm:items-center sm:gap-5">
        <div class="flex-1">
            <p class="text-sm font-bold text-ink">We use cookies 🍪</p>
            <p class="mt-1 text-xs leading-relaxed text-muted">We use essential cookies to make locolie work, and optional ones to improve it. You can accept all, reject the optional ones, or read our <a href="{{ route('legal.cookies') }}" class="font-semibold text-emerald underline">Cookie Policy</a>.</p>
        </div>
        <div class="flex shrink-0 gap-2">
            <button @click="save('rejected')" class="rounded-full border border-hair bg-white px-4 py-2.5 text-xs font-bold text-ink transition hover:bg-black/[0.04]">Reject optional</button>
            <button @click="save('accepted')" class="rounded-full bg-ink px-5 py-2.5 text-xs font-bold text-white transition hover:bg-emerald">Accept all</button>
        </div>
    </div>
</div>

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

    // Viewport-local parallax for brand-pin watermarks: drift relative to each
    // element's own position (not absolute scrollY), so they stay put per-section.
    const pins = [...document.querySelectorAll('[data-pin-parallax]')];
    if (pins.length && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      let pticking = false;
      const movePins = () => {
        if (pticking) return; pticking = true;
        requestAnimationFrame(() => {
          const vh = window.innerHeight;
          pins.forEach((p) => {
            const r = p.getBoundingClientRect();
            const off = ((r.top + r.height / 2) - vh / 2) * parseFloat(p.dataset.pinParallax);
            p.style.transform = `translate3d(0, ${off.toFixed(1)}px, 0)`;
          });
          pticking = false;
        });
      };
      window.addEventListener('scroll', movePins, { passive: true });
      window.addEventListener('resize', movePins, { passive: true });
      movePins();
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
