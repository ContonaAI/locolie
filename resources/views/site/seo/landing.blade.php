@extends('site.layout')
@section('title', $content['title'])
@section('meta_description', $content['meta_description'])

@push('head')
    <link rel="canonical" href="{{ url()->current() }}">
    @include('site.seo._jsonld', [
        'breadcrumbs' => [
            ['name' => 'Home', 'url' => url('/')],
            ['name' => 'Local', 'url' => route('seo.index')],
            ['name' => $location['label'], 'url' => route('seo.area', ['area' => $location['slug']])],
            ['name' => $category->name, 'url' => url()->current()],
        ],
        'businesses' => $businesses,
        'faqs' => $content['faqs'],
    ])
@endpush

@section('content')

{{-- ===================== HERO ===================== --}}
<section class="relative overflow-hidden hero-grid">
    <div class="mesh" aria-hidden="true" data-parallax="0.1"><i class="b1"></i><i class="b2"></i></div>
    <div class="relative z-10 mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 pb-10 pt-32 sm:px-6 lg:pt-40">
        <nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-muted" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-ink">Home</a><span aria-hidden="true">/</span>
            <a href="{{ route('seo.index') }}" class="hover:text-ink">Local</a><span aria-hidden="true">/</span>
            <a href="{{ route('seo.area', ['area' => $location['slug']]) }}" class="hover:text-ink">{{ $location['label'] }}</a><span aria-hidden="true">/</span>
            <span class="font-semibold text-ink">{{ $category->name }}</span>
        </nav>
        <div class="flex items-center gap-4">
            <span class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-emerald-soft text-emerald shadow-sm">
                <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($category->slug) !!}</svg>
            </span>
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight sm:text-5xl">{{ $content['h1'] }}</h1>
                <p class="mt-1 text-muted">{{ $content['count'] }} independent {{ \Illuminate\Support\Str::plural('business', $content['count']) }} in {{ $location['label'] }}</p>
            </div>
        </div>
        @if (! empty($content['intro'][0]))
            <p class="mt-6 max-w-2xl text-base leading-relaxed text-muted">{{ $content['intro'][0] }}</p>
        @endif
    </div>
</section>

{{-- ===================== BUSINESSES ===================== --}}
<section class="pb-16">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        @if ($businesses->count())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($businesses as $b)
                    @php $offer = $b->activeOffers->first(); @endphp
                    <a href="/app?b={{ $b->slug }}" target="_blank" rel="noopener" class="card-hover group overflow-hidden rounded-card border border-hair bg-white">
                        <div class="relative h-44 overflow-hidden bg-[#e2e8f0]">
                            @if ($b->photos)
                                <img src="{{ $b->photos[0] }}" alt="{{ $b->name }} - {{ $category->name }} in {{ $location['label'] }}" loading="lazy" decoding="async" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
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
            {{-- Graceful empty state: still useful, points to nearby + sibling links below. --}}
            <div class="rounded-card border border-hair bg-white px-6 py-16 text-center">
                <span class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-soft text-emerald">
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($category->slug) !!}</svg>
                </span>
                <h2 class="text-xl font-bold text-ink">No {{ strtolower($category->name) }} listed in {{ $location['label'] }} just yet</h2>
                <p class="mx-auto mt-2 max-w-md text-muted">We are scouting the best independent {{ strtolower($category->name) }} every week. In the meantime, try a nearby area or browse other local services below.</p>
                <a href="/for-business" class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:text-ink">Run a {{ strtolower($category->name) }} business here? List it free →</a>
            </div>
        @endif

        @if (! empty($content['intro'][1]))
            <div class="mx-auto mt-12 max-w-3xl">
                <p class="text-base leading-relaxed text-muted">{{ $content['intro'][1] }}</p>
            </div>
        @endif

        {{-- Sub-types of this category --}}
        @if ($children->count())
            <div class="mt-14">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-muted">Explore {{ strtolower($category->name) }} sub-types</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($children as $child)
                        <a href="{{ route('site.category', $child->slug) }}" class="inline-flex items-center gap-1.5 rounded-full border border-hair bg-white px-4 py-2 text-sm font-medium text-ink transition hover:border-emerald hover:text-emerald">
                            <svg class="h-3.5 w-3.5 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($child->slug) !!}</svg>
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Internal links: same category in nearby areas --}}
        @if ($nearby->count())
            <div class="mt-14">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-muted">{{ $category->name }} in nearby areas</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($nearby as $n)
                        <a href="{{ route('seo.landing', ['area' => $n['slug'], 'category' => $category->slug]) }}" class="inline-flex items-center gap-1.5 rounded-full border border-hair bg-white px-4 py-2 text-sm font-medium text-ink transition hover:border-emerald hover:text-emerald">
                            {{ $category->name }} in {{ $n['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Internal links: other local services in this area --}}
        @if ($siblingCategories->count())
            <div class="mt-10">
                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-muted">Other local services in {{ $location['label'] }}</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($siblingCategories as $sib)
                        <a href="{{ route('seo.landing', ['area' => $location['slug'], 'category' => $sib->slug]) }}" class="inline-flex items-center gap-1.5 rounded-full border border-hair bg-white px-4 py-2 text-sm font-medium text-ink transition hover:border-emerald hover:text-emerald">
                            <svg class="h-3.5 w-3.5 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($sib->slug) !!}</svg>
                            {{ $sib->name }} in {{ $location['label'] }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- FAQ --}}
        @if (! empty($content['faqs']))
            <div class="mt-16 max-w-3xl">
                <h2 class="text-2xl font-extrabold tracking-tight text-ink">Frequently asked questions</h2>
                <div class="mt-5 divide-y divide-hair overflow-hidden rounded-card border border-hair bg-white">
                    @foreach ($content['faqs'] as $i => $faq)
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
        @endif
    </div>
</section>

@endsection
