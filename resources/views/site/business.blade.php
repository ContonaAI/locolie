@extends('site.layout')
@section('title', $business->name.' - '.$business->category?->name.' in '.($business->city ?: $llCity).' | locolie')
@section('meta_description', \Illuminate\Support\Str::limit('Independent '.$business->category?->name.' in '.($business->city ?: $llCity).' - '.($business->description ?: $business->name.' on locolie. See offers, ratings and redeem deals at the till.'), 155))

@push('head')
@php
    $img = $business->photos[0] ?? null;

    $biz = [
        '@type' => 'LocalBusiness',
        '@id' => url()->current().'#business',
        'name' => $business->name,
        'url' => url()->current(),
        'image' => $img ? url($img) : url('/og.png'),
        'address' => array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $business->address ?: null,
            'addressLocality' => $business->city ?: $ll['city_full'],
            'postalCode' => $business->postcode,
            'addressCountry' => 'GB',
        ]),
    ];
    if ($business->description) $biz['description'] = \Illuminate\Support\Str::limit($business->description, 300, '');
    if ($business->phone) $biz['telephone'] = $business->phone;
    if ($business->website) $biz['sameAs'] = [$business->website];
    if ($business->lat && $business->lng) {
        $biz['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => $business->lat, 'longitude' => $business->lng];
    }
    if ($business->rating) {
        $biz['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (float) $business->rating,
            'reviewCount' => (int) ($business->reviews_count ?: 1),
        ];
    }
    if ($business->publicOffers()->isNotEmpty()) {
        $biz['makesOffer'] = $business->publicOffers()->map(fn ($o) => array_filter([
            '@type' => 'Offer',
            'name' => $o->title,
            'description' => trim($o->badge.($o->terms ? ' - '.$o->terms : '')),
        ]))->values()->all();
    }

    $ld = ['@context' => 'https://schema.org', '@graph' => [
        $biz,
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $business->category?->name, 'item' => url('/category/'.$business->category?->slug)],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $business->name, 'item' => url()->current()],
            ],
        ],
    ]];
@endphp
@if($img)<meta property="og:image" content="{{ url($img) }}">@endif
<script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
@php $reviews = (is_array($business->reviews) && count($business->reviews)) ? $business->reviews : []; @endphp

<section class="relative">
    <div class="h-64 w-full overflow-hidden bg-[#e2e8f0] sm:h-80">
        @if($img)<img src="{{ $img }}" alt="{{ $business->name }} - {{ $business->category?->name }} in {{ $business->city ?: $llCity }}" class="h-full w-full object-cover">@else<div class="h-full w-full bg-gradient-to-br from-emerald-soft to-[#e2e8f0]"></div>@endif
    </div>
    <div class="mx-auto max-w-5xl 2xl:max-w-6xl px-5 sm:px-6">
        <div class="-mt-16 rounded-card border border-hair bg-white p-6 shadow-xl sm:p-8">
            <nav class="mb-3 flex flex-wrap items-center gap-2 text-sm text-muted" aria-label="Breadcrumb">
                <a href="/" class="hover:text-ink">Home</a><span>/</span>
                <a href="/category/{{ $business->category?->slug }}" class="hover:text-ink">{{ $business->category?->name }}</a><span>/</span>
                <span class="font-semibold text-ink">{{ $business->name }}</span>
            </nav>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-emerald">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($business->category?->slug) !!}</svg>
                        {{ $business->category?->name }}
                        @if(config('locolie.offers_public') && $business->plan !== 'free')<span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] text-amber-800">Sponsored</span>@endif
                    </div>
                    <h1 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">{{ $business->name }}</h1>
                    <div class="mt-2 flex items-center gap-2 text-sm text-muted">
                        <span class="text-amber-500">★</span> {{ number_format((float) $business->rating, 1) }}
                        <span>({{ $business->reviews_count }} reviews)</span><span class="text-hair">·</span>
                        <span>{{ $business->address ?: $business->postcode }}</span>
                    </div>
                </div>
                <a href="/app?b={{ $business->slug }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-3 text-sm font-bold text-white transition hover:bg-emerald">
                    Open in the app <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M7 7h10v10"/></svg>
                </a>
            </div>
            @if($business->description)<p class="mt-5 max-w-2xl leading-relaxed text-muted">{{ $business->description }}</p>@endif
        </div>
    </div>
</section>

<div class="mx-auto max-w-5xl 2xl:max-w-6xl px-5 py-12 sm:px-6">
    <div class="grid gap-10 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-10">
            {{-- Offers (shown only when offers are publicly enabled) --}}
            @if($business->publicOffers()->isNotEmpty())
                <div>
                    <h2 class="text-xl font-bold">Live offers</h2>
                    <div class="mt-4 space-y-3">
                        @foreach($business->publicOffers() as $o)
                            <div class="flex items-center gap-4 rounded-card border border-hair bg-white p-4">
                                <span class="flex-shrink-0 rounded-lg bg-emerald px-3 py-2 text-sm font-extrabold text-white">{{ $o->badge }}</span>
                                <div><div class="font-semibold text-ink">{{ $o->title }}</div><div class="text-sm text-muted">{{ $o->terms ?: 'Open it in the locolie app and show your code at the till' }}</div></div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- No public offer: turn the page into a retailer sign-up hook. --}}
                <div class="relative overflow-hidden rounded-card border border-hair bg-emerald-soft p-6 sm:p-7">
                    <div class="flex items-start gap-4">
                        <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald text-white sm:flex">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9 12 3l9 6v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 21V12h6v9"/></svg>
                        </span>
                        <div>
                            <h2 class="text-xl font-bold text-ink">Own this business?</h2>
                            <p class="mt-2 max-w-xl leading-relaxed text-muted">Are you the owner of {{ $business->name }}? Claim your free listing and add offers your customers will love.</p>
                            <a href="{{ route('business.join') }}" class="mt-4 inline-flex items-center gap-2 rounded-full bg-emerald px-5 py-3 text-sm font-bold text-white transition hover:bg-ink">
                                Claim your free listing
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Reviews --}}
            @if(count($reviews))
                <div>
                    <h2 class="text-xl font-bold">Reviews <span class="text-sm font-medium text-muted">· via Google</span></h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach($reviews as $r)
                            <div class="rounded-card border border-hair bg-white p-5">
                                <div class="flex items-center justify-between"><span class="font-semibold text-ink">{{ $r['author'] ?? 'Google user' }}</span><span class="text-amber-500 text-sm">★ {{ $r['rating'] ?? 5 }}</span></div>
                                <p class="mt-2 text-sm leading-relaxed text-muted">{{ $r['text'] ?? '' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="rounded-card border border-hair bg-white p-5">
                <h3 class="font-bold text-ink">Visit</h3>
                <p class="mt-2 text-sm text-muted">{{ $business->address ?: trim(($business->city ?: $llCity).' '.$business->postcode) }}</p>
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($business->name.' '.$business->postcode) }}" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:text-ink">Get directions <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M7 7h10v10"/></svg></a>
            </div>
            <div class="rounded-card border border-hair bg-emerald-soft p-5">
                <h3 class="font-bold text-ink">Is this your shop?</h3>
                <p class="mt-2 text-sm text-muted">Claim your free listing, post offers that bring in real footfall, and message your regulars by email, SMS and push.</p>
                <a href="/for-business" class="mt-3 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:text-ink">Claim it free <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M7 7h10v10"/></svg></a>
            </div>
        </div>
    </div>

    {{-- Related --}}
    @if($related->count())
        <div class="mt-16">
            <h2 class="mb-4 text-xl font-bold">More indie {{ $business->category?->name }} nearby</h2>
            <div class="grid gap-5 sm:grid-cols-3">
                @foreach($related as $b)
                    <a href="/shop/{{ $b->slug }}" class="card-hover group overflow-hidden rounded-card border border-hair bg-white">
                        <div class="h-36 overflow-hidden bg-[#e2e8f0]"><img src="{{ $b->photos[0] }}" alt="{{ $b->name }}" loading="lazy" class="h-full w-full object-cover transition duration-500 group-hover:scale-105"></div>
                        <div class="p-4"><div class="font-bold text-ink">{{ $b->name }}</div><div class="mt-0.5 text-sm text-muted"><span class="text-amber-500">★</span> {{ number_format((float) $b->rating, 1) }} · {{ $b->postcode }}</div></div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
