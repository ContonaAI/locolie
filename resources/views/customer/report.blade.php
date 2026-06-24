@extends('customer.layout')

@section('title', 'Your locolie')

@php
  $firstName = $report['name'] ? trim(explode(' ', trim($report['name']))[0]) : null;
  $saved = (float) ($report['kpis']['saved'] ?? 0);
  // Whole pounds read cleaner for big numbers; show pence only for small/odd totals.
  $savedDisplay = ($saved == floor($saved)) ? number_format($saved, 0) : number_format($saved, 2);
  $redemptions = (int) ($report['kpis']['redemptions'] ?? 0);
  $businesses = (int) ($report['kpis']['businesses'] ?? 0);
  $fav = $report['kpis']['favourite_category'] ?? null;
@endphp

@section('content')

@if (! ($report['found'] ?? false))
  {{-- ───────────── Empty state ───────────── --}}
  <div class="pt-16 pb-10 text-center animate-pop">
    <div class="inline-flex items-center justify-center h-20 w-20 rounded-3xl bg-emerald-soft mb-6">
      <svg class="h-10 w-10 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18l-2 13H5L3 6Z"/><path d="M3 6 2 3"/><circle cx="9" cy="21" r="1"/><circle cx="17" cy="21" r="1"/></svg>
    </div>
    <h1 class="text-2xl font-extrabold tracking-tight text-slate-900">No locolie yet{{ $firstName ? ', '.$firstName : '' }}</h1>
    <p class="mt-3 text-base text-slate-600 leading-relaxed">We couldn't find any redeemed offers for this email. Grab a deal at an independent shop near you and your savings will show up here.</p>
    <a href="{{ route('app') }}" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-emerald px-6 py-3.5 text-base font-bold text-white transition hover:bg-emerald-700">
      Find local offers
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
  </div>
@else

  {{-- ───────────── 1. Hero ───────────── --}}
  <section class="pt-10 pb-8 text-center animate-pop">
    <p class="text-sm font-semibold uppercase tracking-widest text-emerald mb-3">Your locolie</p>
    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 leading-tight">
      @if ($firstName) Here's your locolie,<br>{{ $firstName }} @else Here's your locolie @endif
    </h1>

    <div class="mt-8 rounded-3xl bg-gradient-to-br from-emerald-600 to-emerald-700 text-white px-6 py-9 shadow-lg shadow-emerald/20">
      <p class="text-sm font-medium text-emerald-50/90">You've saved</p>
      <p class="mt-1 text-6xl font-extrabold tracking-tight leading-none">£{{ $savedDisplay }}</p>
      <p class="mt-3 text-sm font-medium text-emerald-50/90">shopping local with locolie</p>
    </div>
  </section>

  {{-- ───────────── 2. Stat trio ───────────── --}}
  <section class="grid grid-cols-3 gap-3">
    <div class="rounded-2xl bg-white border border-slate-200 p-4 text-center">
      <p class="text-3xl font-extrabold text-slate-900 leading-none">{{ number_format($redemptions) }}</p>
      <p class="mt-1.5 text-xs font-medium text-slate-500 leading-tight">Offers<br>redeemed</p>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 p-4 text-center">
      <p class="text-3xl font-extrabold text-slate-900 leading-none">{{ number_format($businesses) }}</p>
      <p class="mt-1.5 text-xs font-medium text-slate-500 leading-tight">Local<br>businesses</p>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 p-4 text-center flex flex-col justify-center">
      <p class="text-base font-extrabold text-slate-900 leading-tight break-words">{{ $fav ?? '-' }}</p>
      <p class="mt-1.5 text-xs font-medium text-slate-500 leading-tight">Favourite<br>category</p>
    </div>
  </section>

  {{-- ───────────── 3. Impact line ───────────── --}}
  <section class="mt-5">
    <div class="rounded-2xl bg-emerald-soft/60 border border-emerald-100 px-5 py-4 text-center">
      <p class="text-[15px] font-semibold text-emerald-900 leading-relaxed">
        You've supported {{ $businesses }} independent local {{ \Illuminate\Support\Str::plural('business', $businesses) }} and saved £{{ $savedDisplay }} doing it.@if ($fav) Looks like {{ $fav }} is your thing.@endif
      </p>
    </div>
  </section>

  {{-- ───────────── 4. Your places ───────────── --}}
  @if (! empty($report['places']))
  <section class="mt-9">
    <h2 class="text-lg font-extrabold text-slate-900 mb-4">Your places</h2>
    <div class="space-y-2.5">
      @foreach ($report['places'] as $place)
        <a href="{{ route('site.business', $place['slug']) }}" class="flex items-center gap-3.5 rounded-2xl bg-white border border-slate-200 p-3 transition hover:border-emerald hover:shadow-sm">
          @if (! empty($place['logo']))
            <img src="{{ $place['logo'] }}" alt="{{ $place['name'] }}" class="h-12 w-12 shrink-0 rounded-full object-cover bg-slate-100">
          @else
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-base font-bold text-white" style="background-color: {{ $place['color'] }}">{{ $place['initials'] }}</span>
          @endif
          <div class="min-w-0 flex-1">
            <p class="font-bold text-slate-900 truncate">{{ $place['name'] }}</p>
            @if (! empty($place['category']))
              <p class="text-sm text-slate-500 truncate">{{ $place['category'] }}</p>
            @endif
          </div>
          <span class="shrink-0 rounded-full bg-emerald-soft px-2.5 py-1 text-xs font-bold text-emerald">{{ $place['visits'] }} {{ \Illuminate\Support\Str::plural('visit', $place['visits']) }}</span>
        </a>
      @endforeach
    </div>
  </section>
  @endif

  {{-- ───────────── 5. Recent activity timeline ───────────── --}}
  @if (! empty($report['timeline']))
  <section class="mt-9">
    <h2 class="text-lg font-extrabold text-slate-900 mb-4">Recent activity</h2>
    <ol class="relative border-l-2 border-slate-200 ml-2 space-y-5">
      @foreach ($report['timeline'] as $item)
        <li class="relative pl-6">
          <span class="absolute -left-[7px] top-1.5 h-3 w-3 rounded-full bg-emerald ring-4 ring-slate-50"></span>
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="font-bold text-slate-900 truncate">{{ $item['business'] ?? 'Local business' }}</p>
              @if (! empty($item['offer']))
                <p class="text-sm text-slate-500 truncate">{{ $item['offer'] }}</p>
              @endif
              <div class="mt-1.5 flex items-center gap-2 flex-wrap">
                @if (! empty($item['badge']))
                  <span class="rounded-md bg-slate-900 px-2 py-0.5 text-[11px] font-bold text-white">{{ $item['badge'] }}</span>
                @endif
                <span class="text-xs font-semibold text-emerald">saved £{{ number_format((float) $item['saved'], 2) }}</span>
              </div>
            </div>
            @if (! empty($item['when']))
              <span class="shrink-0 text-xs font-medium text-slate-400 whitespace-nowrap">{{ $item['when']->format('j M') }}</span>
            @endif
          </div>
        </li>
      @endforeach
    </ol>
  </section>
  @endif

  {{-- ───────────── 6. Categories ───────────── --}}
  @if (! empty($report['categories']))
    @php $catMax = max($report['categories']); @endphp
    <section class="mt-9">
      <h2 class="text-lg font-extrabold text-slate-900 mb-4">Where you shop</h2>
      <div class="rounded-2xl bg-white border border-slate-200 p-5 space-y-3.5">
        @foreach ($report['categories'] as $catName => $catCount)
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <span class="text-sm font-semibold text-slate-700">{{ $catName }}</span>
              <span class="text-sm font-bold text-slate-900">{{ $catCount }}</span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
              <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-emerald-600" style="width: {{ $catMax > 0 ? max(8, round($catCount / $catMax * 100)) : 0 }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endif

  {{-- ───────────── 7. Footer CTA ───────────── --}}
  <section class="mt-10 text-center">
    <a href="{{ route('app') }}" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald px-6 py-3.5 text-base font-bold text-white transition hover:bg-emerald-700 active:scale-[.99]">
      Find more local offers
      <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </a>
    <p class="mt-5 text-xs text-slate-400 leading-relaxed">
      Generated {{ $report['generated_at']->format('j M Y') }}.<br>
      Savings shown are estimates based on the offers you redeemed.
    </p>
  </section>

@endif
@endsection
