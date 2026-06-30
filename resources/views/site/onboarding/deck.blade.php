@php
    // Launch-market values (config/locolie.php) - never re-hardcode city/price.
    $ll = config('locolie.launch');
    $llPlace = $ll['place'];                 // "Newcastle NE1"
    $llCity = $ll['city'];                   // "Newcastle"
    $llFeaturedPrice = \App\Models\Business::PLANS['featured']['price'] ?? 19;

    // Brand pin (matches the rest of the site).
    $pinPath = 'M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z';

    // Concrete values from a real local business so screenshots feel like the product.
    $bName     = $business?->name ?? 'Your shop';
    $bCity     = $business?->city ?? $llCity;
    $bCategory = $business?->category?->name ?? 'Independent';
    $bRating   = number_format((float) ($business->rating ?? 4.8), 1);
    $bPhoto    = $business?->photos[0] ?? null;
    $bColor    = $business?->brandColor() ?? '#059669';
    $bInitials = $business?->brandInitials() ?? 'LO';
    $bLogo     = $business?->logoUrl();

    // Marketing photography reused from the real site assets.
    $imgCafe   = asset('images/marketing/indie-cafe.jpg');
    $imgOwner  = asset('images/marketing/cafe-owner.jpg');

    // Reusable bits.
    $wordmark = '<span class="wordmark">L<svg class="mx-[-0.02em] inline-block h-[0.92em] w-auto align-[-0.12em]" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="'.$pinPath.'"/></svg>colie</span>';
    // The shop's brand mark: real logo if uploaded, else an initials chip in brand colour.
    $brandMark = $bLogo
        ? '<img src="'.$bLogo.'" alt="'.e($bName).'" class="h-full w-full rounded-[inherit] object-cover" onerror="this.remove()">'
        : '<span class="text-[0.6em] font-extrabold leading-none text-white">'.e($bInitials).'</span>';
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to locolie - retailer onboarding</title>
    <meta name="description" content="A quick walkthrough of how locolie works for your shop: get found, capture customers with a scan, run a free loyalty scheme, and market to your own customers. Launching in {{ $llPlace }}.">
    <meta name="robots" content="noindex">
    <link rel="icon" href="/icon.svg" type="image/svg+xml">
    <meta name="theme-color" content="#0a0a0a">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root { --emerald: #059669; --emerald-soft: #d1fae5; --ink:#0a0a0a; }
        html, body { font-family: 'Inter', system-ui, sans-serif; }
        body { color: #0a0a0a; background: #0a0a0a; -webkit-font-smoothing: antialiased; }
        ::selection { background: #05966933; }
        [x-cloak] { display: none !important; }
        .wordmark { font-weight: 800; letter-spacing: -0.03em; display: inline-flex; align-items: center; line-height: 1; }
        .gradient-text { background: linear-gradient(120deg, #059669, #10b981 40%, #047857); -webkit-background-clip: text; background-clip: text; color: transparent; }
        .hero-grid { background-image: radial-gradient(#0000000a 1px, transparent 1px); background-size: 22px 22px; }

        /* The deck is SQUARE. Each stage is a 1:1 card sized to fit the viewport. */
        .deck-stage { aspect-ratio: 1 / 1; width: min(92vw, 70vh); max-width: 660px; }
        .qr-svg > svg { display: block; width: 100%; height: 100%; }
        a:focus-visible, button:focus-visible { outline: 2px solid var(--emerald); outline-offset: 3px; border-radius: 6px; }

        /* ---- Animations (run when a slide becomes visible) ---- */
        @keyframes llRise { 0% { opacity: 0; transform: translateY(12px); } 100% { opacity: 1; transform: none; } }
        @keyframes llPop  { 0% { opacity: 0; transform: scale(.4); } 60% { transform: scale(1.1); } 100% { opacity: 1; transform: scale(1); } }
        @keyframes llPing { 0% { transform: scale(.6); opacity: .55; } 100% { transform: scale(2.6); opacity: 0; } }
        @keyframes llFloat { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-7px); } }
        @keyframes llScan { 0% { top: 8%; } 50% { top: 84%; } 100% { top: 8%; } }
        .a-rise  { animation: llRise .55s cubic-bezier(.16,.8,.3,1) both; }
        .a-pop   { animation: llPop .5s cubic-bezier(.16,.8,.3,1) both; }
        .a-float { animation: llFloat 4.5s ease-in-out infinite; }
        .ping-ring { animation: llPing 2.4s ease-out infinite; }
        .scan-line { animation: llScan 2.6s ease-in-out infinite; }
        @media (prefers-reduced-motion: reduce) { .a-rise,.a-pop,.a-float,.ping-ring,.scan-line { animation: none !important; } }

        /* ---- Print: one SQUARE slide per page, visuals kept, no chrome ---- */
        @media print {
            @page { size: A4 portrait; margin: 12mm; }
            html, body { background: #ffffff !important; }
            .no-print { display: none !important; }
            .deck-screen { display: none !important; }
            .deck-print { display: block !important; }
            .print-slide { break-after: page; page-break-after: always; box-shadow: none !important; }
            .print-slide:last-child { break-after: auto; page-break-after: auto; }
        }
        .deck-print { display: none; }
    </style>
</head>
<body class="antialiased">

{{-- =================================================================== --}}
{{-- ON-SCREEN DECK (square, interactive, one slide visible at a time)     --}}
{{-- =================================================================== --}}
<div class="deck-screen no-print min-h-screen bg-[#0a0a0a] text-white"
     x-data="{
        slide: 1,
        total: 6,
        next() { if (this.slide < this.total) this.slide++; },
        prev() { if (this.slide > 1) this.slide--; },
        go(n) { this.slide = n; },
     }"
     @keydown.window.arrow-right="next()"
     @keydown.window.arrow-left="prev()"
     @keydown.window.space.prevent="next()">

    {{-- Top bar: brand logo + counter + print --}}
    <header class="mx-auto flex max-w-3xl items-center justify-between px-4 py-3 sm:px-6">
        <a href="/business" class="text-lg text-white">{!! $wordmark !!}</a>
        <div class="flex items-center gap-2 sm:gap-3">
            <span class="rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/80">
                Slide <span x-text="slide"></span> / <span x-text="total"></span>
            </span>
            <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-1.5 rounded-full bg-white px-3.5 py-1.5 text-xs font-bold text-ink transition hover:bg-emerald hover:text-white">
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print / Save as PDF
            </button>
        </div>
    </header>

    {{-- Stage --}}
    <div class="mx-auto flex max-w-3xl flex-col items-center px-4 pb-4 sm:px-6">
        <div class="deck-stage relative overflow-hidden rounded-3xl border border-white/10 bg-white text-ink shadow-2xl">

            {{-- ============ SLIDE 1: Welcome + scan to sign up ============ --}}
            <section x-show="slide === 1" x-transition.opacity.duration.300ms class="hero-grid absolute inset-0 flex flex-col justify-between p-7 sm:p-9">
                <div>
                    <div class="text-xl text-ink">{!! $wordmark !!}</div>
                    <h1 class="mt-6 text-3xl font-extrabold leading-[1.05] tracking-tight sm:text-4xl">
                        Back your high street. <span class="gradient-text">Own your customers.</span>
                    </h1>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-muted sm:text-base">
                        locolie is the local-business platform for {{ $llCity }}. Get found by nearby shoppers, run a free loyalty scheme, and bring your own customers back - all in one place.
                    </p>
                </div>
                <div class="flex items-end justify-between gap-4">
                    <ul class="space-y-2 text-sm">
                        @foreach (['A free listing shoppers actually use', 'A loyalty scheme that costs you nothing', 'Email, SMS and push to your own customers'] as $point)
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4.5 w-4.5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                <span class="text-ink/80">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                    {{-- Real, scannable signup QR --}}
                    <div class="shrink-0 text-center">
                        <div class="qr-svg mx-auto h-28 w-28 rounded-xl border border-hair bg-white p-1.5 shadow-sm sm:h-32 sm:w-32">{!! $signupQr !!}</div>
                        <div class="mt-1.5 text-[11px] font-bold text-ink">Scan to sign up</div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 2: Get found ============ --}}
            <section x-show="slide === 2" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0 grid grid-rows-[auto_1fr]">
                <div class="bg-[#fafafa] p-7 sm:p-9">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald">Step 1</span>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Claim your shop and get found</h2>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-muted">
                        Add your photos, hours and story in minutes. You appear in search, on the map, and on your own {{ $llCity }} page - so nearby shoppers find you, not just the chains.
                    </p>
                </div>
                <div class="relative flex items-center justify-center overflow-hidden bg-gradient-to-br from-emerald-soft/60 to-white p-6">
                    {{-- Listing card mock, real business --}}
                    <div class="a-rise w-full max-w-[15rem] overflow-hidden rounded-2xl border border-hair bg-white shadow-xl">
                        <div class="relative h-28 overflow-hidden" style="background: linear-gradient(135deg, {{ $bColor }}, #0a0a0a);">
                            @if ($bPhoto)<img src="{{ $bPhoto }}" alt="{{ $bName }}" loading="lazy" onerror="this.remove()" class="h-full w-full object-cover">@endif
                            <span class="absolute left-2.5 top-2.5 rounded-full bg-white/90 px-2 py-0.5 text-[10px] font-bold text-emerald">Independent</span>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl" style="background: {{ $bColor }};">{!! $brandMark !!}</span>
                                <div class="min-w-0">
                                    <h3 class="truncate text-base font-bold text-ink">{{ $bName }}</h3>
                                    <div class="truncate text-xs text-muted">{{ $bCategory }} <span class="text-hair">·</span> {{ $bCity }}</div>
                                </div>
                            </div>
                            <div class="mt-2 flex items-center gap-1 text-xs text-muted"><span class="text-amber-500">★</span> {{ $bRating }} <span class="text-hair">·</span> Verified local</div>
                            <div class="mt-3 inline-flex w-full items-center justify-center rounded-full bg-ink py-2 text-xs font-semibold text-white">View shop</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 3: Scan to capture (the mechanic) ============ --}}
            <section x-show="slide === 3" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0 grid grid-rows-[auto_1fr]">
                <div class="p-7 sm:p-9">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald">Step 2 · How customers join</span>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">One scan builds your customer list</h2>
                    <p class="mt-2 max-w-lg text-sm leading-relaxed text-muted">
                        Put your locolie poster on the counter. A customer scans it, opts in on a branded page, and joins your loyalty scheme and marketing list - in seconds.
                    </p>
                </div>
                <div class="grid grid-cols-3 items-center gap-3 bg-gradient-to-br from-ink to-[#10241c] p-6 text-white">
                    {{-- a) poster with QR + animated scan line --}}
                    <div class="a-rise text-center" style="animation-delay:.05s">
                        <div class="relative mx-auto w-fit rounded-xl bg-white p-2 shadow-lg">
                            <div class="qr-svg h-20 w-20 sm:h-24 sm:w-24">{!! $signupQr !!}</div>
                            <div class="scan-line absolute inset-x-2 h-0.5 rounded bg-emerald shadow-[0_0_8px_2px_#05966999]"></div>
                        </div>
                        <div class="mt-2 text-[11px] font-semibold text-white/80">1. Scan in store</div>
                    </div>
                    {{-- b) branded opt-in form --}}
                    <div class="a-rise text-center" style="animation-delay:.2s">
                        <div class="mx-auto w-full max-w-[8rem] rounded-xl bg-white p-2.5 text-left shadow-lg">
                            <div class="flex items-center gap-1.5">
                                <span class="flex h-5 w-5 items-center justify-center overflow-hidden rounded" style="background: {{ $bColor }};">{!! $brandMark !!}</span>
                                <span class="text-[10px] font-bold text-ink">Join {{ \Illuminate\Support\Str::limit($bName, 12) }}</span>
                            </div>
                            <div class="mt-1.5 space-y-1">
                                <div class="h-3 rounded bg-slate-100"></div>
                                <div class="h-3 rounded bg-slate-100"></div>
                                <div class="rounded bg-emerald py-1 text-center text-[9px] font-bold text-white">Join</div>
                            </div>
                        </div>
                        <div class="mt-2 text-[11px] font-semibold text-white/80">2. Opt in</div>
                    </div>
                    {{-- c) joined --}}
                    <div class="a-pop text-center" style="animation-delay:.4s">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald sm:h-20 sm:w-20">
                            <svg class="h-8 w-8 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        </div>
                        <div class="mt-2 text-[11px] font-semibold text-white/80">3. On your list</div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 4: Free loyalty (animated stamps) ============ --}}
            <section x-show="slide === 4" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0 grid grid-rows-[auto_1fr]">
                <div class="bg-[#fafafa] p-7 sm:p-9">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald">Step 3 · Free, forever</span>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">A loyalty scheme that costs nothing</h2>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-muted">
                        A digital stamp card on the customer's phone. No plastic, no app to build, no fees. You set the reward - buy nine, get the tenth free, or whatever suits.
                    </p>
                </div>
                <div class="flex items-center justify-center bg-gradient-to-br from-emerald-soft/50 to-white p-6">
                    <div class="a-rise w-full max-w-[16rem] rounded-2xl bg-white p-4 shadow-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-lg" style="background: {{ $bColor }};">{!! $brandMark !!}</span>
                                <div><div class="text-xs font-bold text-ink">{{ $bName }}</div><div class="text-[10px] text-muted">Loyalty card</div></div>
                            </div>
                            <svg class="h-5 w-auto" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $pinPath }}"/></svg>
                        </div>
                        <div class="mt-4 grid grid-cols-5 gap-2">
                            @for ($s = 1; $s <= 10; $s++)
                                <div class="{{ $s <= 7 ? 'a-pop' : '' }} flex aspect-square items-center justify-center rounded-full {{ $s <= 7 ? 'bg-emerald text-white' : 'border-2 border-dashed border-hair text-hair' }}" @if($s <= 7) style="animation-delay: {{ 0.15 * $s }}s" @endif>
                                    @if ($s <= 7)<svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>@else<span class="text-[11px] font-bold">{{ $s }}</span>@endif
                                </div>
                            @endfor
                        </div>
                        <div class="mt-4 rounded-xl bg-emerald-soft/60 px-3 py-2 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-emerald">3 to go</div>
                            <div class="text-xs font-bold text-ink">Next reward: a free coffee on us</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 5: Market to your customers + privacy ============ --}}
            <section x-show="slide === 5" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0 grid grid-rows-[auto_1fr]">
                <div class="p-7 sm:p-9">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald">Step 4</span>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Bring your customers back</h2>
                    <p class="mt-2 max-w-md text-sm leading-relaxed text-muted">
                        Send a branded email, SMS or push anytime. Your customers' contact details stay protected - we never sell or share them, and messages go out through locolie, so the relationship stays yours.
                    </p>
                </div>
                <div class="flex items-center justify-center bg-gradient-to-br from-emerald-soft/50 to-white p-6">
                    <div class="a-float relative w-full max-w-[12.5rem] overflow-hidden rounded-[2rem] border-[10px] border-[#111] bg-cover bg-center shadow-2xl" style="background-image:url('{{ $imgOwner }}'); aspect-ratio: 9 / 16;">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/30 to-black/60"></div>
                        <div class="absolute left-0 right-0 top-0 flex items-center justify-center bg-black/30 py-1.5"><span class="h-3 w-12 rounded-full bg-[#0a0a0a]"></span></div>
                        <div class="absolute inset-x-3 top-10 space-y-2">
                            <div class="a-rise rounded-2xl bg-white/95 p-2.5 shadow-lg backdrop-blur" style="animation-delay:.2s">
                                <div class="flex items-center gap-1.5">
                                    <span class="flex h-5 w-5 items-center justify-center overflow-hidden rounded" style="background: {{ $bColor }};">{!! $brandMark !!}</span>
                                    <span class="text-[10px] font-bold text-ink">{{ \Illuminate\Support\Str::limit($bName, 16) }}</span>
                                    <span class="ml-auto text-[9px] text-muted">now</span>
                                </div>
                                <p class="mt-1 text-[10px] leading-snug text-ink/80">New autumn menu just dropped - come and try it this week.</p>
                            </div>
                            <div class="a-rise rounded-2xl bg-white/85 p-2.5 shadow backdrop-blur" style="animation-delay:.5s">
                                <div class="flex items-center gap-1.5">
                                    <span class="flex h-5 w-5 items-center justify-center rounded bg-emerald text-[9px] font-extrabold text-white">@</span>
                                    <span class="text-[10px] font-bold text-ink">Email · branded</span>
                                </div>
                                <p class="mt-1 text-[10px] leading-snug text-ink/70">You are 3 stamps from a free coffee.</p>
                            </div>
                        </div>
                        <div class="absolute inset-x-3 bottom-3 flex items-center gap-2 rounded-xl border border-white/30 bg-white/85 px-2.5 py-1.5 backdrop-blur">
                            <svg class="h-3.5 w-3.5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <span class="text-[9px] font-semibold leading-tight text-ink/80">Contact details protected. Never sold or shared.</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 6: Your dashboard + go live ============ --}}
            <section x-show="slide === 6" x-cloak x-transition.opacity.duration.300ms class="hero-grid absolute inset-0 flex flex-col justify-between p-7 sm:p-9">
                <div>
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald">Your dashboard</span>
                    <h2 class="mt-3 text-2xl font-extrabold tracking-tight sm:text-3xl">Go live, then watch it work</h2>
                    {{-- Retailer's OWN screen: simple KPI strip --}}
                    <div class="mt-4 grid grid-cols-3 gap-2.5">
                        @foreach ([['128', 'Customers'], ['46', 'Loyalty stamps'], ['62%', 'Email opens']] as $i => $kpi)
                            <div class="a-rise rounded-xl border border-hair bg-white p-3 shadow-sm" style="animation-delay: {{ 0.12 * $i }}s">
                                <div class="text-xl font-extrabold text-ink">{{ $kpi[0] }}</div>
                                <div class="text-[10px] font-semibold uppercase tracking-wider text-muted">{{ $kpi[1] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-muted">Who is visiting, stamps collected, and how your messages land. Clear numbers, no jargon - all in your retailer dashboard.</p>
                </div>
                <div class="flex items-end justify-between gap-4">
                    <div>
                        <a href="{{ $signupUrl }}" class="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-3 text-sm font-semibold text-white transition hover:bg-emerald">
                            Sign up your shop
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <p class="mt-2 text-xs text-muted">Free listing, always. Priority placement from £{{ $llFeaturedPrice }}/mo.</p>
                    </div>
                    <div class="shrink-0 text-center">
                        <div class="qr-svg mx-auto h-24 w-24 rounded-xl border border-hair bg-white p-1.5 shadow-sm sm:h-28 sm:w-28">{!! $signupQr !!}</div>
                        <div class="mt-1.5 text-[11px] font-bold text-ink">Scan to sign up</div>
                    </div>
                </div>
            </section>

        </div>

        {{-- Nav controls + dots --}}
        <div class="mt-4 flex w-full max-w-3xl items-center justify-between">
            <button type="button" @click="prev()" :disabled="slide === 1"
                class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-30">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Back
            </button>
            <div class="flex items-center gap-2">
                <template x-for="n in total" :key="n">
                    <button type="button" @click="go(n)" :class="slide === n ? 'w-7 bg-emerald' : 'w-2.5 bg-white/25 hover:bg-white/45'" class="h-2.5 rounded-full transition-all" :aria-label="'Go to slide ' + n"></button>
                </template>
            </div>
            <button type="button" @click="next()" x-show="slide < total"
                class="inline-flex items-center gap-1.5 rounded-full bg-emerald px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald/90">
                Next
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
            <a href="{{ $signupUrl }}" x-show="slide === total" x-cloak
                class="inline-flex items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-emerald hover:text-white">
                Get started
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
        <p class="mt-3 text-center text-xs text-white/40">Use the arrow keys, or click. Print to a PDF anytime with the button up top.</p>
    </div>
</div>

{{-- =================================================================== --}}
{{-- PRINT VERSION (square slides, one per page, QR + visuals kept)       --}}
{{-- =================================================================== --}}
<div class="deck-print bg-white text-ink">
    @php
        $printSlides = [
            ['Welcome to locolie', 'Back your high street. Own your customers.', 'locolie is the local-business platform for '.$llCity.'. Get found by nearby shoppers, run a free loyalty scheme, and bring your own customers back - all in one place.', ['A free listing shoppers actually use', 'A loyalty scheme that costs you nothing', 'Email, SMS and push to your own customers'], 'qr'],
            ['Step 1 - Get found', 'Claim your shop and get found', 'Add your photos, hours and story in minutes. You appear in search, on the map and on your own '.$llCity.' page - so nearby shoppers find you, not just the chains.', ['Live in the '.$llCity.' directory and map', 'Your own shop page shoppers can share', 'Verified, independents-only listing'], 'listing'],
            ['Step 2 - How customers join', 'One scan builds your customer list', 'Put your locolie poster on the counter. A customer scans it, opts in on a branded page, and joins your loyalty scheme and marketing list in seconds.', ['Scan the poster in store', 'Opt in on your branded page', 'Straight onto your customer list'], 'qr'],
            ['Step 3 - Loyalty (free)', 'A loyalty scheme that costs nothing', 'A digital stamp card on the customer\'s phone. No plastic, no app to build, no fees. You set the reward - buy nine, get the tenth free, or whatever suits.', ['Digital stamp card, nothing to print', 'You set the reward', 'Built into your free listing'], 'stamp'],
            ['Step 4 - Market to your customers', 'Bring your customers back', 'Send a branded email, SMS or push anytime. Your customers\' contact details stay protected - we never sell or share them, and messages go out through locolie, so the relationship stays yours.', ['Branded email, SMS and push', 'Contact details stay protected', 'The relationship stays yours'], 'push'],
            ['Your dashboard', 'Go live, then watch it work', 'Who is visiting, loyalty stamps collected and how your messages land. Clear numbers, no jargon. Free listing, always. Priority placement from £'.$llFeaturedPrice.'/mo.', ['Listing live and discoverable', 'Loyalty running automatically', 'Reports and reach in one dashboard'], 'qr'],
        ];
    @endphp
    @foreach ($printSlides as $i => $s)
        <section class="print-slide mx-auto my-6 flex aspect-square max-w-[170mm] flex-col rounded-2xl border border-hair bg-white p-9">
            <div class="flex items-center justify-between border-b border-hair pb-3">
                <span class="text-lg text-ink">{!! $wordmark !!}</span>
                <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Slide {{ $i + 1 }} of 6</span>
            </div>
            <p class="mt-5 text-xs font-bold uppercase tracking-wider text-emerald">{{ $s[0] }}</p>
            <h2 class="mt-1.5 text-2xl font-extrabold tracking-tight text-ink">{{ $s[1] }}</h2>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-muted">{{ $s[2] }}</p>
            <div class="mt-5 flex flex-1 items-center gap-8">
                <ul class="flex-1 space-y-2.5">
                    @foreach ($s[3] as $point)
                        <li class="flex items-start gap-2.5 text-sm">
                            <svg class="mt-0.5 h-4.5 w-4.5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <span class="text-ink/80">{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
                {{-- Keep a visual on the page so the PDF is not text-only --}}
                <div class="shrink-0">
                    @if ($s[4] === 'qr')
                        <div class="text-center">
                            <div class="qr-svg h-36 w-36 rounded-xl border border-hair p-2">{!! $signupQr !!}</div>
                            <div class="mt-2 text-[11px] font-bold text-ink">Scan to sign up</div>
                        </div>
                    @elseif ($s[4] === 'stamp')
                        <div class="w-40 rounded-2xl border border-hair bg-white p-3 shadow-sm">
                            <div class="grid grid-cols-5 gap-1.5">
                                @for ($x = 1; $x <= 10; $x++)
                                    <div class="flex aspect-square items-center justify-center rounded-full {{ $x <= 7 ? 'bg-emerald text-white' : 'border-2 border-dashed border-hair text-hair' }}">@if ($x <= 7)<svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M20 6 9 17l-5-5"/></svg>@else<span class="text-[9px] font-bold">{{ $x }}</span>@endif</div>
                                @endfor
                            </div>
                            <div class="mt-2 rounded-lg bg-emerald-soft/60 py-1 text-center text-[10px] font-bold text-emerald">3 to go: free coffee</div>
                        </div>
                    @elseif ($s[4] === 'push')
                        <div class="w-40 space-y-2 rounded-2xl border border-hair bg-[#fafafa] p-3">
                            <div class="rounded-xl bg-white p-2.5 shadow-sm">
                                <div class="flex items-center gap-1.5"><span class="flex h-5 w-5 items-center justify-center overflow-hidden rounded" style="background: {{ $bColor }};">{!! $brandMark !!}</span><span class="text-[10px] font-bold text-ink">{{ \Illuminate\Support\Str::limit($bName, 14) }}</span></div>
                                <p class="mt-1 text-[10px] leading-snug text-ink/70">New autumn menu just dropped.</p>
                            </div>
                            <div class="rounded-xl bg-white p-2.5 shadow-sm"><span class="text-[10px] font-bold text-ink">Email · branded</span><p class="mt-0.5 text-[10px] text-ink/70">3 stamps from a free coffee.</p></div>
                        </div>
                    @else
                        <div class="w-40 overflow-hidden rounded-2xl border border-hair shadow-sm">
                            <div class="h-20" style="background: linear-gradient(135deg, {{ $bColor }}, #0a0a0a);">@if ($bPhoto)<img src="{{ $bPhoto }}" alt="" onerror="this.remove()" class="h-full w-full object-cover">@endif</div>
                            <div class="p-2.5"><div class="text-xs font-bold text-ink">{{ \Illuminate\Support\Str::limit($bName, 16) }}</div><div class="text-[10px] text-muted">{{ $bCategory }} · {{ $bCity }}</div></div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endforeach
</div>

</body>
</html>
