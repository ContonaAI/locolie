@extends('site.layout')
@section('title', 'Local business directory | independent shops by area & category | locolie')
@section('meta_description', 'The locolie directory of independent local businesses - browse every area and category to find real indies near you, with offers you can only get on locolie.')

@push('head')
    <link rel="canonical" href="{{ url()->current() }}">
@endpush

@section('content')

{{-- ===================== HERO ===================== --}}
<section class="relative overflow-hidden hero-grid">
    <div class="mesh" aria-hidden="true" data-parallax="0.1"><i class="b1"></i><i class="b2"></i></div>
    <div class="relative z-10 mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 pb-10 pt-32 sm:px-6 lg:pt-40">
        <nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-muted" aria-label="Breadcrumb">
            <a href="{{ url('/') }}" class="hover:text-ink">Home</a><span aria-hidden="true">/</span>
            <span class="font-semibold text-ink">Local</span>
        </nav>
        <h1 class="max-w-3xl text-3xl font-extrabold tracking-tight sm:text-5xl">Local business directory</h1>
        <p class="mt-4 max-w-2xl text-base leading-relaxed text-muted">Every independent business on locolie, grouped by area and category. Skip the chains, back your high street and find real indies near you - with live offers you redeem at the till.</p>
    </div>
</section>

{{-- ===================== AREAS ===================== --}}
@if ($locations->count())
    <section class="pb-4">
        <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
            <h2 class="mb-5 text-2xl font-extrabold tracking-tight text-ink">Browse by area</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($locations as $l)
                    <a href="{{ route('seo.area', ['area' => $l['slug']]) }}" class="card-hover group flex items-center justify-between gap-3 rounded-card border border-hair bg-white px-5 py-4">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>
                            </span>
                            <span class="font-bold text-ink group-hover:text-emerald">{{ $l['label'] }}</span>
                        </span>
                        <span class="rounded-full bg-black/[0.04] px-2.5 py-1 text-xs font-semibold text-muted">{{ $l['count'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

{{-- ===================== CATEGORY x AREA MATRIX (crawl hub) ===================== --}}
@php $topLocations = $locations->take(8); @endphp
<section class="pb-20 pt-12">
    <div class="mx-auto max-w-7xl 2xl:max-w-[1500px] px-5 sm:px-6">
        <h2 class="mb-5 text-2xl font-extrabold tracking-tight text-ink">Browse by category &amp; area</h2>
        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ($parents as $parent)
                <div class="rounded-card border border-hair bg-white p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! \App\Models\Category::iconPath($parent->slug) !!}</svg>
                        </span>
                        <h3 class="text-lg font-bold text-ink">
                            <a href="{{ route('site.category', $parent->slug) }}" class="transition hover:text-emerald">{{ $parent->name }}</a>
                        </h3>
                    </div>

                    @if ($topLocations->count())
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach ($topLocations as $l)
                                <a href="{{ route('seo.landing', ['area' => $l['slug'], 'category' => $parent->slug]) }}" class="inline-flex items-center gap-1.5 rounded-full border border-hair bg-white px-3.5 py-1.5 text-sm font-medium text-ink transition hover:border-emerald hover:text-emerald">
                                    {{ $parent->name }} in {{ $l['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if ($parent->children->count())
                        <div class="mt-4 border-t border-hair pt-4">
                            <div class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-muted">Sub-categories</div>
                            <div class="flex flex-wrap gap-x-3 gap-y-1.5">
                                @foreach ($parent->children as $child)
                                    <a href="{{ route('site.category', $child->slug) }}" class="text-sm text-muted transition hover:text-emerald">{{ $child->name }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

@endsection
