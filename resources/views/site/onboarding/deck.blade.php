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

    // Marketing photography reused from the real site assets.
    $imgCafe   = asset('images/marketing/indie-cafe.jpg');
    $imgOwner  = asset('images/marketing/cafe-owner.jpg');
    $imgBarber = asset('images/marketing/barber-owner.jpg');
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to locolie - retailer onboarding</title>
    <meta name="description" content="A quick walkthrough of how locolie works for your shop: claim your listing, run a free loyalty scheme, and market to your own customers. Launching in {{ $llPlace }}.">
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

        /* Each slide is a clean 16:9-ish stage that fills the viewport on screen. */
        .deck-stage { aspect-ratio: 16 / 9; }
        a:focus-visible, button:focus-visible { outline: 2px solid var(--emerald); outline-offset: 3px; border-radius: 6px; }

        /* ---- Print: one slide per page, no chrome, clean full pages ---- */
        @media print {
            @page { size: A4 landscape; margin: 12mm; }
            html, body { background: #ffffff !important; }
            .no-print { display: none !important; }
            .deck-screen { display: none !important; }
            .deck-print { display: block !important; }
            .print-slide {
                break-after: page;
                page-break-after: always;
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
            }
            .print-slide:last-child { break-after: auto; page-break-after: auto; }
        }
        .deck-print { display: none; }
    </style>
</head>
<body class="antialiased">

{{-- =================================================================== --}}
{{-- ON-SCREEN DECK (interactive, one slide visible at a time)            --}}
{{-- =================================================================== --}}
<div class="deck-screen no-print min-h-screen bg-[#0a0a0a] text-white"
     x-data="{
        slide: 1,
        total: 5,
        next() { if (this.slide < this.total) this.slide++; },
        prev() { if (this.slide > 1) this.slide--; },
        go(n) { this.slide = n; },
     }"
     @keydown.window.arrow-right="next()"
     @keydown.window.arrow-left="prev()"
     @keydown.window.space.prevent="next()">

    {{-- ── Top bar: brand + counter + print ── --}}
    <header class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 sm:px-6">
        <a href="/business" class="flex items-center gap-1.5 text-lg">
            <span class="wordmark text-white">L<svg class="mx-[-0.02em] inline-block h-[0.92em] w-auto align-[-0.12em]" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $pinPath }}"/></svg>colie</span>
        </a>
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

    {{-- ── Stage ── --}}
    <div class="mx-auto max-w-6xl px-4 pb-4 sm:px-6">
        <div class="deck-stage relative w-full overflow-hidden rounded-3xl border border-white/10 bg-white text-ink shadow-2xl">

            {{-- ============ SLIDE 1: Welcome ============ --}}
            <section x-show="slide === 1" x-transition.opacity.duration.300ms class="hero-grid absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald">Welcome to locolie</span>
                    <h1 class="mt-5 text-3xl font-extrabold leading-[1.05] tracking-tight sm:text-4xl lg:text-5xl">
                        Back your high street. <span class="gradient-text">Own your customers.</span>
                    </h1>
                    <p class="mt-5 max-w-md text-base leading-relaxed text-muted sm:text-lg">
                        locolie is the local-business platform for {{ $llCity }}. Shoppers discover the independents near them, and you get a free listing, a loyalty scheme and a way to bring your customers back - all in one place.
                    </p>
                    <ul class="mt-6 space-y-2.5 text-sm">
                        @foreach ([
                            'A free, beautiful listing shoppers actually use',
                            'A built-in loyalty scheme that costs you nothing',
                            'Marketing to your own customers, by email, SMS and push',
                        ] as $point)
                            <li class="flex items-start gap-2.5">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                <span class="text-ink/80">{{ $point }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mt-7 text-xs font-semibold text-muted">Five quick slides. Use the arrows, or print this to a PDF to keep.</p>
                </div>
                <div class="relative hidden md:block">
                    <img src="{{ $imgCafe }}" alt="An independent cafe on the high street" class="h-full w-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-tr from-ink/40 to-transparent"></div>
                    <div class="absolute bottom-6 left-6 right-6 rounded-2xl border border-white/30 bg-white/85 p-4 backdrop-blur">
                        <div class="flex items-center gap-3">
                            <x-seal variant="light" class="h-12 w-12" />
                            <div>
                                <div class="text-sm font-extrabold text-ink">Now backing the indies in {{ $llCity }}</div>
                                <div class="text-xs text-muted">Independent shops, pubs and makers only - always.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 2: Claim & set up ============ --}}
            <section x-show="slide === 2" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center bg-[#fafafa] p-8 sm:p-12 lg:p-16">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald">Step 1</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Claim and set up your shop</h2>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-muted">
                        Find your business or add it in a couple of minutes. Add your photos, opening hours, category and a short story. That is your free listing live in the {{ $llCity }} directory.
                    </p>
                    <ul class="mt-6 space-y-3 text-sm">
                        @foreach ([
                            ['Add your details', 'Name, category, address, hours and a few good photos.'],
                            ['Tell your story', 'A short blurb so shoppers know what makes you, you.'],
                            ['Go on the map', 'You appear in search, on the map and in your category.'],
                        ] as $i => $row)
                            <li class="flex items-start gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-ink text-xs font-extrabold text-white">{{ $i + 1 }}</span>
                                <span class="text-ink/80"><span class="font-semibold text-ink">{{ $row[0] }}.</span> {{ $row[1] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="flex items-center justify-center bg-gradient-to-br from-emerald-soft/60 to-white p-8 sm:p-12">
                    {{-- Listing card mock, using a real business --}}
                    <div class="w-full max-w-xs overflow-hidden rounded-2xl border border-hair bg-white shadow-xl">
                        <div class="relative h-40 overflow-hidden" style="background: linear-gradient(135deg, {{ $bColor }}, #0a0a0a);">
                            @if ($bPhoto)
                                <img src="{{ $bPhoto }}" alt="{{ $bName }} in {{ $bCity }}" loading="lazy" onerror="this.remove()" class="h-full w-full object-cover">
                            @endif
                            <span class="absolute left-3 top-3 rounded-full bg-white/90 px-2.5 py-1 text-xs font-bold text-emerald">Independent</span>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold text-white" style="background: {{ $bColor }};">{{ $bInitials }}</span>
                                <div class="min-w-0">
                                    <h3 class="truncate text-lg font-bold text-ink">{{ $bName }}</h3>
                                    <div class="truncate text-sm text-muted">{{ $bCategory }} <span class="text-hair">·</span> {{ $bCity }}</div>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center gap-1 text-sm text-muted">
                                <span class="text-amber-500">★</span> {{ $bRating }} <span class="text-hair">·</span> Verified local
                            </div>
                            <div class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full bg-ink py-2.5 text-sm font-semibold text-white">View shop</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 3: Loyalty scheme (free) ============ --}}
            <section x-show="slide === 3" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald">Step 2 · Free</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Run a loyalty scheme that costs nothing</h2>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-muted">
                        Reward repeat custom with a digital stamp card. No plastic, no app to make, no fees. Customers collect stamps on their phone and you decide the reward.
                    </p>
                    <ul class="mt-6 space-y-3 text-sm">
                        @foreach ([
                            ['Digital stamp card', 'A clean card on the customer\'s phone - nothing to print or lose.'],
                            ['You set the reward', 'Buy nine, get the tenth free, or whatever suits your shop.'],
                            ['Built right in', 'It is part of your free listing. No extra tools, no extra cost.'],
                        ] as $row)
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                <span class="text-ink/80"><span class="font-semibold text-ink">{{ $row[0] }}.</span> {{ $row[1] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="flex items-center justify-center bg-gradient-to-br from-ink to-[#10241c] p-8 sm:p-12">
                    {{-- Stamp card mock --}}
                    <div class="w-full max-w-xs rounded-2xl bg-white p-5 shadow-2xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg text-xs font-extrabold text-white" style="background: {{ $bColor }};">{{ $bInitials }}</span>
                                <div>
                                    <div class="text-sm font-bold text-ink">{{ $bName }}</div>
                                    <div class="text-[11px] text-muted">Loyalty card</div>
                                </div>
                            </div>
                            <svg class="h-5 w-auto" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $pinPath }}"/></svg>
                        </div>
                        <div class="mt-5 grid grid-cols-5 gap-2.5">
                            @for ($s = 1; $s <= 10; $s++)
                                <div class="flex aspect-square items-center justify-center rounded-full {{ $s <= 7 ? 'bg-emerald text-white' : 'border-2 border-dashed border-hair text-hair' }}">
                                    @if ($s <= 7)
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    @else
                                        <span class="text-xs font-bold">{{ $s }}</span>
                                    @endif
                                </div>
                            @endfor
                        </div>
                        <div class="mt-5 rounded-xl bg-emerald-soft/60 px-4 py-3 text-center">
                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-emerald">3 to go</div>
                            <div class="mt-0.5 text-sm font-bold text-ink">Next reward: a free coffee on us</div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 4: Marketing + privacy promise ============ --}}
            <section x-show="slide === 4" x-cloak x-transition.opacity.duration.300ms class="absolute inset-0 grid grid-cols-1 md:grid-cols-2">
                <div class="flex flex-col justify-center bg-[#fafafa] p-8 sm:p-12 lg:p-16">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald">Step 3</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Bring your customers back</h2>
                    <p class="mt-4 max-w-md text-base leading-relaxed text-muted">
                        Every visit builds a customer list that is yours. Send a branded message by email, SMS or push - a new season, an event, a reason to pop in.
                    </p>
                    <ul class="mt-5 space-y-2.5 text-sm">
                        @foreach ([
                            ['Email', 'M4 4h16v16H4z M22 6l-10 7L2 6'],
                            ['SMS', 'M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z'],
                            ['Push', 'M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9 M13.7 21a2 2 0 0 1-3.4 0'],
                        ] as $ch)
                            <li class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm">
                                    <svg class="h-4.5 w-4.5 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $ch[1] }}"/></svg>
                                </span>
                                <span class="font-semibold text-ink">{{ $ch[0] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-6 flex items-start gap-3 rounded-2xl border border-emerald/30 bg-emerald-soft/50 p-4">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        <p class="text-xs leading-relaxed text-ink/80"><span class="font-bold text-ink">Our privacy promise.</span> Your customers' contact details stay protected. We never sell or share them, and we never pass them to other businesses. Messages go out through locolie, so the relationship stays yours.</p>
                    </div>
                </div>
                <div class="flex items-center justify-center bg-gradient-to-br from-emerald-soft/60 to-white p-8 sm:p-12">
                    {{-- Phone push-notification mock --}}
                    <div class="relative w-full max-w-[15rem] overflow-hidden rounded-[2.25rem] border-[11px] border-[#111] bg-cover bg-center shadow-2xl" style="background-image:url('{{ $imgOwner }}'); aspect-ratio: 9 / 17;">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/10 via-black/30 to-black/60"></div>
                        <div class="absolute left-0 right-0 top-0 flex items-center justify-center bg-black/30 py-1.5"><span class="h-3.5 w-14 rounded-full bg-[#0a0a0a]"></span></div>
                        <div class="absolute inset-x-3 top-12 space-y-2.5">
                            <div class="rounded-2xl bg-white/95 p-3 shadow-lg backdrop-blur">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md text-[10px] font-extrabold text-white" style="background: {{ $bColor }};">{{ $bInitials }}</span>
                                    <span class="text-[11px] font-bold text-ink">{{ $bName }}</span>
                                    <span class="ml-auto text-[10px] text-muted">now</span>
                                </div>
                                <p class="mt-1.5 text-[11px] leading-snug text-ink/80">New autumn menu just dropped - come and try it this week. See you soon.</p>
                            </div>
                            <div class="rounded-2xl bg-white/85 p-3 shadow backdrop-blur">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald text-[10px] font-extrabold text-white">@</span>
                                    <span class="text-[11px] font-bold text-ink">Email · branded</span>
                                </div>
                                <p class="mt-1.5 text-[11px] leading-snug text-ink/70">You are 3 stamps from a free coffee.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ============ SLIDE 5: Go live / reports / next steps ============ --}}
            <section x-show="slide === 5" x-cloak x-transition.opacity.duration.300ms class="hero-grid absolute inset-0 flex flex-col justify-center p-8 sm:p-12 lg:p-16">
                <div class="mx-auto w-full max-w-4xl">
                    <span class="inline-flex w-fit items-center gap-2 rounded-full bg-emerald-soft px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald">You are ready</span>
                    <h2 class="mt-5 text-3xl font-extrabold tracking-tight sm:text-4xl">Go live, then watch it work</h2>
                    <p class="mt-4 max-w-xl text-base leading-relaxed text-muted">
                        Once your listing is live you get a simple dashboard: who is visiting, loyalty stamps collected, and how your messages are landing. Clear numbers, no jargon.
                    </p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-3">
                        @foreach ([
                            ['Listing live', 'Your shop is discoverable across the '.$llCity.' directory and map.'],
                            ['Loyalty running', 'Stamps collect automatically. Customers come back for the reward.'],
                            ['Reports + reach', 'See visits and loyalty at a glance, and message your customers anytime.'],
                        ] as $i => $row)
                            <div class="rounded-2xl border border-hair bg-white p-5 shadow-sm">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-soft text-base font-extrabold text-emerald">{{ $i + 1 }}</div>
                                <h3 class="mt-3 font-bold text-ink">{{ $row[0] }}</h3>
                                <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $row[1] }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="/business" class="inline-flex items-center gap-2 rounded-full bg-ink px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald">
                            Open your dashboard
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                        </a>
                        <span class="text-sm text-muted">Free listing, always. Priority placement from £{{ $llFeaturedPrice }}/mo.</span>
                    </div>
                </div>
            </section>

        </div>

        {{-- ── Nav controls + dots ── --}}
        <div class="mt-4 flex items-center justify-between">
            <button type="button" @click="prev()" :disabled="slide === 1"
                class="inline-flex items-center gap-1.5 rounded-full border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10 disabled:cursor-not-allowed disabled:opacity-30">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                Back
            </button>

            <div class="flex items-center gap-2">
                <template x-for="n in total" :key="n">
                    <button type="button" @click="go(n)"
                        :class="slide === n ? 'w-7 bg-emerald' : 'w-2.5 bg-white/25 hover:bg-white/45'"
                        class="h-2.5 rounded-full transition-all" :aria-label="'Go to slide ' + n"></button>
                </template>
            </div>

            <button type="button" @click="next()" x-show="slide < total"
                class="inline-flex items-center gap-1.5 rounded-full bg-emerald px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald/90">
                Next
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
            <a href="/business" x-show="slide === total" x-cloak
                class="inline-flex items-center gap-1.5 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-ink transition hover:bg-emerald hover:text-white">
                Get started
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
        <p class="mt-3 text-center text-xs text-white/40">Use the left and right arrow keys, or click. Print this to a PDF anytime with the button up top.</p>
    </div>
</div>

{{-- =================================================================== --}}
{{-- PRINT VERSION (all slides stacked, one clean page each)              --}}
{{-- =================================================================== --}}
<div class="deck-print bg-white text-ink">
    @php
        $printSlides = [
            ['Welcome to locolie', 'Back your high street. Own your customers.', 'locolie is the local-business platform for '.$llCity.'. Shoppers discover the independents near them, and you get a free listing, a loyalty scheme and a way to bring your customers back - all in one place.', [
                'A free, beautiful listing shoppers actually use',
                'A built-in loyalty scheme that costs you nothing',
                'Marketing to your own customers, by email, SMS and push',
            ]],
            ['Step 1 - Claim and set up your shop', 'Your free listing, live in minutes', 'Find your business or add it in a couple of minutes. Add your photos, opening hours, category and a short story. That is your free listing live in the '.$llCity.' directory.', [
                'Add your details: name, category, address, hours and photos',
                'Tell your story so shoppers know what makes you, you',
                'Go on the map: you appear in search, on the map and in your category',
            ]],
            ['Step 2 - Loyalty scheme (free)', 'Reward repeat custom, at no cost', 'Reward repeat custom with a digital stamp card. No plastic, no app to make, no fees. Customers collect stamps on their phone and you decide the reward.', [
                'A clean digital stamp card on the customer\'s phone',
                'You set the reward - buy nine, get the tenth free, or whatever suits',
                'Built into your free listing: no extra tools, no extra cost',
            ]],
            ['Step 3 - Market to your customers', 'Bring your customers back', 'Every visit builds a customer list that is yours. Send a branded message by email, SMS or push. Our privacy promise: your customers\' contact details stay protected. We never sell or share them, and we never pass them to other businesses.', [
                'Branded email, SMS and push - all from locolie',
                'Contact details stay protected and are never sold or shared',
                'The relationship stays yours, not a platform\'s or a chain\'s',
            ]],
            ['Step 4 - Go live and watch it work', 'Clear numbers, no jargon', 'Once your listing is live you get a simple dashboard: who is visiting, loyalty stamps collected, and how your messages are landing. Free listing, always. Priority placement from £'.$llFeaturedPrice.'/mo.', [
                'Listing live and discoverable across the '.$llCity.' directory and map',
                'Loyalty running automatically - customers return for the reward',
                'Reports and reach: see visits and loyalty, message customers anytime',
            ]],
        ];
    @endphp
    @foreach ($printSlides as $i => $s)
        <section class="print-slide mx-auto my-6 max-w-3xl rounded-2xl border border-hair bg-white p-10">
            <div class="flex items-center justify-between border-b border-hair pb-4">
                <span class="wordmark text-xl text-ink">L<svg class="mx-[-0.02em] inline-block h-[0.92em] w-auto align-[-0.12em]" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $pinPath }}"/></svg>colie</span>
                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Slide {{ $i + 1 }} of 5</span>
            </div>
            <p class="mt-6 text-xs font-bold uppercase tracking-wider text-emerald">{{ $s[0] }}</p>
            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-ink">{{ $s[1] }}</h2>
            <p class="mt-4 max-w-xl text-base leading-relaxed text-muted">{{ $s[2] }}</p>
            <ul class="mt-6 space-y-3">
                @foreach ($s[3] as $point)
                    <li class="flex items-start gap-3 text-sm">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                        <span class="text-ink/80">{{ $point }}</span>
                    </li>
                @endforeach
            </ul>
            @if ($i === 4)
                <div class="mt-8 rounded-xl bg-emerald-soft/50 px-5 py-4 text-sm font-semibold text-emerald">Ready to start? Open your dashboard at locolie and claim your shop.</div>
            @endif
        </section>
    @endforeach
</div>

</body>
</html>
