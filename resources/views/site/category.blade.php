@extends('site.layout')
@section('title', $category->name.' in Newcastle NE1 - independent '.$category->name.' on locolie')
@section('meta_description', 'Discover independent '.strtolower($category->name).' in Newcastle NE1 on locolie - see live offers, ratings and redeem deals at the till.')

@section('content')

{{-- Hero --}}
<section class="relative overflow-hidden hero-grid">
    <div class="mesh" aria-hidden="true" data-parallax="0.1"><i class="b1"></i><i class="b2"></i></div>
    <div class="relative z-10 mx-auto max-w-6xl px-5 pb-10 pt-32 sm:px-6 lg:pt-40">
        <nav class="mb-5 flex items-center gap-2 text-sm text-muted" aria-label="Breadcrumb">
            <a href="/" class="hover:text-ink">Home</a><span>/</span>
            <a href="/#categories" class="hover:text-ink">Categories</a><span>/</span>
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
    </div>
</section>

{{-- Businesses --}}
<section class="pb-24">
    <div class="mx-auto max-w-6xl px-5 sm:px-6">
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
                <p class="text-muted">No live {{ strtolower($category->name) }} yet - check back soon.</p>
                <a href="/for-business" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald hover:text-ink">Run a {{ strtolower($category->name) }} business? List it free →</a>
            </div>
        @endif

        {{-- Other categories --}}
        <div class="mt-16">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wider text-muted">Other categories</h2>
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
