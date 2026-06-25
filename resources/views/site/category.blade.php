@extends('site.layout')
@section('title', $category->name.' in Newcastle NE1 - independent '.$category->name.' on locolie')
@section('meta_description', 'Discover independent '.strtolower($category->name).' in Newcastle NE1 on locolie - see live offers, ratings and redeem deals at the till.')

@push('head')
@php
    $catLower = strtolower($category->name);
    $catFaqs = [
        ['q' => "Are these {$category->name} in Newcastle independent?", 'a' => "Yes - every business listed here is a genuine independent {$catLower} in or around Newcastle NE1, never a national chain."],
        ['q' => "Do independent {$catLower} in Newcastle offer discounts?", 'a' => "Many do. Businesses on locolie publish exclusive offers - look for the offer badge on a listing, then show the code or QR at the till to redeem it."],
        ['q' => "Is locolie free to use to find {$catLower}?", 'a' => "Completely free for shoppers. Discover independent {$catLower} near you and save with offers that keep money on your local high street."],
    ];
@endphp
@include('site.seo._jsonld', [
    'breadcrumbs' => array_values(array_filter([
        ['name' => 'Home', 'url' => url('/')],
        ['name' => 'Categories', 'url' => url('/').'#categories'],
        (\App\Models\Category::supportsHierarchy() && $category->parent)
            ? ['name' => $category->parent->name, 'url' => route('site.category', $category->parent->slug)] : null,
        ['name' => $category->name, 'url' => url()->current()],
    ])),
    'businesses' => $businesses,
    'faqs' => $catFaqs,
])
@endpush

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden hero-grid">
    <div class="mesh" aria-hidden="true" data-parallax="0.1"><i class="b1"></i><i class="b2"></i></div>
    <div class="relative z-10 mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 pb-10 pt-32 sm:px-6 lg:pt-40">
        <nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-muted" aria-label="Breadcrumb">
            <a href="/" class="hover:text-ink">Home</a><span>/</span>
            <a href="/#categories" class="hover:text-ink">Categories</a><span>/</span>
            @if (\App\Models\Category::supportsHierarchy() && $category->parent)
                <a href="{{ route('site.category', $category->parent->slug) }}" class="hover:text-ink">{{ $category->parent->name }}</a><span>/</span>
            @endif
            <span class="font-semibold text-ink">{{ $category->name }}</span>
        </nav>
        <div class="flex items-center gap-4">
            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-soft text-emerald shadow-sm">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($category->slug) !!}</svg>
            </span>
            <div>
                <h1 class="text-4xl font-extrabold tracking-tight sm:text-5xl">{{ $category->name }}</h1>
                <p class="mt-1 text-muted">{{ $businesses->count() }} independent {{ \Illuminate\Support\Str::plural('business', $businesses->count()) }} in Newcastle NE1</p>
            </div>
        </div>
        <p class="mt-6 max-w-2xl text-base leading-relaxed text-muted">Skip the chains and back your high street. These are real, independent {{ strtolower($category->name) }} near you - with live offers you redeem at the till on locolie.</p>
    </div>
</section>

@php $areas = \App\Services\LocationService::all()->take(8); @endphp

{{-- Subcategories --}}
@if (\App\Models\Category::supportsHierarchy() && $category->children->count())
    <section class="pb-4">
        <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-muted">Browse {{ strtolower($category->name) }} by type</h2>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                @foreach ($category->children as $child)
                    <a href="{{ route('site.category', $child->slug) }}" class="card-hover group flex items-center gap-3 rounded-card border border-hair bg-white px-4 py-3.5 text-ink transition hover:border-emerald">
                        <span class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($child->slug) !!}</svg>
                        </span>
                        <span class="text-sm font-semibold leading-tight group-hover:text-emerald">{{ $child->name }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- In your area --}}
@if ($areas->count())
    <section class="pb-4 pt-6">
        <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-muted">{{ $category->name }} in your area</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($areas as $loc)
                    <a href="{{ route('seo.landing', ['area' => $loc['slug'], 'category' => $category->slug]) }}" class="inline-flex items-center gap-1.5 rounded-full border border-hair bg-white px-4 py-2 text-sm font-medium text-ink transition hover:border-emerald hover:text-emerald">
                        <svg class="h-3.5 w-3.5 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.5-7-10a7 7 0 0 1 14 0c0 5.5-7 10-7 10z"/><circle cx="12" cy="11" r="2.5"/></svg>
                        {{ $category->name }} in {{ $loc['label'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- Businesses --}}
<section class="pb-24 pt-8">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        @if ($businesses->count())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($businesses as $b)
                    @php $offer = $b->activeOffers->first(); @endphp
                    <a href="/app?b={{ $b->slug }}" target="_blank" rel="noopener" class="card-hover group overflow-hidden rounded-card border border-hair bg-white">
                        <div class="relative h-44 overflow-hidden bg-[#e2e8f0]">
                            @if ($b->photos)
                                <img src="{{ $b->photos[0] }}" alt="{{ $b->name }} - {{ $category->name }} in Newcastle NE1" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <div class="h-full w-full bg-gradient-to-br from-emerald-soft to-[#e2e8f0]"></div>
                            @endif
                            @if ($b->plan !== 'free')<span class="absolute right-3 top-3 rounded-full bg-black/70 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white">Sponsored</span>@endif
                            @if ($offer)<span class="absolute left-3 top-3 rounded-lg bg-emerald px-2.5 py-1 text-xs font-extrabold text-white">{{ $offer->badge }}</span>@endif
                        </div>
                        <div class="p-5">
                            <h2 class="text-lg font-bold text-ink">{{ $b->name }}</h2>
                            <div class="mt-1 flex items-center gap-1 text-sm text-muted"><span class="text-amber-500">★</span> {{ number_format((float) $b->rating, 1) }} <span class="text-hair">·</span> {{ $b->postcode }}</div>
                            @if ($offer)<div class="mt-3 rounded-lg bg-emerald-soft px-3 py-2 text-sm font-semibold text-emerald">{{ $offer->title }}</div>@endif
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-card border border-hair bg-white py-16 text-center">
                <p class="text-muted">No independent {{ strtolower($category->name) }} on locolie just yet. We are adding new local spots every week, so pop back soon.</p>
                <a href="/for-business" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:text-ink">Run an independent {{ strtolower($category->name) }} business? Be the first to list it, free →</a>
            </div>
        @endif

        {{-- FAQ (mirrors the FAQPage rich snippet) --}}
        <div class="mt-16 max-w-3xl">
            <h2 class="text-2xl font-extrabold tracking-tight text-ink">Frequently asked questions</h2>
            <div class="mt-5 divide-y divide-hair overflow-hidden rounded-card border border-hair bg-white">
                @foreach ($catFaqs as $i => $faq)
                    <details class="group" @if($i === 0) open @endif>
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-left text-base font-semibold text-ink transition hover:bg-black/[0.015]">
                            <span>{{ $faq['q'] }}</span>
                            <svg class="h-4 w-4 shrink-0 text-muted transition-transform duration-300 group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
                        </summary>
                        <div class="px-5 pb-5 text-base leading-relaxed text-muted">{{ $faq['a'] }}</div>
                    </details>
                @endforeach
            </div>
        </div>

        {{-- Other categories --}}
        <div class="mt-16">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-muted">Back more indies</h2>
            <div class="flex flex-wrap gap-2">
                @foreach ($categories->where('slug', '!=', $category->slug) as $c)
                    <a href="/category/{{ $c->slug }}" class="inline-flex items-center gap-1.5 rounded-full border border-hair bg-white px-4 py-2 text-sm font-medium text-ink transition hover:border-emerald hover:text-emerald">
                        <svg class="h-3.5 w-3.5 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($c->slug) !!}</svg>
                        {{ $c->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</section>

@endsection
