@php
    // Launch-market values (config/locolie.php) - never re-hardcode city/price.
    $ll = config('locolie.launch');
    $llPlace = $ll['place'];                 // "Newcastle NE1"
    $llCity = $ll['city'];                   // "Newcastle"  (also used by the embedded _appwalk)

    // Brand pin (matches the rest of the site).
    $pinPath = 'M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z';

    // Concrete values from a real local business so the mocks feel like the product.
    $bName     = $business?->name ?? 'Your shop';
    $bCity     = $business?->city ?? $llCity;
    $bCategory = $business?->category?->name ?? 'Independent';
    $bRating   = number_format((float) ($business->rating ?? 4.8), 1);
    $bPhoto    = $business?->photos[0] ?? null;
    $bColor    = $business?->brandColor() ?? '#059669';
    $bInitials = $business?->brandInitials() ?? 'LO';
    $bLogo     = $business?->logoUrl();

    $imgOwner  = asset('images/marketing/cafe-owner.jpg');
    $imgCafe   = asset('images/marketing/indie-cafe.jpg');

    // Wordmark (white) + the shop's brand mark (real logo, else initials chip).
    $wm = fn ($cls = 'text-white') => '<span class="wordmark '.$cls.'">L<svg class="mx-[-0.02em] inline-block h-[0.9em] w-auto align-[-0.12em]" viewBox="0 0 24 24" fill="#10b981" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="'.$pinPath.'"/></svg>colie</span>';
    $brandMark = $bLogo
        ? '<img src="'.$bLogo.'" alt="'.e($bName).'" class="h-full w-full rounded-[inherit] object-cover" onerror="this.remove()">'
        : '<span class="text-[0.62em] font-extrabold leading-none text-white">'.e($bInitials).'</span>';

    $tierBlurb = ['free' => 'For getting started', 'featured' => 'Most popular', 'premium' => 'For growing shops', 'enterprise' => 'Chains & multi-site'];
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>locolie for retailers - the deck</title>
    <meta name="description" content="Why locolie, and how it works for your shop: get found, capture customers with a scan, run a free loyalty scheme, and market to your own customers. Launching in {{ $llPlace }}.">
    <meta name="robots" content="noindex">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <meta name="theme-color" content="#0a0a0a">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('head') {{-- the embedded app demo (_appwalk / _appchrome) push their styles here --}}

    <style>
        :root { --emerald: #059669; --e2:#10b981; }
        html, body { font-family: 'Inter', system-ui, sans-serif; }
        body { color:#fff; background:#000; -webkit-font-smoothing: antialiased; }
        ::selection { background:#10b98133; }
        [x-cloak] { display:none !important; }
        .wordmark { font-weight:800; letter-spacing:-0.03em; display:inline-flex; align-items:center; line-height:1; }
        .gtext { background:linear-gradient(115deg,#34d399,#10b981 45%,#059669); -webkit-background-clip:text; background-clip:text; color:transparent; }

        /* Google Slides widescreen proportions: a 16:9 stage that fits the viewport. */
        .deck-stage { aspect-ratio:16/9; width:min(95vw, calc((100vh - 132px) * 16 / 9)); }
        /* Present mode: the deck fills the whole screen. */
        .deck-screen:fullscreen { display:flex; flex-direction:column; justify-content:center; background:#000; }
        .deck-screen:fullscreen .deck-top, .deck-screen:fullscreen .deck-hint { display:none !important; }
        .deck-screen:fullscreen .deck-wrap { max-width:none !important; padding:0 !important; }
        .deck-screen:fullscreen .deck-stage { width:min(100vw, calc(100vh * 16 / 9)); max-width:none; border-radius:0; border:0; }
        .deck-screen:fullscreen .deck-nav { position:fixed; left:50%; bottom:20px; transform:translateX(-50%); width:auto; gap:1.25rem; background:rgba(0,0,0,.55); padding:.5rem .9rem; border-radius:9999px; backdrop-filter:blur(8px); opacity:0; transition:opacity .3s; }
        .deck-screen:fullscreen:hover .deck-nav { opacity:1; }
        /* Premium dark canvas: deep ink + emerald glow + faint grid, like the site hero. */
        .slide-dark { background:#070a09; }
        .glow { background:
            radial-gradient(60% 70% at 78% 12%, rgba(16,185,129,.20), transparent 60%),
            radial-gradient(55% 60% at 8% 92%, rgba(5,150,105,.16), transparent 60%),
            #070a09; }
        .grid-dots { background-image:radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px); background-size:24px 24px; }
        .glass { background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.10); backdrop-filter:blur(8px); }
        .hair { border-color:rgba(255,255,255,.10); }
        .muted2 { color:rgba(255,255,255,.62); }
        a:focus-visible, button:focus-visible { outline:2px solid var(--e2); outline-offset:3px; border-radius:6px; }

        /* Reveal + accent animations (restart when a slide is shown). */
        @keyframes dRise { 0%{opacity:0;transform:translateY(14px)} 100%{opacity:1;transform:none} }
        @keyframes dPop  { 0%{opacity:0;transform:scale(.45)} 60%{transform:scale(1.1)} 100%{opacity:1;transform:scale(1)} }
        @keyframes dPing { 0%{transform:scale(.5);opacity:.6} 100%{transform:scale(2.6);opacity:0} }
        @keyframes dFloat{ 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        @keyframes dScan { 0%{top:8%} 50%{top:84%} 100%{top:8%} }
        .r  { animation:dRise .6s cubic-bezier(.16,.8,.3,1) both; }
        .pp { animation:dPop .55s cubic-bezier(.16,.8,.3,1) both; }
        .fl { animation:dFloat 4.6s ease-in-out infinite; }
        .ping::after { content:''; position:absolute; inset:0; border-radius:9999px; border:2px solid var(--e2); animation:dPing 2.4s ease-out infinite; }
        .scanline { animation:dScan 2.6s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce){ .r,.pp,.fl,.ping::after,.scanline{ animation:none !important; } }

        /* Print: the real premium-dark slides, one per A4 landscape page. */
        @media print {
            @page { size:A4 landscape; margin:9mm; }
            html, body { background:#fff !important; }
            .no-print, .deck-screen { display:none !important; }
            .deck-print { display:block !important; }
            /* Force backgrounds + colours to print even when "Background graphics" is off. */
            html, body, .deck-print, .deck-print * { -webkit-print-color-adjust:exact !important; print-color-adjust:exact !important; }
            .print-slide { break-after:page; page-break-after:always; }
            .print-slide:last-child { break-after:auto; page-break-after:auto; }
            /* Freeze reveal animations so nothing prints mid-fade (opacity 0). */
            .deck-print .r, .deck-print .pp, .deck-print .fl { animation:none !important; opacity:1 !important; transform:none !important; }
        }
        .deck-print { display:none; }
    </style>
</head>
<body class="antialiased">

{{-- =================================================================== --}}
{{-- ON-SCREEN DECK (premium dark, 16:9, one slide visible at a time)      --}}
{{-- =================================================================== --}}
<div class="deck-screen no-print min-h-screen bg-black text-white"
     x-data="{ slide:1, total:13,
        next(){ if(this.slide<this.total) this.slide++ },
        prev(){ if(this.slide>1) this.slide-- },
        go(n){ this.slide=n },
        present(){ const el=this.$root; if(document.fullscreenElement){ document.exitFullscreen() } else { (el.requestFullscreen||el.webkitRequestFullscreen||el.msRequestFullscreen)?.call(el) } } }"
     @keydown.window.arrow-right="next()" @keydown.window.arrow-left="prev()" @keydown.window.space.prevent="next()">

    {{-- Top bar --}}
    <header class="deck-top mx-auto flex max-w-[1120px] items-center justify-between px-4 py-3 sm:px-2">
        <a href="/business" class="text-lg">{!! $wm() !!}</a>
        <div class="flex items-center gap-2 sm:gap-3">
            <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/80">Slide <span x-text="slide"></span> / <span x-text="total"></span></span>
            <button type="button" @click="present()" class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-3.5 py-1.5 text-xs font-bold text-white transition hover:bg-white/10">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3M21 8V5a2 2 0 0 0-2-2h-3M3 16v3a2 2 0 0 0 2 2h3M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                Present
            </button>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 rounded-full bg-white px-3.5 py-1.5 text-xs font-bold text-[#0a0a0a] transition hover:bg-emerald-500 hover:text-white">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print / Save as PDF
            </button>
        </div>
    </header>

    {{-- Stage --}}
    <div class="deck-wrap mx-auto flex max-w-[1120px] flex-col items-center px-4 pb-5 sm:px-2">
        <div class="deck-stage relative overflow-hidden rounded-3xl border hair shadow-2xl">

            {{-- ===== 1 · COVER ===== --}}
            <section x-show="slide===1" x-transition.opacity.duration.400ms class="glow grid-dots absolute inset-0">
                <div class="absolute inset-0 grid grid-cols-1 md:grid-cols-[1.15fr_1fr]">
                    <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                        <div class="text-2xl">{!! $wm() !!}</div>
                        <h1 class="mt-7 text-4xl font-extrabold leading-[1.02] tracking-tight sm:text-5xl lg:text-6xl">Back your high street.<br><span class="gtext">Own your customers.</span></h1>
                        <p class="mt-5 max-w-md text-base muted2 sm:text-lg">The local platform for {{ $llCity }}. Get found by nearby shoppers, run a free loyalty scheme, and bring your own customers back - the relationship stays yours.</p>
                        <div class="mt-7 inline-flex w-fit items-center gap-2 rounded-full bg-emerald-500/15 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald-300 ring-1 ring-emerald-400/30">For independent retailers</div>
                    </div>
                    <div class="relative hidden md:block">
                        <img src="{{ $imgOwner }}" alt="An independent shop owner" class="h-full w-full object-cover opacity-90">
                        <div class="absolute inset-0 bg-gradient-to-l from-transparent via-[#070a09]/40 to-[#070a09]"></div>
                        <div class="absolute bottom-6 right-6 text-center">
                            <div class="relative mx-auto h-24 w-24 rounded-2xl bg-white p-2 shadow-2xl">
                                <span class="ping absolute -right-1 -top-1 h-3 w-3 rounded-full bg-emerald-400"></span>
                                <div class="[&>svg]:h-full [&>svg]:w-full">{!! $signupQr !!}</div>
                            </div>
                            <div class="mt-2 text-xs font-bold text-white">Scan to sign up</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== 2 · THE PROBLEM ===== --}}
            <section x-show="slide===2" x-cloak x-transition.opacity.duration.400ms class="slide-dark absolute inset-0 flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">The problem</span>
                <h2 class="mt-3 max-w-3xl text-3xl font-extrabold leading-tight tracking-tight sm:text-4xl lg:text-5xl">Big platforms rent you <span class="gtext">your own customers</span> - and take a cut every time.</h2>
                <p class="mt-4 max-w-xl text-base muted2">A shopper finds you on a marketplace or an ad, you pay to reach them, and you never get to keep them. The independents who make a high street worth visiting get the worst of it.</p>
                <div class="mt-8 grid max-w-3xl gap-3 sm:grid-cols-3">
                    @foreach ([['Commissions', 'up to 30% per order on the big platforms'], ['Ad spend', 'paying again to reach people who already love you'], ['No list', 'their customer data, not yours']] as $i => $row)
                        <div class="r glass rounded-2xl p-4" style="animation-delay: {{ 0.1*$i }}s">
                            <div class="text-sm font-bold text-white">{{ $row[0] }}</div>
                            <div class="mt-1 text-xs muted2">{{ $row[1] }}</div>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ===== 3 · OWN YOUR CUSTOMERS ===== --}}
            <section x-show="slide===3" x-cloak x-transition.opacity.duration.400ms class="glow absolute inset-0 grid grid-rows-[auto_1fr] p-8 sm:p-12 lg:p-16">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">The locolie way</span>
                    <h2 class="mt-3 max-w-2xl text-3xl font-extrabold tracking-tight sm:text-4xl lg:text-5xl">A direct line to your customers.</h2>
                </div>
                <div class="flex items-center">
                    <div class="grid w-full max-w-4xl grid-cols-[1fr_auto_1fr] items-center gap-4 sm:gap-8">
                        <div class="r glass rounded-2xl p-5 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center overflow-hidden rounded-2xl" style="background: {{ $bColor }};">{!! $brandMark !!}</div>
                            <div class="mt-3 text-sm font-bold">Your shop</div>
                        </div>
                        <div class="r flex flex-col items-center" style="animation-delay:.15s">
                            <svg class="h-7 w-7 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            <div class="mt-2 rounded-full bg-emerald-500/15 px-3 py-1 text-[11px] font-bold text-emerald-300 ring-1 ring-emerald-400/30">direct, no middleman</div>
                            <svg class="mt-2 h-7 w-7 rotate-180 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </div>
                        <div class="r glass rounded-2xl p-5 text-center" style="animation-delay:.3s">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10"><svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/></svg></div>
                            <div class="mt-3 text-sm font-bold">Your customer</div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap items-center gap-2 text-xs muted2">
                    <span class="text-white/40 line-through">Facebook</span><span class="text-white/40 line-through">Google Ads</span><span class="text-white/40 line-through">Deliveroo</span>
                    <span class="ml-1">no one renting your audience back to you.</span>
                </div>
            </section>

            {{-- ===== 4 · MISSION ===== --}}
            <section x-show="slide===4" x-cloak x-transition.opacity.duration.400ms class="slide-dark absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Our mission</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Made in the North East, for independents.</h2>
                    <p class="mt-4 max-w-md text-base muted2">locolie exists for one reason: to help independent shops, cafes, pubs and makers win back regulars from the chains and the big platforms. Indies only, always - we will never list a multinational next to you.</p>
                    <div class="mt-6 flex items-center gap-3">
                        <x-seal variant="light" class="h-12 w-12" />
                        <div class="text-sm font-semibold">Independents only. Verified local.</div>
                    </div>
                </div>
                <div class="relative hidden md:block">
                    <img src="{{ $imgCafe }}" alt="An independent cafe" class="h-full w-full object-cover opacity-90">
                    <div class="absolute inset-0 bg-gradient-to-r from-[#070a09] via-transparent to-transparent"></div>
                </div>
            </section>

            {{-- ===== 5 · PROOF ===== --}}
            <section x-show="slide===5" x-cloak x-transition.opacity.duration.400ms class="glow grid-dots absolute inset-0 flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Already building</span>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">A directory shoppers actually use.</h2>
                <div class="mt-8 grid max-w-3xl grid-cols-2 gap-4 sm:grid-cols-4">
                    @foreach ([[number_format($stats['businesses']), 'independents mapped'], [$stats['categories'], 'categories'], [$llCity, 'launch city'], ['100%', 'indies only']] as $i => $kpi)
                        <div class="r glass rounded-2xl p-5" style="animation-delay: {{ 0.1*$i }}s">
                            <div class="text-3xl font-extrabold text-white">{{ $kpi[0] }}</div>
                            <div class="mt-1 text-[11px] font-semibold uppercase tracking-wider muted2">{{ $kpi[1] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6 flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        @for ($s=0;$s<5;$s++)<span class="flex h-6 w-6 items-center justify-center rounded bg-emerald-500"><svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="m12 17.3 6.2 3.7-1.6-7 5.4-4.7-7.1-.6L12 2 9.1 8.7l-7.1.6 5.4 4.7-1.6 7z"/></svg></span>@endfor
                        <span class="ml-2 text-sm font-semibold">Trustpilot</span>
                    </div>
                    <div class="flex -space-x-2">
                        @foreach ($cards->take(4) as $c)<span class="h-8 w-8 overflow-hidden rounded-full ring-2 ring-[#070a09]" style="background: {{ $c->brandColor() }};">@if($c->photos[0] ?? false)<img src="{{ $c->photos[0] }}" alt="" onerror="this.remove()" class="h-full w-full object-cover">@endif</span>@endforeach
                    </div>
                </div>
            </section>

            {{-- ===== 6 · PRICING ===== --}}
            <section x-show="slide===6" x-cloak x-transition.opacity.duration.400ms class="slide-dark absolute inset-0 grid grid-rows-[auto_1fr] p-8 sm:p-10 lg:p-12">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Pricing</span>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Free to start. Pay only to grow.</h2>
                </div>
                <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ($plans as $key => $p)
                        <div class="r flex flex-col rounded-2xl border p-4 {{ $key==='featured' ? 'border-emerald-400/50 bg-emerald-500/10' : 'glass' }}" style="animation-delay: {{ 0.08*$loop->index }}s">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-extrabold text-white">{{ $p['label'] }}</div>
                                @if($key==='featured')<span class="rounded-full bg-emerald-500 px-2 py-0.5 text-[9px] font-bold text-white">Popular</span>@endif
                            </div>
                            <div class="mt-1 text-2xl font-extrabold text-white">{{ is_null($p['price']) ? 'Custom' : '£'.$p['price'] }}<span class="text-xs font-medium muted2">{{ is_null($p['price']) ? '' : '/mo' }}</span></div>
                            <div class="text-[10px] muted2">{{ $tierBlurb[$key] ?? '' }}</div>
                            <div class="mt-3 text-[11px] font-semibold text-emerald-300">
                                @if(($p['sends']['email'] ?? 0) === 0) Loyalty only, no sends
                                @elseif(($p['sends']['email'] ?? 0) >= PHP_INT_MAX) Unlimited email, SMS &amp; push
                                @else {{ number_format($p['sends']['email']) }} email · {{ number_format($p['sends']['sms']) }} SMS / mo @endif
                            </div>
                            <ul class="mt-2 space-y-1">
                                @foreach (array_slice($p['perks'], 0, 3) as $perk)
                                    <li class="flex items-start gap-1.5 text-[11px] muted2"><svg class="mt-0.5 h-3 w-3 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg><span>{!! $perk !!}</span></li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ===== 7 · GET FOUND ===== --}}
            <section x-show="slide===7" x-cloak x-transition.opacity.duration.400ms class="glow absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Step 1</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Claim your shop, get found</h2>
                    <p class="mt-3 max-w-md text-base muted2">Add your photos, hours and story in minutes. You appear in search, on the map, and on your own {{ $llCity }} page - so nearby shoppers find you, not just the chains.</p>
                    <ul class="mt-5 space-y-2 text-sm">
                        @foreach (['Live in the '.$llCity.' directory and on the map', 'Your own shareable shop page', 'Found via "{category} in '.$llCity.'" search pages'] as $pt)
                            <li class="flex items-start gap-2.5"><svg class="mt-0.5 h-4.5 w-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><span class="text-white/85">{{ $pt }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="relative flex items-center justify-center overflow-hidden p-8">
                    <div class="r w-full max-w-[17rem] overflow-hidden rounded-2xl bg-white text-[#0a0a0a] shadow-2xl">
                        <div class="relative h-32 overflow-hidden" style="background: linear-gradient(135deg, {{ $bColor }}, #0a0a0a);">
                            @if ($bPhoto)<img src="{{ $bPhoto }}" alt="{{ $bName }}" onerror="this.remove()" class="h-full w-full object-cover">@endif
                            <span class="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold text-emerald-700">Independent</span>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl" style="background: {{ $bColor }};">{!! $brandMark !!}</span>
                                <div class="min-w-0"><h3 class="truncate text-base font-bold">{{ $bName }}</h3><div class="truncate text-xs text-slate-500">{{ $bCategory }} · {{ $bCity }}</div></div>
                            </div>
                            <div class="mt-2 flex items-center gap-1 text-xs text-slate-500"><span class="text-amber-500">★</span> {{ $bRating }} · Verified local</div>
                            <div class="mt-3 rounded-full bg-[#0a0a0a] py-2 text-center text-xs font-semibold text-white">View shop</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== 8 · SCAN TO CAPTURE ===== --}}
            <section x-show="slide===8" x-cloak x-transition.opacity.duration.400ms class="slide-dark absolute inset-0 grid grid-rows-[auto_1fr] p-8 sm:p-12 lg:p-14">
                <div>
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Step 2 · How customers join</span>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">One scan builds your customer list</h2>
                    <p class="mt-2 max-w-2xl text-sm muted2">Your locolie poster sits on the counter. A customer scans it, opts in on a branded page, and joins your loyalty scheme and marketing list - in seconds.</p>
                </div>
                <div class="grid grid-cols-3 items-center gap-4">
                    <div class="r text-center">
                        <div class="relative mx-auto w-fit rounded-2xl bg-white p-2.5 shadow-2xl">
                            <div class="[&>svg]:block [&>svg]:h-24 [&>svg]:w-24 sm:[&>svg]:h-28 sm:[&>svg]:w-28">{!! $signupQr !!}</div>
                            <div class="scanline absolute inset-x-2.5 h-0.5 rounded bg-emerald-500 shadow-[0_0_10px_3px_rgba(5,150,105,.7)]"></div>
                        </div>
                        <div class="mt-3 text-sm font-semibold muted2">1. Scan in store</div>
                    </div>
                    <div class="r text-center" style="animation-delay:.18s">
                        <div class="mx-auto w-full max-w-[9rem] rounded-2xl bg-white p-3 text-left text-[#0a0a0a] shadow-2xl">
                            <div class="flex items-center gap-1.5"><span class="flex h-5 w-5 items-center justify-center overflow-hidden rounded" style="background: {{ $bColor }};">{!! $brandMark !!}</span><span class="text-[10px] font-bold">Join {{ \Illuminate\Support\Str::limit($bName, 12) }}</span></div>
                            <div class="mt-2 space-y-1"><div class="h-3.5 rounded bg-slate-100"></div><div class="h-3.5 rounded bg-slate-100"></div><div class="rounded bg-emerald-500 py-1 text-center text-[9px] font-bold text-white">Join</div></div>
                        </div>
                        <div class="mt-3 text-sm font-semibold muted2">2. Opt in</div>
                    </div>
                    <div class="pp text-center" style="animation-delay:.36s">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-500 sm:h-24 sm:w-24"><svg class="h-10 w-10 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></div>
                        <div class="mt-3 text-sm font-semibold muted2">3. On your list</div>
                    </div>
                </div>
            </section>

            {{-- ===== 9 · LOYALTY ===== --}}
            <section x-show="slide===9" x-cloak x-transition.opacity.duration.400ms class="glow absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Step 3 · Free, forever</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">A loyalty scheme that costs nothing</h2>
                    <p class="mt-3 max-w-md text-base muted2">A digital stamp card on the customer's phone. No plastic, no app to build, no fees. You set the reward - buy nine, get the tenth free, or whatever suits.</p>
                </div>
                <div class="flex items-center justify-center p-8">
                    <div class="r w-full max-w-[17rem] rounded-2xl bg-white p-5 text-[#0a0a0a] shadow-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2"><span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg" style="background: {{ $bColor }};">{!! $brandMark !!}</span><div><div class="text-xs font-bold">{{ $bName }}</div><div class="text-[10px] text-slate-500">Loyalty card</div></div></div>
                            <svg class="h-5 w-auto" viewBox="0 0 24 24" fill="#059669"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $pinPath }}"/></svg>
                        </div>
                        <div class="mt-4 grid grid-cols-5 gap-2">
                            @for ($s=1;$s<=10;$s++)
                                <div class="{{ $s<=7?'pp':'' }} flex aspect-square items-center justify-center rounded-full {{ $s<=7?'bg-emerald-500 text-white':'border-2 border-dashed border-slate-200 text-slate-300' }}" @if($s<=7) style="animation-delay: {{ 0.14*$s }}s" @endif>@if($s<=7)<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>@else<span class="text-[11px] font-bold">{{ $s }}</span>@endif</div>
                            @endfor
                        </div>
                        <div class="mt-4 rounded-xl bg-emerald-50 px-3 py-2 text-center"><div class="text-[10px] font-bold uppercase tracking-[0.15em] text-emerald-700">3 to go</div><div class="text-xs font-bold">Next reward: a free coffee on us</div></div>
                    </div>
                </div>
            </section>

            {{-- ===== 10 · MARKETING + PRIVACY ===== --}}
            <section x-show="slide===10" x-cloak x-transition.opacity.duration.400ms class="slide-dark absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Step 4</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Bring your customers back</h2>
                    <p class="mt-3 max-w-md text-base muted2">Send a branded email, SMS or push anytime. A new season, an event, a reason to pop in.</p>
                    <div class="mt-5 flex items-start gap-3 rounded-2xl border border-emerald-400/30 bg-emerald-500/10 p-4">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <p class="text-xs leading-relaxed text-white/85"><span class="font-bold text-white">Our privacy promise.</span> Your customers' contact details stay protected. We never sell or share them, and messages go out through locolie - so the relationship stays yours.</p>
                    </div>
                </div>
                <div class="flex items-center justify-center p-8">
                    <div class="fl relative w-full max-w-[13rem] overflow-hidden rounded-[2rem] border-[10px] border-[#1c1c1c] bg-cover bg-center shadow-2xl ring-1 ring-white/10" style="background-image:url('{{ $imgOwner }}'); aspect-ratio: 9/16;">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/30 to-black/65"></div>
                        <div class="absolute left-0 right-0 top-0 flex items-center justify-center bg-black/30 py-1.5"><span class="h-3 w-12 rounded-full bg-[#0a0a0a]"></span></div>
                        <div class="absolute inset-x-3 top-10 space-y-2">
                            <div class="r rounded-2xl bg-white/95 p-2.5 text-[#0a0a0a] shadow-lg" style="animation-delay:.2s"><div class="flex items-center gap-1.5"><span class="flex h-5 w-5 items-center justify-center overflow-hidden rounded" style="background: {{ $bColor }};">{!! $brandMark !!}</span><span class="text-[10px] font-bold">{{ \Illuminate\Support\Str::limit($bName, 15) }}</span><span class="ml-auto text-[9px] text-slate-400">now</span></div><p class="mt-1 text-[10px] leading-snug text-slate-700">New autumn menu just dropped - come and try it this week.</p></div>
                            <div class="r rounded-2xl bg-white/85 p-2.5 text-[#0a0a0a] shadow" style="animation-delay:.5s"><div class="flex items-center gap-1.5"><span class="flex h-5 w-5 items-center justify-center rounded bg-emerald-500 text-[9px] font-extrabold text-white">@</span><span class="text-[10px] font-bold">Email · branded</span></div><p class="mt-1 text-[10px] leading-snug text-slate-600">You are 3 stamps from a free coffee.</p></div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== 11 · THE APP, LIVE (real animated demo) ===== --}}
            <section x-show="slide===11" x-cloak x-transition.opacity.duration.400ms class="glow grid-dots absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                    <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">The app, live</span>
                    <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">What your customers hold</h2>
                    <p class="mt-3 max-w-md text-base muted2">Shoppers discover you, save your offers and loyalty, and scan at the till - all in one beautiful app. This is the real thing, running live.</p>
                    <ul class="mt-5 space-y-2 text-sm">
                        @foreach (['Discover local shops and offers', 'Reveal and redeem in store', 'Watch their savings add up'] as $pt)
                            <li class="flex items-start gap-2.5"><svg class="mt-0.5 h-4.5 w-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><span class="text-white/85">{{ $pt }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div class="relative flex items-center justify-center overflow-hidden">
                    {{-- The real animated app component, scaled to fit the slide. --}}
                    <div style="transform: scale(.58); transform-origin: center;">
                        @include('site._appwalk', ['src' => $appSrc, 'dark' => true, 'cards' => $cards, 'class' => ''])
                    </div>
                </div>
            </section>

            {{-- ===== 12 · YOUR DASHBOARD ===== --}}
            <section x-show="slide===12" x-cloak x-transition.opacity.duration.400ms class="slide-dark absolute inset-0 flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                <span class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">Your dashboard</span>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Go live, then watch it work</h2>
                <p class="mt-3 max-w-xl text-base muted2">Who is visiting, loyalty stamps collected, and how your messages land. Clear numbers, no jargon - all in your retailer dashboard.</p>
                <div class="mt-7 grid max-w-3xl gap-3 sm:grid-cols-4">
                    @foreach ([['128', 'Customers'], ['46', 'Loyalty stamps'], ['62%', 'Email opens'], ['£1,240', 'Influenced spend']] as $i => $kpi)
                        <div class="r glass rounded-2xl p-4" style="animation-delay: {{ 0.1*$i }}s"><div class="text-2xl font-extrabold text-white">{{ $kpi[0] }}</div><div class="mt-1 text-[10px] font-semibold uppercase tracking-wider muted2">{{ $kpi[1] }}</div></div>
                    @endforeach
                </div>
                <div class="mt-5 flex items-center gap-3 text-xs muted2"><svg class="h-4 w-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg> Visits, loyalty and message performance, updated live.</div>
            </section>

            {{-- ===== 13 · SIGN UP / CONTACT ===== --}}
            <section x-show="slide===13" x-cloak x-transition.opacity.duration.400ms class="glow grid-dots absolute inset-0 grid grid-cols-1 md:grid-cols-[1.2fr_1fr]">
                <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                    <div class="text-xl">{!! $wm() !!}</div>
                    <h2 class="mt-5 text-4xl font-extrabold tracking-tight sm:text-5xl">Claim your shop today.</h2>
                    <p class="mt-4 max-w-md text-base muted2">Free listing, always. Loyalty included. Priority placement and marketing from £{{ $plans['featured']['price'] ?? 19 }}/mo.</p>
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        <a href="{{ $signupUrl }}" class="inline-flex items-center gap-2 rounded-full bg-emerald-500 px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald-400">Sign up your shop<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                        <a href="mailto:info@locolie.com" class="text-sm font-semibold text-white/80 underline-offset-4 hover:underline">info@locolie.com</a>
                    </div>
                </div>
                <div class="flex items-center justify-center p-8">
                    <div class="r text-center">
                        <div class="mx-auto h-44 w-44 rounded-2xl bg-white p-3 shadow-2xl [&>svg]:h-full [&>svg]:w-full">{!! $signupQr !!}</div>
                        <div class="mt-3 text-sm font-bold text-white">Scan to sign up</div>
                        <div class="text-xs muted2">locolie.com</div>
                    </div>
                </div>
            </section>

        </div>

        {{-- Nav --}}
        <div class="deck-nav mt-4 flex w-full max-w-[1120px] items-center justify-between">
            <button type="button" @click="prev()" :disabled="slide===1" class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-30"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>Back</button>
            <div class="flex flex-wrap items-center justify-center gap-1.5">
                <template x-for="n in total" :key="n"><button type="button" @click="go(n)" :class="slide===n ? 'w-6 bg-emerald-500' : 'w-2 bg-white/25 hover:bg-white/45'" class="h-2 rounded-full transition-all" :aria-label="'Go to slide ' + n"></button></template>
            </div>
            <button type="button" @click="next()" x-show="slide<total" class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-400">Next<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></button>
            <a href="{{ $signupUrl }}" x-show="slide===total" x-cloak class="inline-flex items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-[#0a0a0a] transition hover:bg-emerald-500 hover:text-white">Get started<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
        <p class="deck-hint mt-3 text-center text-xs text-white/40">Use the arrow keys, or click. Present for fullscreen, or print to a PDF anytime with the buttons up top.</p>
    </div>
</div>

{{-- =================================================================== --}}
{{-- PRINT VERSION (light, one landscape page each, QR + visuals kept)    --}}
{{-- =================================================================== --}}
<div class="deck-print bg-white text-[#0a0a0a]">
    @php
        $printSlides = [
            ['locolie for retailers', 'Back your high street. Own your customers.', 'The local platform for '.$llCity.'. Get found by nearby shoppers, run a free loyalty scheme, and bring your own customers back - the relationship stays yours.', ['A free listing shoppers actually use', 'A loyalty scheme that costs you nothing', 'Email, SMS and push to your own customers'], 'qr'],
            ['The problem', 'Big platforms rent you your own customers', 'A shopper finds you on a marketplace or an ad, you pay to reach them, and you never get to keep them. Commissions up to 30%, ad spend to reach people who already love you, and their data - not yours.', ['Commissions on every order', 'Paying again to reach your own customers', 'You never own the relationship'], 'costs'],
            ['The locolie way', 'A direct line to your customers', 'locolie connects you straight to the shopper - no marketplace, no ad auction, no one renting your audience back to you. You own the list, the data and the relationship.', ['Direct, no middleman', 'You own your customer list', 'Indies only, verified local'], 'none'],
            ['Our mission', 'Made in the North East, for independents', 'locolie exists to help independent shops, cafes, pubs and makers win back regulars from the chains and big platforms. Indies only, always.', ['Independents only', 'Built for the local high street', 'Verified, local listings'], 'seal'],
            ['Already building', 'A directory shoppers actually use', number_format($stats['businesses']).' independents mapped across '.$stats['categories'].' categories in '.$llCity.'. Indies only, and rated on Trustpilot.', [number_format($stats['businesses']).' independents mapped', $stats['categories'].' categories', '100% independents only'], 'stats'],
            ['Pricing', 'Free to start. Pay only to grow.', 'Free listing and loyalty for every shop. Featured from £'.($plans['featured']['price'] ?? 19).'/mo unlocks placement and marketing; Premium £'.($plans['premium']['price'] ?? 49).'/mo adds push and analytics; Enterprise for chains.', ['Free: listing, offers, loyalty', 'Featured £'.($plans['featured']['price'] ?? 19).': placement + 2,000 emails / 250 SMS', 'Premium £'.($plans['premium']['price'] ?? 49).': push, analytics, 10,000 emails'], 'pricing'],
            ['Step 1 - Get found', 'Claim your shop, get found', 'Add your photos, hours and story in minutes. You appear in search, on the map and on your own '.$llCity.' page - so nearby shoppers find you, not just the chains.', ['Live in the '.$llCity.' directory and map', 'Your own shareable shop page', 'Found via category search pages'], 'listing'],
            ['Step 2 - How customers join', 'One scan builds your customer list', 'Put your locolie poster on the counter. A customer scans it, opts in on a branded page, and joins your loyalty scheme and marketing list in seconds.', ['Scan the poster in store', 'Opt in on your branded page', 'Straight onto your customer list'], 'qr'],
            ['Step 3 - Loyalty (free)', 'A loyalty scheme that costs nothing', 'A digital stamp card on the customer\'s phone. No plastic, no app to build, no fees. You set the reward.', ['Digital stamp card, nothing to print', 'You set the reward', 'Built into your free listing'], 'stamp'],
            ['Step 4 - Marketing', 'Bring your customers back', 'Send a branded email, SMS or push anytime. Contact details stay protected - we never sell or share them, and messages go out through locolie, so the relationship stays yours.', ['Branded email, SMS and push', 'Contact details stay protected', 'The relationship stays yours'], 'push'],
            ['The app, live', 'What your customers hold', 'Shoppers discover you, save your offers and loyalty, and scan at the till - all in one beautiful app.', ['Discover local shops and offers', 'Reveal and redeem in store', 'Savings add up automatically'], 'none'],
            ['Your dashboard', 'Go live, then watch it work', 'Who is visiting, loyalty stamps collected and how your messages land. Clear numbers, no jargon.', ['Customers and new sign-ups', 'Loyalty running automatically', 'Message performance, live'], 'kpis'],
            ['Sign up', 'Claim your shop today', 'Free listing, always. Loyalty included. Priority placement and marketing from £'.($plans['featured']['price'] ?? 19).'/mo. Questions: info@locolie.com', ['Scan the QR to sign up', 'Free listing and loyalty', 'Upgrade only when you grow'], 'qr'],
        ];
    @endphp
    @foreach ($printSlides as $i => $s)
        <section class="print-slide relative mx-auto my-5 flex w-full max-w-[265mm] flex-col overflow-hidden rounded-2xl bg-[#070a09] p-10 text-white" style="aspect-ratio:16/9">
            <span class="pointer-events-none absolute -right-20 -top-20 h-64 w-64 rounded-full" style="background:radial-gradient(circle, rgba(16,185,129,.22), transparent 70%)"></span>
            <span class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full" style="background:radial-gradient(circle, rgba(5,150,105,.15), transparent 70%)"></span>
            <span class="pointer-events-none absolute inset-0" style="background-image:radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);background-size:24px 24px"></span>
            <div class="relative flex items-center justify-between border-b border-white/10 pb-3">
                <span class="text-lg">{!! $wm('text-white') !!}</span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-white/40">Slide {{ $i + 1 }} of {{ count($printSlides) }}</span>
            </div>
            <p class="relative mt-6 text-xs font-bold uppercase tracking-[0.2em] text-emerald-400">{{ $s[0] }}</p>
            <h2 class="relative mt-2 text-4xl font-extrabold tracking-tight">{{ $s[1] }}</h2>
            <p class="relative mt-3 max-w-2xl text-sm leading-relaxed text-white/65">{{ $s[2] }}</p>
            <div class="relative mt-6 flex flex-1 items-center gap-10">
                <ul class="flex-1 space-y-3">
                    @foreach ($s[3] as $point)
                        <li class="flex items-start gap-2.5 text-sm"><svg class="mt-0.5 h-4.5 w-4.5 shrink-0 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg><span class="text-white/85">{{ $point }}</span></li>
                    @endforeach
                </ul>
                <div class="shrink-0">
                    @if ($s[4] === 'qr')
                        <div class="text-center"><div class="h-44 w-44 rounded-xl bg-white p-2.5 [&>svg]:h-full [&>svg]:w-full">{!! $signupQr !!}</div><div class="mt-2 text-[11px] font-bold text-white">Scan to sign up</div></div>
                    @elseif ($s[4] === 'stamp')
                        <div class="w-48 rounded-2xl bg-white p-3 text-[#0a0a0a]"><div class="grid grid-cols-5 gap-1.5">@for($x=1;$x<=10;$x++)<div class="flex aspect-square items-center justify-center rounded-full {{ $x<=7?'bg-emerald-500 text-white':'border-2 border-dashed border-slate-200 text-slate-300' }}">@if($x<=7)<svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>@else<span class="text-[9px] font-bold">{{ $x }}</span>@endif</div>@endfor</div><div class="mt-2 rounded-lg bg-emerald-50 py-1 text-center text-[10px] font-bold text-emerald-700">3 to go: free coffee</div></div>
                    @elseif ($s[4] === 'push')
                        <div class="w-48 space-y-2"><div class="rounded-xl bg-white p-2.5 text-[#0a0a0a] shadow-sm"><div class="flex items-center gap-1.5"><span class="flex h-5 w-5 items-center justify-center overflow-hidden rounded" style="background: {{ $bColor }};">{!! $brandMark !!}</span><span class="text-[10px] font-bold">{{ \Illuminate\Support\Str::limit($bName, 14) }}</span></div><p class="mt-1 text-[10px] text-slate-600">New autumn menu just dropped.</p></div><div class="rounded-xl bg-white p-2.5 text-[#0a0a0a] shadow-sm"><span class="text-[10px] font-bold">Email · branded</span><p class="mt-0.5 text-[10px] text-slate-600">3 stamps from a free coffee.</p></div></div>
                    @elseif ($s[4] === 'listing')
                        <div class="w-48 overflow-hidden rounded-2xl bg-white text-[#0a0a0a]"><div class="h-24" style="background: linear-gradient(135deg, {{ $bColor }}, #0a0a0a);">@if($bPhoto)<img src="{{ $bPhoto }}" alt="" onerror="this.remove()" class="h-full w-full object-cover">@endif</div><div class="p-2.5"><div class="text-xs font-bold">{{ \Illuminate\Support\Str::limit($bName, 16) }}</div><div class="text-[10px] text-slate-500">{{ $bCategory }} · {{ $bCity }}</div></div></div>
                    @elseif ($s[4] === 'costs')
                        <div class="w-52 space-y-2">@foreach ([['Commissions','up to 30% per order'],['Ad spend','to reach your own fans'],['No list','their data, not yours']] as $cst)<div class="rounded-xl border border-white/10 bg-white/5 p-3"><div class="text-xs font-bold text-white">{{ $cst[0] }}</div><div class="text-[10px] text-white/60">{{ $cst[1] }}</div></div>@endforeach</div>
                    @elseif ($s[4] === 'stats')
                        <div class="grid w-56 grid-cols-2 gap-2.5">@foreach ([[number_format($stats['businesses']),'independents'],[$stats['categories'],'categories'],[$llCity,'launch city'],['100%','indies only']] as $kpi)<div class="rounded-xl border border-white/10 bg-white/5 p-3"><div class="text-xl font-extrabold text-white">{{ $kpi[0] }}</div><div class="text-[9px] font-semibold uppercase tracking-wider text-white/55">{{ $kpi[1] }}</div></div>@endforeach</div>
                    @elseif ($s[4] === 'pricing')
                        <div class="grid w-60 grid-cols-2 gap-2.5">@foreach ($plans as $key => $p)<div class="rounded-xl border {{ $key==='featured'?'border-emerald-400/50 bg-emerald-500/10':'border-white/10 bg-white/5' }} p-3"><div class="text-xs font-extrabold text-white">{{ $p['label'] }}</div><div class="text-lg font-extrabold text-white">{{ is_null($p['price'])?'Custom':'£'.$p['price'] }}</div></div>@endforeach</div>
                    @elseif ($s[4] === 'kpis')
                        <div class="grid w-56 grid-cols-2 gap-2.5">@foreach ([['128','Customers'],['46','Stamps'],['62%','Opens'],['£1,240','Spend']] as $kpi)<div class="rounded-xl border border-white/10 bg-white/5 p-3"><div class="text-xl font-extrabold text-white">{{ $kpi[0] }}</div><div class="text-[9px] font-semibold uppercase tracking-wider text-white/55">{{ $kpi[1] }}</div></div>@endforeach</div>
                    @elseif ($s[4] === 'seal')
                        <div class="flex flex-col items-center gap-2"><x-seal variant="light" class="h-24 w-24" /><div class="text-[11px] font-semibold text-white/80">Independents only</div></div>
                    @endif
                </div>
            </div>
        </section>
    @endforeach
</div>

</body>
</html>
