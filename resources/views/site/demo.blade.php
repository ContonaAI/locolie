@extends('site.layout')
@section('title', 'How locolie offers work - Demo')
@section('meta_description', 'See exactly how locolie offers work, from discovering a local indie to claiming a deal and redeeming it at the till. An illustrated walkthrough using sample data.')

@push('head')
<link rel="canonical" href="{{ url('/demo') }}">
@endpush

@section('content')

@php
    // ---- Brand pin (matches the rest of the site) ----
    $pinPath = 'M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z';

    // ---- Concrete values from the real business + sample offer ----
    $bName     = $business->name;
    $bCity     = $business->city ?? $llCity;
    $bCategory = $business->category?->name ?? 'Independent';
    $bRating   = number_format((float) ($business->rating ?? 4.8), 1);
    $bReviews  = (int) ($business->reviews_count ?? 0);
    $bPhoto    = $business->photos[0] ?? null;
    $bColor    = $business->brandColor();
    $bInitials = $business->brandInitials();

    // ---- Deterministic decorative QR mock (no network, faux pattern is fine for a demo) ----
    $n = 21; $c = 96 / $n; $qo = 2; $mods = '';
    for ($r = 0; $r < $n; $r++) {
        for ($k = 0; $k < $n; $k++) {
            $fin = (($r < 7 && $k < 7) || ($r < 7 && $k >= $n - 7) || ($r >= $n - 7 && $k < 7));
            if ($fin) continue;
            if (((($r + 1) * ($k + 2) + $r * 3 + $k * 7) % 5) < 2) {
                $mods .= '<rect x="'.round($qo + $k * $c, 2).'" y="'.round($qo + $r * $c, 2).'" width="'.round($c + 0.25, 2).'" height="'.round($c + 0.25, 2).'"/>';
            }
        }
    }
    $fp = function ($gr, $gk) use ($c, $qo) {
        $x = $qo + $gk * $c; $y = $qo + $gr * $c; $s = 7 * $c;
        return '<rect x="'.round($x, 2).'" y="'.round($y, 2).'" width="'.round($s, 2).'" height="'.round($s, 2).'" rx="2.5" fill="#0a0a0a"/>'
            .'<rect x="'.round($x + $c, 2).'" y="'.round($y + $c, 2).'" width="'.round($s - 2 * $c, 2).'" height="'.round($s - 2 * $c, 2).'" rx="1.5" fill="#fff"/>'
            .'<rect x="'.round($x + 2 * $c, 2).'" y="'.round($y + 2 * $c, 2).'" width="'.round($s - 4 * $c, 2).'" height="'.round($s - 4 * $c, 2).'" rx="1" fill="#059669"/>';
    };
    $qr = '<svg viewBox="0 0 100 100" class="h-full w-full" aria-hidden="true"><rect width="100" height="100" rx="8" fill="#fff"/><g fill="#0a0a0a">'.$mods.'</g>'.$fp(0, 0).$fp(0, $n - 7).$fp($n - 7, 0).'</svg>';
@endphp

{{-- ============================================================ HERO --}}
<section class="relative overflow-hidden hero-grid">
    <div class="mesh" aria-hidden="true"><i class="b1"></i><i class="b2"></i><i class="b3"></i></div>
    <div class="relative mx-auto max-w-4xl px-5 pb-14 pt-32 text-center sm:px-6 lg:pt-40">
        <span class="inline-flex items-center gap-2 rounded-full border border-hair bg-white px-3.5 py-1.5 text-xs font-semibold uppercase tracking-wider text-emerald shadow-sm">Demo</span>
        <h1 class="mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight text-balance sm:text-5xl lg:text-6xl">
            How <span class="text-emerald lowercase">locolie</span> offers work
        </h1>
        <p class="mx-auto mt-5 max-w-xl text-lg leading-relaxed text-muted">
            An illustrated walkthrough of the offer and redemption experience, from spotting a local indie to saving at the till. Follow the four steps below.
        </p>
        <p class="mx-auto mt-6 inline-flex items-center gap-2 rounded-full bg-emerald-soft px-4 py-2 text-xs font-semibold text-emerald">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
            These are sample offers. Real ones are set by each business.
        </p>
    </div>
</section>

{{-- ============================================================ THE 4-STEP JOURNEY (interactive stepper) --}}
<section class="py-16 sm:py-24">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">The journey</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Four steps, from discover to redeemed.</p>
            <p class="mt-4 text-muted">Step through it below, or read all four at a glance. Either way, this is exactly how a real offer flows.</p>
        </div>

        @php
            $steps = [
                ['n' => '1', 'k' => 'Discover', 'sub' => 'Find a local indie you love in the feed or on the map.'],
                ['n' => '2', 'k' => 'Claim the offer', 'sub' => 'Tap to grab the deal. It saves straight to your phone.'],
                ['n' => '3', 'k' => 'Show the code', 'sub' => 'Flash your code and QR at the till to save.'],
                ['n' => '4', 'k' => 'Redeemed', 'sub' => 'Enjoy the saving. The visit lands in the shop\'s CRM.'],
            ];
        @endphp

        <div x-data="{ step: 1, total: 4 }" class="mt-14">
            {{-- Step dots / tabs --}}
            <div class="mx-auto flex max-w-3xl flex-wrap items-center justify-center gap-2 sm:gap-3">
                @foreach ($steps as $i => $s)
                    <button type="button" @click="step = {{ $i + 1 }}"
                        :class="step === {{ $i + 1 }} ? 'border-emerald bg-emerald text-white shadow-lg shadow-emerald/20' : 'border-hair bg-white text-muted hover:border-emerald hover:text-ink'"
                        class="flex items-center gap-2 rounded-full border px-3.5 py-2 text-sm font-semibold transition">
                        <span :class="step === {{ $i + 1 }} ? 'bg-white/20 text-white' : 'bg-emerald-soft text-emerald'" class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-extrabold">{{ $s['n'] }}</span>
                        <span class="hidden sm:inline">{{ $s['k'] }}</span>
                    </button>
                @endforeach
            </div>

            {{-- Stage --}}
            <div class="mt-10 grid items-center gap-10 lg:grid-cols-2">
                {{-- LEFT: the visual that swaps per step --}}
                <div class="relative flex min-h-[520px] items-center justify-center">

                    {{-- STEP 1 & 2: business card (step 2 reveals the offer) --}}
                    <template x-if="step === 1 || step === 2">
                        <div class="w-full max-w-sm">
                            <div class="card-hover overflow-hidden rounded-card border border-hair bg-white shadow-sm">
                                <div class="relative h-48 overflow-hidden bg-[#e2e8f0]">
                                    @if ($bPhoto)
                                        <div class="absolute inset-0 flex items-center justify-center text-3xl font-extrabold text-white" style="background: linear-gradient(135deg, {{ $bColor }}, #0a0a0a);">{{ $bInitials }}</div>
                                        <img src="{{ $bPhoto }}" alt="{{ $bName }} in {{ $bCity }}" loading="lazy" decoding="async" onerror="this.remove()" class="relative h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-4xl font-extrabold text-white" style="background: linear-gradient(135deg, {{ $bColor }}, #0a0a0a);">{{ $bInitials }}</div>
                                    @endif
                                    {{-- Offer badge appears in step 2 --}}
                                    <span x-show="step === 2" x-cloak x-transition.scale class="absolute left-3 top-3 rounded-lg bg-emerald px-2.5 py-1 text-xs font-extrabold text-white shadow-md">{{ $offer->badge }}</span>
                                    <span x-show="step === 1" class="absolute left-3 top-3 rounded-full bg-emerald-soft px-2.5 py-1 text-xs font-bold text-emerald">Independent</span>
                                </div>
                                <div class="p-5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-extrabold text-white" style="background: {{ $bColor }};">{{ $bInitials }}</span>
                                        <div class="min-w-0">
                                            <h3 class="truncate text-lg font-bold text-ink">{{ $bName }}</h3>
                                            <div class="mt-0.5 truncate text-sm text-muted">{{ $bCategory }} <span class="text-hair">·</span> {{ $bCity }}</div>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex items-center gap-1 text-sm text-muted">
                                        <span class="text-amber-500">★</span> {{ $bRating }}
                                        @if ($bReviews)<span class="text-hair">·</span> {{ number_format($bReviews) }} reviews @endif
                                    </div>

                                    {{-- Step 2: the offer + CTA --}}
                                    <div x-show="step === 2" x-cloak x-transition>
                                        <div class="mt-4 rounded-xl border border-emerald/30 bg-emerald-soft/60 p-4">
                                            <div class="flex items-center gap-2">
                                                <span class="rounded-md bg-emerald px-2 py-0.5 text-xs font-extrabold text-white">{{ $offer->badge }}</span>
                                                <span class="text-sm font-bold text-ink">{{ $offer->title }}</span>
                                            </div>
                                            <p class="mt-2 text-sm leading-relaxed text-ink/70">{{ $offer->description }}</p>
                                        </div>
                                        <button type="button" @click="step = 3" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full bg-ink py-3 text-sm font-semibold text-white transition hover:bg-emerald">
                                            Get this offer
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 text-center text-xs text-muted">Sample listing for a real local business.</p>
                        </div>
                    </template>

                    {{-- STEP 3: phone showing the code + QR --}}
                    <template x-if="step === 3">
                        <div class="w-full max-w-xs">
                            <div class="relative mx-auto overflow-hidden rounded-[2.5rem] border-[12px] border-[#111] bg-[#eef1f4] shadow-[0_30px_70px_-25px_rgba(0,0,0,0.55)]">
                                <div class="flex items-center justify-center bg-black" style="height:22px;"><span class="h-[15px] w-16 rounded-full bg-[#0a0a0a] ring-1 ring-white/10"></span></div>
                                <div class="px-4 pb-6 pt-4">
                                    <div class="flex items-center justify-center gap-1.5 text-ink">
                                        <svg class="h-4 w-auto" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="{{ $pinPath }}"/></svg>
                                        <span class="text-base font-extrabold lowercase tracking-tight">locolie</span>
                                    </div>
                                    <div class="mt-4 rounded-2xl bg-white p-4 shadow-sm">
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xs font-extrabold text-white" style="background: {{ $bColor }};">{{ $bInitials }}</span>
                                            <div class="min-w-0">
                                                <div class="truncate text-sm font-bold text-ink">{{ $bName }}</div>
                                                <div class="truncate text-[11px] text-muted">{{ $bCategory }} · {{ $bCity }}</div>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex items-center gap-2">
                                            <span class="rounded-md bg-emerald px-2 py-0.5 text-xs font-extrabold text-white">{{ $offer->badge }}</span>
                                            <span class="text-sm font-bold text-ink">{{ $offer->title }}</span>
                                        </div>

                                        {{-- QR mock --}}
                                        <div class="mx-auto mt-4 h-32 w-32">{!! $qr !!}</div>

                                        {{-- Sample code --}}
                                        <div class="mt-4 rounded-xl border border-dashed border-emerald/40 bg-emerald-soft/50 px-3 py-2.5 text-center">
                                            <div class="text-[10px] font-bold uppercase tracking-[0.15em] text-emerald">Your code</div>
                                            <div class="mt-0.5 font-mono text-lg font-extrabold tracking-wider text-ink">{{ $sampleCode }}</div>
                                        </div>
                                        <p class="mt-3 text-[10px] leading-snug text-muted">{{ $offer->terms }}</p>
                                    </div>
                                    <div class="mt-3 text-center text-[10px] font-semibold text-muted">Show this at the till</div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- STEP 4: redeemed success --}}
                    <template x-if="step === 4">
                        <div class="w-full max-w-sm">
                            <div class="overflow-hidden rounded-card border border-emerald/30 bg-white p-8 text-center shadow-sm">
                                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-emerald-soft">
                                    <svg class="h-10 w-10 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </div>
                                <h3 class="mt-5 text-2xl font-extrabold text-ink">Redeemed - enjoy!</h3>
                                <p class="mt-2 text-sm text-muted">Your <span class="font-semibold text-ink">{{ $offer->badge }}</span> at {{ $bName }} is done. No printout, no fuss.</p>
                                <div class="mt-6 rounded-xl bg-[#f7f7f7] p-4 text-left">
                                    <div class="flex items-center gap-2 text-sm font-bold text-ink">
                                        <svg class="h-4 w-4 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/></svg>
                                        Captured for the retailer
                                    </div>
                                    <p class="mt-1.5 text-xs leading-relaxed text-muted">This visit lands in {{ $bName }}'s own CRM, so they can welcome you back with future offers by email and push.</p>
                                </div>
                            </div>
                            <p class="mt-3 text-center text-xs text-muted">Sample redemption shown for illustration.</p>
                        </div>
                    </template>
                </div>

                {{-- RIGHT: the explanation that swaps per step --}}
                <div class="lg:pl-6">
                    @foreach ($steps as $i => $s)
                        <div x-show="step === {{ $i + 1 }}" x-cloak x-transition>
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-ink text-base font-extrabold text-white">{{ $s['n'] }}</div>
                            <h3 class="mt-5 text-2xl font-extrabold tracking-tight text-ink">{{ $s['k'] }}</h3>
                            @if ($i === 0)
                                <p class="mt-3 text-lg leading-relaxed text-muted">Browse independents near you in the feed or on the map. Here's a real {{ strtolower($bCategory) }} in {{ $bCity }}, with its rating and category, exactly as shoppers see it.</p>
                            @elseif ($i === 1)
                                <p class="mt-3 text-lg leading-relaxed text-muted">When a business posts a deal, it shows right on their card. Tap <span class="font-semibold text-ink">Get this offer</span> and it saves to your phone. No printout, no voucher to lose.</p>
                            @elseif ($i === 2)
                                <p class="mt-3 text-lg leading-relaxed text-muted">At the till, open the offer and show your code and QR. The shop scans or types it in to confirm. The terms are always in plain sight before you redeem.</p>
                            @else
                                <p class="mt-3 text-lg leading-relaxed text-muted">Done. You saved on the spot, and the shop captured a real customer they own and can reach again. Loyalty that works for both sides.</p>
                            @endif

                            {{-- Prev / Next --}}
                            <div class="mt-8 flex items-center gap-3">
                                <button type="button" @click="step = Math.max(1, step - 1)" :disabled="step === 1"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-hair bg-white px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-ink disabled:cursor-not-allowed disabled:opacity-40">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
                                    Back
                                </button>
                                <button type="button" x-show="step < total" @click="step = Math.min(total, step + 1)"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-ink px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald">
                                    Next
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </button>
                                <button type="button" x-show="step === total" @click="step = 1"
                                    class="inline-flex items-center gap-1.5 rounded-full bg-emerald px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-ink">
                                    Watch again
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- At-a-glance 4-up (always rendered, no-JS friendly fallback summary) --}}
        <div class="mt-20 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($steps as $s)
                <div class="card-hover rounded-card border border-hair bg-white p-6">
                    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-emerald-soft text-base font-extrabold text-emerald">{{ $s['n'] }}</div>
                    <h3 class="mt-4 font-bold text-ink">{{ $s['k'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $s['sub'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ VALUE PANELS --}}
<section class="border-y border-hair bg-[#fafafa] py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="grid gap-6 lg:grid-cols-2">
            {{-- For shoppers --}}
            <div class="glass-card rounded-card p-8">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">For shoppers</h2>
                <p class="mt-3 text-2xl font-extrabold tracking-tight text-ink text-balance">Save money, back your high street.</p>
                <ul class="mt-6 space-y-3 text-sm">
                    @foreach ([
                        ['Save real money', 'Genuine discounts at independents near you, not the same chain deals everywhere.'],
                        ['Support local', 'Every redemption keeps money on your high street and helps the indies fight back.'],
                        ['No app needed', 'Scan a window sticker or open it in your browser. Nothing to download first.'],
                    ] as $point)
                        <li class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <span class="text-ink/80"><span class="font-semibold text-ink">{{ $point[0] }}.</span> {{ $point[1] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- For retailers --}}
            <div class="relative overflow-hidden rounded-card border-2 border-emerald bg-ink p-8 text-white">
                <div class="pointer-events-none absolute -top-16 -right-16 h-48 w-48 rounded-full bg-emerald/25 blur-2xl"></div>
                <div class="relative">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-soft">For retailers</h2>
                    <p class="mt-3 text-2xl font-extrabold tracking-tight text-balance">Drive footfall, own the relationship.</p>
                    <ul class="mt-6 space-y-3 text-sm">
                        @foreach ([
                            ['Drive footfall', 'Post an offer and bring nearby shoppers through your door, then see exactly what it drove.'],
                            ['Capture customers', 'Every redemption adds a real customer to a list you own, not a chain or a platform.'],
                            ['Full control of offers', 'You set the deal, the terms and when it runs. Change or pull it anytime.'],
                        ] as $point)
                            <li class="flex items-start gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                <span class="text-white/80"><span class="font-semibold text-white">{{ $point[0] }}.</span> {{ $point[1] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('business.join') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 text-sm font-semibold text-ink transition hover:bg-emerald hover:text-white">
                        List your business free
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ FOOTER CTA --}}
<section class="border-t border-hair bg-ink py-20 text-center text-white sm:py-24">
    <div class="mx-auto max-w-2xl px-5 sm:px-6">
        <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Ready to see it for real?</h2>
        <p class="mt-4 text-white/70">Browse the independents already on locolie, or list your own shop free and post your first offer.</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('seo.index') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-white/20 bg-white/[0.06] px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/[0.12]">
                Browse the directory
            </a>
            <a href="{{ route('business.join') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-ink transition hover:bg-emerald hover:text-white">
                List your business free
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
