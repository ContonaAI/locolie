@extends('site.layout')
@section('title', 'locolie - back your high street and bag real local discounts')
@section('meta_description', 'locolie helps you back the indies. Shoppers discover real discounts from independent shops near you in Newcastle NE1. Retailers list free, post offers that drive footfall, and message customers in real time by email, SMS and push.')

@push('head')
<script>window.FL_CITIES = @json($cityData); window.FL_POINTS = @json($mapPoints);</script>
@include('partials.google-maps', ['key' => $mapsKey ?? null])
@endpush

@section('content')

{{-- ============================================================ HERO --}}
<section class="relative overflow-hidden hero-grid">
    <div class="mesh" aria-hidden="true" data-parallax="0.12"><i class="b1"></i><i class="b2"></i><i class="b3"></i></div>

    <div class="relative z-10 mx-auto grid max-w-7xl 2xl:max-w-[1500px] items-center gap-12 px-5 pb-20 pt-32 sm:px-6 lg:grid-cols-2 lg:gap-8 lg:pb-28 lg:pt-40">
        <div class="reveal" x-data="geoArea({{ $stats['businesses'] }})" x-init="detect()">
            <span :class="live ? 'text-emerald' : 'text-amber-600'"
                  class="inline-flex items-center gap-2 rounded-full border border-hair bg-white/70 glass px-3.5 py-1.5 text-xs font-semibold shadow-sm">
                <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75" :class="live ? 'bg-emerald' : 'bg-amber-500'"></span><span class="relative inline-flex h-2 w-2 rounded-full" :class="live ? 'bg-emerald' : 'bg-amber-500'"></span></span>
                <span x-text="label">Now live in Newcastle NE1</span>
            </span>
            <h1 class="mt-6 text-4xl font-extrabold leading-[1.02] tracking-tight text-balance sm:text-5xl lg:text-[4.1rem]">
                Back the indies. <span class="gradient-text">Bag the deals</span>.
            </h1>
            <p class="mt-5 max-w-lg text-lg leading-relaxed text-muted">
                locolie brings back your high street. Find real discounts from independents near you, tap to reveal the offer and redeem it at the till - and keep the corner cafe, the family butcher and the indie salon thriving. <span class="font-semibold text-ink">Live in Newcastle now</span>, rolling out across the UK next.
            </p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <a href="/app" class="group inline-flex items-center justify-center gap-2 rounded-full bg-ink px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-ink/10 transition hover:bg-emerald hover:shadow-emerald/25">
                    Find local deals
                    <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
                <a href="/for-business" class="inline-flex items-center justify-center gap-2 rounded-full border border-hair bg-white/70 glass px-6 py-3.5 text-sm font-semibold text-ink transition hover:border-ink">
                    List your business
                </a>
            </div>
            {{-- Quick-search chips: contextual entry points straight into the app --}}
            <div class="mt-6 flex flex-wrap gap-2">
                @php $quick = [['Open now', '/app'], ['Free stuff', '/app'], ['Top rated', '/app'], ['Near me', '/app'], ['Food & drink', '/category/food-drink']]; @endphp
                @foreach ($quick as $q)
                    <a href="{{ $q[1] }}" class="inline-flex items-center gap-1.5 rounded-full border border-hair bg-white px-3.5 py-1.5 text-sm font-medium text-ink transition hover:border-emerald hover:text-emerald">
                        <svg class="h-3.5 w-3.5 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>{{ $q[0] }}
                    </a>
                @endforeach
            </div>
            <div class="mt-8 flex items-center gap-6 text-sm text-muted">
                <div><span class="text-xl font-extrabold text-ink" x-text="count">{{ $stats['businesses'] }}</span> <span class="block text-xs">indies <span x-text="'in '+place" class="text-emerald">in Newcastle</span></span></div>
                <span class="h-8 w-px bg-hair"></span>
                <div><span class="text-xl font-extrabold text-ink" data-count="{{ $stats['offers'] }}">{{ $stats['offers'] }}</span> <span class="block text-xs">live deals</span></div>
                <span class="h-8 w-px bg-hair"></span>
                <div><span class="text-xl font-extrabold text-ink" data-count="{{ $stats['categories'] }}">{{ $stats['categories'] }}</span> <span class="block text-xs">categories</span></div>
            </div>
        </div>

        {{-- Hero device --}}
        <div class="relative flex justify-center lg:justify-end">
            <div class="absolute -inset-10 rounded-[3rem] bg-gradient-to-tr from-emerald-soft via-white to-transparent blur-3xl" data-parallax="0.05"></div>
            <div class="relative animate-floaty">
                @include('site._phone', ['src' => '/app', 'class' => 'relative', 'cards' => $featured])
            </div>
            {{-- Floating feature chips - the marketing tools businesses get --}}
            @php $heroChips = [
                ['-left-6 top-10', '-1.5s', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/>', 'Email sent', 'to 94 regulars'],
                ['-left-4 bottom-28', '-3s', '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>', 'SMS sent', '“2-for-1 tonight 🍔”'],
                ['-right-6 top-24', '-0.6s', '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>', 'Push sent', 'to shoppers nearby'],
                ['-right-3 bottom-16', '-2.2s', '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>', '+ New customer', 'added to your list'],
            ]; @endphp
            @foreach ($heroChips as $chip)
                <div class="absolute {{ $chip[0] }} hidden glass-card rounded-2xl px-3.5 py-2.5 lg:block animate-floaty2" style="animation-delay:{{ $chip[1] }}">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-soft text-emerald"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $chip[2] !!}</svg></span>
                        <div class="text-xs leading-tight"><div class="font-bold text-ink">{{ $chip[3] }}</div><div class="text-muted">{{ $chip[4] }}</div></div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Mobile: the same feature overlays as a tidy scrolling strip (floating ones are desktop-only) --}}
        <div class="-mx-5 mt-8 flex gap-2.5 overflow-x-auto px-5 pb-1 lg:hidden" style="scrollbar-width:none">
            @foreach ($heroChips as $chip)
                <div class="flex shrink-0 items-center gap-2 rounded-2xl border border-hair bg-white px-3.5 py-2.5 shadow-sm">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-soft text-emerald"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $chip[2] !!}</svg></span>
                    <div class="whitespace-nowrap text-xs leading-tight"><div class="font-bold text-ink">{{ $chip[3] }}</div><div class="text-muted">{{ $chip[4] }}</div></div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ TWO-SIDED VALUE --}}
<section class="relative z-10 -mt-6 pb-8 sm:pb-12">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="grid gap-5 md:grid-cols-2">
            {{-- Shoppers --}}
            <div class="reveal card-hover rounded-card border border-hair bg-white p-7">
                <div class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-soft text-emerald"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></span>
                    <div><div class="text-xs font-semibold uppercase tracking-wider text-emerald">For shoppers</div><h3 class="text-lg font-bold">Real discounts on your doorstep</h3></div>
                </div>
                <p class="mt-3 text-sm leading-relaxed text-muted">Find independents on a live map, reveal real discount codes, redeem at the till, save your favourites and get a nudge when there's a deal near you.</p>
                <a href="/app" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:text-ink">Find local deals <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>
            {{-- Businesses --}}
            <div class="reveal card-hover relative overflow-hidden rounded-card bg-ink p-7 text-white" data-d="1">
                <div class="mesh" aria-hidden="true" style="opacity:.16"><i class="b2"></i></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-emerald-soft"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9 12 3l9 6v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 21V12h6v9"/></svg></span>
                        <div><div class="text-xs font-semibold uppercase tracking-wider text-emerald-soft">For retailers</div><h3 class="text-lg font-bold">Drive footfall, then keep them coming back</h3></div>
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-white/70">List free and post offers that pull real footfall through your door. Every redemption captures a customer you own, so you can message them in real time by email, SMS and push - all from one dashboard.</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @php $chips = [
                            ['Customer data', '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
                            ['Email', '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/>'],
                            ['Push', '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/>'],
                            ['SMS / text', '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>'],
                            ['Analytics', '<path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/>'],
                        ]; @endphp
                        @foreach ($chips as $c)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-semibold text-white/90">
                                <svg class="h-3.5 w-3.5 text-emerald-soft" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $c[1] !!}</svg>{{ $c[0] }}
                            </span>
                        @endforeach
                    </div>
                    <a href="/for-business" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-soft hover:text-white">See what retailers get <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                </div>
            </div>
        </div>

        {{-- Reach map: where we're live + where we're scouting --}}
        <div class="reveal mt-5 overflow-hidden rounded-card border border-hair bg-white">
            <div class="flex flex-wrap items-center justify-between gap-3 px-6 pt-5">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-emerald">Where we're at</div>
                    <h3 class="text-lg font-bold">Live in Newcastle, coming to your town next</h3>
                </div>
                <div class="flex items-center gap-4 text-xs font-medium text-muted">
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald"></span> Live now</span>
                    <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-500"></span> Coming soon</span>
                </div>
            </div>
            <div id="reachmap" class="mt-4 h-[440px] w-full bg-[#eef1f4]"></div>
        </div>
    </div>
</section>

<script>
  document.addEventListener('DOMContentLoaded', async function () {
    var el = document.getElementById('reachmap');
    if (!el || !(window.google && google.maps && google.maps.importLibrary)) return;
    const { Map } = await google.maps.importLibrary('maps');
    const { AdvancedMarkerElement } = await google.maps.importLibrary('marker');
    const map = new Map(el, {
      center: { lat: 54.4, lng: -3.2 }, zoom: 5,
      mapId: @json($mapsId ?? 'DEMO_MAP_ID'),
      disableDefaultUI: true, zoomControl: true, gestureHandling: 'cooperative', clickableIcons: false,
    });
    const markers = (window.FL_POINTS || []).map(function (p) {
      const dot = document.createElement('div');
      dot.style.cssText = 'width:11px;height:11px;border-radius:50%;border:1.5px solid #fff;box-shadow:0 1px 3px rgba(0,0,0,.35);background:' + (p.live ? '#059669' : '#f59e0b');
      return new AdvancedMarkerElement({ position: { lat: p.lat, lng: p.lng }, content: dot });
    });
    new markerClusterer.MarkerClusterer({ map: map, markers: markers });
  });
</script>

{{-- ============================================================ PROBLEM / WHY --}}
<section class="relative border-y border-hair bg-[#fafafa] py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-3xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">The problem</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Indies are brilliant. Getting noticed isn't.</p>
            <p class="mt-4 text-lg text-muted">The chains and online giants have apps, loyalty cards and ad budgets. The indie down the road - better coffee, fairer prices, bags more character - has a chalkboard and a lot of hope. locolie evens things up.</p>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
            @php
                $probs = [
                    ['stat' => '1 in 7', 'label' => 'high-street shops sit empty', 'body' => 'Footfall drifts to the chains and out-of-town retail. Indies lose the discovery game before they start.'],
                    ['stat' => '£0', 'label' => 'marketing budget, typically', 'body' => 'Most indies can\'t stretch to ads or an app, so loyal locals never even hear about their offers.'],
                    ['stat' => '0%', 'label' => 'of their customers, owned', 'body' => 'They serve hundreds a week but capture none of them - no way to bring a single one back.'],
                ];
            @endphp
            @foreach ($probs as $i => $p)
                <div class="reveal card-hover rounded-card border border-hair bg-white p-7" data-d="{{ $i+1 }}">
                    <div class="text-4xl font-extrabold gradient-text">{{ $p['stat'] }}</div>
                    <div class="mt-1 text-sm font-semibold text-ink">{{ $p['label'] }}</div>
                    <p class="mt-3 text-sm leading-relaxed text-muted">{{ $p['body'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ USP GRID --}}
<section id="how" class="py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">Why locolie</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">One app. Your whole independent high street.</p>
            <p class="mt-4 text-muted">Built for the way people really discover, and the way small shops really run.</p>
        </div>
        <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @php
                $usps = [
                    ['t' => 'Live local map', 'b' => 'A clean map of every indie near you - names, categories and offers on the pin. No chains cluttering it up.',
                     'i' => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>'],
                    ['t' => 'Real, redeemable discounts', 'b' => 'Tap to reveal a one-time code, scan at the till. Every redemption is tracked - proof a deal really drove footfall.',
                     'i' => '<path d="M20.59 13.41 13.42 20.6a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82Z"/><line x1="7" y1="7" x2="7.01" y2="7"/>'],
                    ['t' => 'Customers you own', 'b' => 'Every redemption captures a real customer you can market to again - the loyalty data the chains have, finally for indies.',
                     'i' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
                    ['t' => 'Smart, instant search', 'b' => 'Filter by distance, category, rating, open-now and offer type. Find exactly what\'s near you, right now.',
                     'i' => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>'],
                    ['t' => 'Email, SMS and push that land', 'b' => 'Nearby shoppers get pinged about fresh offers; retailers bring regulars back in real time. Reach you don\'t rent from Big Tech.',
                     'i' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>'],
                    ['t' => 'Free to start, fair to grow', 'b' => 'Retailers list free, forever. Upgrade only for more reach - no contracts, cancel anytime.',
                     'i' => '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>'],
                ];
            @endphp
            @foreach ($usps as $i => $u)
                <div class="reveal card-hover rounded-card border border-hair bg-white p-7" data-d="{{ ($i % 3) + 1 }}">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $u['i'] !!}</svg>
                    </div>
                    <h3 class="mt-5 text-lg font-bold">{{ $u['t'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $u['b'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ FIND SOMETHING FOR... (outcome-first) --}}
<section class="border-t border-hair py-16 sm:py-20">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="reveal">
            <h2 class="text-2xl font-extrabold tracking-tight sm:text-3xl">Find something for...</h2>
            <p class="mt-2 text-muted">Tell us the occasion and we'll point you at the right indies.</p>
        </div>
        <div class="mt-8 flex flex-wrap gap-3 reveal">
            @php $occasions = [
                ['A coffee run', 'food-drink', '<path d="M18 8h1a4 4 0 0 1 0 8h-1"/><path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4Z"/>'],
                ['A night out', 'pubs-bars', '<path d="M8 22h8M12 11v11M5 3h14l-1 8a6 6 0 0 1-12 0Z"/>'],
                ['Treating yourself', 'beauty', '<path d="m12 3 1.9 5.8H20l-5 3.6 1.9 5.8L12 14.6 6.1 18.2 8 12.4l-5-3.6h6.1Z"/>'],
                ['A fresh cut', 'hairdressers', '<circle cx="6" cy="6" r="3"/><circle cx="6" cy="18" r="3"/><line x1="20" y1="4" x2="8.12" y2="15.88"/><line x1="8.12" y1="8.12" x2="12" y2="12"/>'],
                ['Getting fit', 'fitness', '<path d="m6.5 6.5 11 11"/><path d="m21 21-1-1"/><path d="m3 3 1 1"/><path d="m18 22 4-4"/><path d="m2 6 4-4"/>'],
                ['Shopping local', 'retail', '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/>'],
            ]; @endphp
            @foreach ($occasions as $o)
                <a href="/category/{{ $o[1] }}" class="card-hover inline-flex items-center gap-2.5 rounded-2xl border border-hair bg-white px-5 py-3.5 font-semibold text-ink">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-soft text-emerald"><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $o[2] !!}</svg></span>
                    {{ $o[0] }}
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ BROWSE BY CATEGORY --}}
<section id="categories" class="border-t border-hair py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">Explore</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Every kind of indie.</p>
            <p class="mt-4 text-muted">From your morning coffee to your MOT - browse the categories we cover in Newcastle NE1.</p>
        </div>
        <div class="mt-12 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($categories as $i => $c)
                <a href="/category/{{ $c->slug }}" class="reveal card-hover group flex items-center gap-3 rounded-2xl border border-hair bg-white p-4" data-d="{{ ($i % 4) + 1 }}">
                    <span class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-soft text-emerald transition group-hover:bg-emerald group-hover:text-white">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($c->slug) !!}</svg>
                    </span>
                    <div class="min-w-0">
                        <div class="truncate font-bold text-ink">{{ $c->name }}</div>
                        <div class="text-xs text-muted">{{ $c->live_count }} {{ \Illuminate\Support\Str::plural('business', $c->live_count) }}</div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ FEATURED INDEPENDENTS (real data) --}}
@if ($featured->count())
<section class="border-t border-hair bg-[#fafafa] py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">On locolie now</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Real indies, real discounts.</p>
            <p class="mt-4 text-muted">A few of the {{ number_format($stats['businesses']) }} Newcastle indies already live in the app.</p>
        </div>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $i => $b)
                @php $offer = $b->activeOffers->first(); @endphp
                <a href="/app?b={{ $b->slug }}" target="_blank" rel="noopener" class="reveal card-hover group overflow-hidden rounded-card border border-hair bg-white" data-d="{{ ($i % 3) + 1 }}">
                    <div class="relative h-44 overflow-hidden bg-[#e2e8f0]">
                        <img src="{{ $b->photos[0] }}" alt="{{ $b->name }} - independent {{ $b->category?->name }} in Newcastle" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                        @if ($b->plan !== 'free')
                            <span class="absolute right-3 top-3 rounded-full bg-black/70 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">Sponsored</span>
                        @endif
                        @if ($offer)
                            <span class="absolute left-3 top-3 rounded-lg bg-emerald px-2.5 py-1 text-xs font-extrabold text-white">{{ $offer->badge }}</span>
                        @endif
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-emerald">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($b->category?->slug) !!}</svg>
                            {{ $b->category?->name }}
                        </div>
                        <h3 class="mt-1.5 text-lg font-bold text-ink">{{ $b->name }}</h3>
                        <div class="mt-1 flex items-center gap-1 text-sm text-muted"><span class="text-amber-500">★</span> {{ number_format((float) $b->rating, 1) }} <span class="text-hair">·</span> {{ $b->postcode }}</div>
                        @if ($offer)
                            <div class="mt-3 rounded-lg bg-emerald-soft px-3 py-2 text-sm font-semibold text-emerald">{{ $offer->title }}</div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ============================================================ LIVE DEMO --}}
<section id="demo" class="relative overflow-hidden border-y border-hair bg-ink py-20 text-white sm:py-28">
    <div class="mesh" aria-hidden="true" style="opacity:.18" data-parallax="0.08"><i class="b1"></i><i class="b2"></i><i class="b3"></i></div>
    <div class="relative z-10 mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald-soft">See it live</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">A real, fast, properly handy app.</p>
            <p class="mt-4 text-white/60">{{ number_format($stats['businesses']) }} real Newcastle indies, live right now. Open it full-screen and it works a treat on any device.</p>
        </div>

        <div class="mt-12 grid items-center gap-10 reveal lg:grid-cols-2">
            <div class="flex justify-center lg:justify-end">
                @include('site._phone', ['src' => '/app', 'class' => 'animate-floaty', 'dark' => true, 'cards' => $featured])
            </div>
            <div>
                <ul class="space-y-4">
                    @php $live = [
                        ['Live map &amp; feed', 'Every indie near you - names, categories and offers on the pin.'],
                        ['One-tap redemptions', 'Reveal a code, scan at the till, saving applied instantly.'],
                        ['Fast &amp; lightweight', 'Optimised images and lazy loading - it flies, even on mobile data.'],
                    ]; @endphp
                    @foreach ($live as $l)
                        <li class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald text-white"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                            <div><span class="font-semibold text-white">{!! $l[0] !!}.</span> <span class="text-white/60">{!! $l[1] !!}</span></div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="/app" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-white px-6 py-3.5 text-sm font-bold text-ink transition hover:bg-emerald hover:text-white">
                        Open the live app
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M7 7h10v10"/></svg>
                    </a>
                    <a href="/app?as=business" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full glass-dark px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/[0.1]">
                        See the business view
                    </a>
                </div>
                <p class="mt-4 text-xs text-white/40">Tip: open it, then resize your browser - it’s one responsive app for mobile, tablet &amp; desktop.</p>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ OWN YOUR CUSTOMERS --}}
<section id="data" class="py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div class="reveal">
                <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">The secret weapon</h2>
                <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">The customer data the chains have - <span class="gradient-text">finally for the indies</span>.</p>
                <p class="mt-5 text-lg leading-relaxed text-muted">
                    Every time a shopper redeems your offer, locolie captures them as <span class="font-semibold text-ink">your customer</span> - name, email, how often they pop in. It's the first-party data the chains built loyalty empires on, that indies have <span class="font-semibold text-ink">never been able to get</span>. Now you can reach them in real time by email, SMS and push.
                </p>
                <div class="mt-8 space-y-4">
                    @php $perks = [
                        ['Capture every customer', 'No clipboards - each redemption builds your list automatically.'],
                        ['Message them in real time', 'Fire off email, SMS and push offers to opted-in regulars in a couple of taps.'],
                        ['Own the relationship', 'Export your list anytime. It\'s yours - no middleman owning your customers.'],
                    ]; @endphp
                    @foreach ($perks as $p)
                        <div class="flex gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-soft text-emerald"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                            <div><span class="font-semibold text-ink">{{ $p[0] }}.</span> <span class="text-muted">{{ $p[1] }}</span></div>
                        </div>
                    @endforeach
                </div>
                <a href="/for-business" class="mt-8 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:text-ink">See how it works for retailers <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
            </div>

            <div class="relative reveal" data-d="2">
                <div class="absolute -inset-6 rounded-[2.5rem] bg-gradient-to-tr from-emerald-soft to-transparent blur-2xl"></div>
                <div class="relative glass-card rounded-card p-6">
                    <div class="flex items-center justify-between"><div class="font-bold text-ink">Your customers</div><span class="rounded-full bg-emerald-soft px-3 py-1 text-xs font-bold text-emerald">128 captured</span></div>
                    <p class="mt-1 text-xs text-muted">94 opted in to marketing</p>
                    <div class="mt-4 divide-y divide-hair">
                        @php $rows = [['Sarah J.', 'sarah.j@…', '4 visits'], ['Mark T.', 'markt@…', '2 visits'], ['Priya K.', 'priya@…', '6 visits'], ['Dan W.', 'danw@…', '1 visit']]; @endphp
                        @foreach ($rows as $r)
                            <div class="flex items-center justify-between py-2.5">
                                <div class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-xs font-bold text-ink shadow-sm">{{ substr($r[0],0,1) }}</span><div><div class="text-sm font-semibold text-ink">{{ $r[0] }}</div><div class="text-xs text-muted">{{ $r[1] }}</div></div></div>
                                <span class="text-xs font-medium text-muted">{{ $r[2] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <button class="mt-4 w-full rounded-xl bg-emerald py-2.5 text-sm font-bold text-white">✉ Email these customers</button>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ CASE STUDIES --}}
<section id="stories" class="py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">Case studies</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Indies, winning.</p>
            <p class="mt-4 text-muted">How real NE1 indies use locolie to pull in footfall and keep customers coming back.</p>
        </div>
        <div class="mt-14 grid gap-6 lg:grid-cols-3">
            @php
                $stories = [
                    ['cat' => 'Food & Drink', 'icon' => 'food-drink', 'name' => 'A Quayside café',
                     'quote' => 'A “20% off before noon” offer filled our quietest hours. We captured 140 regulars in a month - now we just text them when the cakes come out of the oven.',
                     'stat' => '+38%', 'statlabel' => 'weekday morning covers'],
                    ['cat' => 'Hairdressers', 'icon' => 'hairdressers', 'name' => 'A Grainger St barber',
                     'quote' => 'New-client cuts via the app, then automated win-back texts at the 5-week mark. Our chairs are full and the rebookings run themselves.',
                     'stat' => '210', 'statlabel' => 'customers on their list'],
                    ['cat' => 'Fitness', 'icon' => 'fitness', 'name' => 'An independent gym',
                     'quote' => 'Free class taster on locolie, push notification to anyone within a mile. We turned taster sign-ups into members for the price of zero ad spend.',
                     'stat' => '£0', 'statlabel' => 'spent on ads'],
                ];
            @endphp
            @foreach ($stories as $i => $s)
                <figure class="reveal card-hover flex flex-col rounded-card border border-hair bg-white p-7" data-d="{{ $i+1 }}">
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-emerald">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($s['icon']) !!}</svg>
                        {{ $s['cat'] }}
                    </div>
                    <blockquote class="mt-4 flex-1 text-[15px] leading-relaxed text-ink/80">“{{ $s['quote'] }}”</blockquote>
                    <figcaption class="mt-5 flex items-center justify-between border-t border-hair pt-4">
                        <span class="text-sm font-semibold text-ink">{{ $s['name'] }}</span>
                        <span class="text-right"><span class="block text-2xl font-extrabold gradient-text">{{ $s['stat'] }}</span><span class="block text-[11px] text-muted">{{ $s['statlabel'] }}</span></span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
        <p class="mt-6 text-center text-xs text-muted">Illustrative results from early locolie pilots with indies in Newcastle NE1.</p>
    </div>
</section>

{{-- ============================================================ COMPARISON --}}
<section class="border-y border-hair bg-[#fafafa] py-20 sm:py-28">
    <div class="mx-auto max-w-4xl px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">The difference</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Everything the chains have, without becoming one.</p>
        </div>
        <div class="mt-12 overflow-hidden rounded-card border border-hair bg-white reveal">
            @php
                $compare = [
                    ['Get discovered by nearby shoppers', true, false],
                    ['Run trackable offers & redemptions', true, false],
                    ['Own your customer list', true, false],
                    ['Message customers by email, SMS & push', true, false],
                    ['No ad agency or app to build', true, false],
                    ['Keeps money on the high street', true, false],
                ];
            @endphp
            <div class="grid grid-cols-[1fr_auto_auto] items-center gap-x-4 sm:gap-x-8">
                <div class="px-5 py-4 text-xs font-semibold uppercase tracking-wider text-muted sm:px-7"></div>
                <div class="py-4 text-center text-sm font-extrabold text-emerald">With locolie</div>
                <div class="px-5 py-4 text-center text-sm font-semibold text-muted sm:px-7">Going it alone</div>
                @foreach ($compare as $c)
                    <div class="border-t border-hair px-5 py-4 text-sm font-medium text-ink sm:px-7">{{ $c[0] }}</div>
                    <div class="border-t border-hair py-4 text-center">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald text-white"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                    </div>
                    <div class="border-t border-hair px-5 py-4 text-center sm:px-7">
                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-[#f0f0f0] text-muted"><svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg></span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ HOW IT WORKS (shoppers) --}}
<section class="py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">For shoppers</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Three taps to a real local discount.</p>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
            @php $steps = [
                ['n'=>'01','t'=>'Discover','b'=>'Browse a live map and feed of indies near you - by category, distance and offer.','i'=>'<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z"/><circle cx="12" cy="10" r="3"/>'],
                ['n'=>'02','t'=>'Reveal the code','b'=>'Tap an offer to reveal its one-time code. Real discounts from the indies, not the chains.','i'=>'<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>'],
                ['n'=>'03','t'=>'Redeem at the till','b'=>'Show your code, the shop scans the QR, the saving applies instantly. Done.','i'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><path d="M14 14h3v3M21 21v.01M17 21h.01M21 17h.01"/>'],
            ]; @endphp
            @foreach ($steps as $i => $s)
                <div class="reveal card-hover relative rounded-card border border-hair bg-white p-7" data-d="{{ $i+1 }}">
                    <span class="absolute right-6 top-6 text-sm font-bold text-hair">{{ $s['n'] }}</span>
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-soft text-emerald"><svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $s['i'] !!}</svg></div>
                    <h3 class="mt-5 text-lg font-bold">{{ $s['t'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $s['b'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ STATS BAND --}}
<section class="relative overflow-hidden bg-ink py-16 text-white">
    <div class="mesh" aria-hidden="true" style="opacity:.16"><i class="b1"></i><i class="b2"></i></div>
    <div class="relative z-10 mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="grid gap-8 text-center sm:grid-cols-3">
            <div class="reveal"><div class="text-5xl font-extrabold tracking-tight"><span data-count="{{ $stats['businesses'] }}">{{ $stats['businesses'] }}</span></div><div class="mt-2 text-sm font-medium text-white/60">Independent shops</div></div>
            <div class="reveal" data-d="1"><div class="text-5xl font-extrabold tracking-tight"><span data-count="{{ $stats['categories'] }}">{{ $stats['categories'] }}</span></div><div class="mt-2 text-sm font-medium text-white/60">Categories</div></div>
            <div class="reveal" data-d="2"><div class="text-5xl font-extrabold tracking-tight gradient-text">NE1</div><div class="mt-2 text-sm font-medium text-white/60">Newcastle city centre</div></div>
        </div>
    </div>
</section>

{{-- ============================================================ PRICING --}}
<section id="pricing" class="py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">For retailers</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Get found by locals. Start free.</p>
            <p class="mt-4 text-muted">Claim your free listing in minutes and upgrade only when you want more reach. No contracts.</p>
        </div>
        <div class="reveal">@include('site._pricing')</div>
        <div class="mt-10 text-center reveal">
            <a href="/for-business" class="inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:text-ink">See everything retailers get <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
        </div>
    </div>
</section>

{{-- ============================================================ FOUNDERS --}}
<section id="founders" class="border-y border-hair bg-[#fafafa] py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="mx-auto max-w-2xl text-center reveal">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-emerald">The team</h2>
            <p class="mt-3 text-3xl font-extrabold tracking-tight sm:text-4xl">Three founders who love their high street.</p>
            <p class="mt-4 text-muted">Equal partners, one mission: bring back the indies and keep them thriving.</p>
        </div>
        <div class="mx-auto mt-14 grid max-w-4xl gap-6 sm:grid-cols-3">
            @php $founders = [
                ['name'=>'Tom','initials'=>'T','role'=>'Co-founder','line'=>'Product & engineering - building the app shoppers actually want to open.'],
                ['name'=>'Joe','initials'=>'J','role'=>'Co-founder','line'=>'Growth & business - getting locolie into every NE1 shop window.'],
                ['name'=>'Roddy','initials'=>'R','role'=>'Co-founder','line'=>'Operations & partnerships - keeping local indies on side and onboarded.'],
            ]; @endphp
            @foreach ($founders as $i => $f)
                <div class="reveal card-hover rounded-card border border-hair bg-white p-7 text-center" data-d="{{ $i+1 }}">
                    <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-emerald to-[#047857] text-2xl font-extrabold text-white shadow-lg shadow-emerald/20">{{ $f['initials'] }}</div>
                    <h3 class="mt-5 text-lg font-bold">{{ $f['name'] }}</h3>
                    <p class="text-xs font-semibold uppercase tracking-wider text-emerald">{{ $f['role'] }}</p>
                    <p class="mt-3 text-sm leading-relaxed text-muted">{{ $f['line'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================ APP DOWNLOAD --}}
<section id="download" class="py-20 sm:py-28">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <div class="relative overflow-hidden rounded-[2rem] bg-ink px-8 py-14 text-white sm:px-14 lg:py-20">
            <div class="mesh" aria-hidden="true" style="opacity:.2"><i class="b1"></i><i class="b2"></i></div>
            <div class="relative z-10 grid items-center gap-10 lg:grid-cols-2">
                <div class="reveal">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">Your high street, in your pocket.</h2>
                    <p class="mt-4 max-w-md text-white/70">Coming soon to the App Store and Google Play. Have a proper go in your browser right now.</p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="#" class="inline-flex items-center gap-3 rounded-xl border border-white/15 bg-white/5 px-5 py-3 transition hover:bg-white/10" aria-label="Download on the App Store (coming soon)">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="white"><path d="M17.05 12.04c-.02-2.06 1.68-3.06 1.76-3.1-.96-1.4-2.46-1.6-2.99-1.62-1.27-.13-2.49.75-3.13.75-.65 0-1.65-.73-2.71-.71-1.39.02-2.68.81-3.4 2.06-1.45 2.52-.37 6.25 1.04 8.29.69 1 1.51 2.12 2.58 2.08 1.04-.04 1.43-.67 2.69-.67 1.25 0 1.6.67 2.7.65 1.11-.02 1.82-1.02 2.5-2.02.79-1.16 1.11-2.28 1.13-2.34-.02-.01-2.17-.83-2.19-3.3zM15 6.2c.57-.69.96-1.65.85-2.6-.82.03-1.82.55-2.41 1.24-.53.61-.99 1.58-.87 2.51.92.07 1.86-.47 2.43-1.15z"/></svg>
                            <span class="text-left leading-tight"><span class="block text-[10px] text-white/60">Download on the</span><span class="block text-base font-semibold">App Store</span></span>
                        </a>
                        <a href="#" class="inline-flex items-center gap-3 rounded-xl border border-white/15 bg-white/5 px-5 py-3 transition hover:bg-white/10" aria-label="Get it on Google Play (coming soon)">
                            <svg class="h-7 w-7" viewBox="0 0 24 24"><path d="M3.6 2.3c-.3.3-.5.8-.5 1.4v16.6c0 .6.2 1.1.5 1.4l.1.1 9.3-9.3v-.2L3.6 2.3z" fill="#00d2ff"/><path d="M16.1 15.3l-3.1-3.1v-.2l3.1-3.1.1.1 3.7 2.1c1.1.6 1.1 1.6 0 2.2l-3.8 2z" fill="#ffce00"/><path d="M16.2 15.2 13 12 3.6 21.7c.4.4 1 .4 1.7.1l10.9-6.6z" fill="#ff3a44"/><path d="M16.2 8.8 5.3 2.2c-.7-.4-1.3-.3-1.7.1L13 12l3.2-3.2z" fill="#00f076"/></svg>
                            <span class="text-left leading-tight"><span class="block text-[10px] text-white/60">Get it on</span><span class="block text-base font-semibold">Google Play</span></span>
                        </a>
                    </div>
                    <a href="/app" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-soft hover:text-white">Try the web app now <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                </div>
                <div class="relative z-10 flex justify-center lg:justify-end reveal" data-d="2">
                    @include('site._phone', ['src' => '/app', 'class' => 'animate-floaty', 'dark' => true, 'cards' => $featured])
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================ FINAL CTA --}}
<section class="border-t border-hair py-20 text-center sm:py-28">
    <div class="mx-auto max-w-2xl px-5 sm:px-6 reveal">
        <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl text-balance">Ready to back the indies?</h2>
        <p class="mt-4 text-muted">Join the shoppers and retailers bringing Newcastle's independent high street back to life.</p>
        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="/app" class="inline-flex items-center justify-center gap-2 rounded-full bg-ink px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-emerald">Find local deals</a>
            <a href="/for-business" class="inline-flex items-center justify-center rounded-full border border-hair bg-white px-6 py-3.5 text-sm font-semibold text-ink transition hover:border-ink">List your business - free</a>
        </div>
    </div>
</section>

@endsection
