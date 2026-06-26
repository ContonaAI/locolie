@extends('site.layout')
@section('title', 'locolie - back the indies')
@section('meta_description', 'Back your high street. Discover real discounts from the independent shops near you in Newcastle NE1.')

@push('head')
<style>
  /* ── home-1: bold full-screen app showcase. Brand easing matches the logo. ── */
  .h1-wrap { font-family: 'Inter', sans-serif; }
  .h1-eyebrow, .h1-up, .h1-stat, .h1-phone { opacity: 0; }
  .has-js .h1-down { animation: h1Down .6s cubic-bezier(.22,1,.36,1) both; }
  .has-js .h1-up   { animation: h1Up .7s cubic-bezier(.22,1,.36,1) both; }
  @keyframes h1Down { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: none; } }
  @keyframes h1Up   { from { opacity: 0; transform: translateY(34px); } to { opacity: 1; transform: none; } }

  /* Clip-reveal heading: each word slides up inside an overflow-hidden wrapper. */
  .h1-clip { overflow: hidden; display: block; }
  .h1-word { display: block; transform: translateY(110%); }
  .has-js .h1-word { animation: h1Word .8s cubic-bezier(.22,1,.36,1) forwards; }
  @keyframes h1Word { to { transform: translateY(0); } }

  /* Soft emerald glow + drifting pin watermarks, on-brand. */
  .h1-bg { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 0; }
  .h1-glow { position: absolute; border-radius: 50%; filter: blur(70px); opacity: .55; }

  /* No-JS / reduced-motion: show everything, no transforms. */
  body:not(.has-js) .h1-eyebrow, body:not(.has-js) .h1-up, body:not(.has-js) .h1-stat, body:not(.has-js) .h1-phone { opacity: 1; }
  body:not(.has-js) .h1-word { transform: none; }
  @media (prefers-reduced-motion: reduce) {
    .h1-down, .h1-up, .h1-word { animation: none !important; }
    .h1-eyebrow, .h1-up, .h1-stat, .h1-phone { opacity: 1 !important; }
    .h1-word { transform: none !important; }
  }
</style>
@endpush

@section('content')
@php
    $arrow = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:-0.12em"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>';
    $headingWords = ['Back', 'The', 'Indies'];
    $stats = [
        ['+', number_format($stats['businesses']), "Indie\nshops"],
        ['+', number_format($stats['offers']), "Live\ndeals"],
        ['+', number_format($stats['categories']), "Local\ncategories"],
    ];
@endphp

<section class="h1-wrap relative flex min-h-screen flex-col overflow-hidden bg-[#fafafa] text-black">
  {{-- background wash --}}
  <div class="h1-bg" aria-hidden="true">
    <span class="h1-glow" style="width:42rem;height:42rem;left:-8%;top:-12%;background:radial-gradient(circle at 30% 30%,#6ee7b7,transparent 70%)"></span>
    <span class="h1-glow" style="width:34rem;height:34rem;right:-6%;bottom:-10%;background:radial-gradient(circle at 60% 40%,#a7f3d0,transparent 70%)"></span>
    <div class="absolute inset-0" style="background-image:radial-gradient(#0000000a 1px, transparent 1px);background-size:24px 24px"></div>
  </div>

  {{-- ── top: eyebrow ── --}}
  <div class="relative z-10 flex items-center justify-between px-5 pt-24 sm:px-8 md:px-12 md:pt-28">
    <span class="h1-eyebrow h1-down inline-flex items-center gap-2.5 text-xs font-semibold uppercase tracking-widest sm:text-sm" style="animation-delay:0s">
      <span class="grid h-8 w-8 place-items-center rounded-full border-2 border-[#059669]"><span class="h-2.5 w-2.5 rounded-full bg-[#059669]"></span></span>
      locolie
    </span>
    <span class="h1-eyebrow h1-down inline-flex items-center gap-2 rounded-full border border-black/10 bg-white/70 px-3.5 py-1.5 text-[10px] font-semibold uppercase tracking-widest backdrop-blur sm:text-xs" style="animation-delay:.1s">
      <span class="relative flex h-2 w-2"><span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-[#059669] opacity-75"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-[#059669]"></span></span>
      Live in Newcastle NE1
    </span>
  </div>

  {{-- ── middle: heading + CTA (left) · app mockup (right) ── --}}
  <div class="relative z-10 flex flex-1 items-center px-5 py-10 sm:px-8 md:px-12 md:py-0">
    <div class="mx-auto grid w-full max-w-7xl items-center gap-10 lg:grid-cols-[1.15fr_1fr] lg:gap-8">

      {{-- left --}}
      <div class="order-2 lg:order-1">
        <h1 class="font-semibold uppercase leading-[0.88] tracking-tight text-black" style="font-size:clamp(2.6rem,9vw,8.5rem)">
          @foreach ($headingWords as $wi => $word)
            <span class="h1-clip"><span class="h1-word" style="animation-delay:{{ 0.4 + $wi * 0.14 }}s">{{ $word }}</span></span>
          @endforeach
        </h1>

        <p class="h1-up mt-6 max-w-md text-xs font-semibold uppercase tracking-widest text-black/70 sm:text-sm" style="animation-delay:.85s">
          Real discounts from the independent shops, pubs and makers on your high street.
        </p>

        <div class="h1-up mt-7 flex flex-wrap items-center gap-x-7 gap-y-3" style="animation-delay:.95s">
          <a href="/app" class="group inline-flex items-center gap-2 text-base font-semibold uppercase tracking-widest text-[#059669] sm:text-xl">
            Find local deals
            <span class="transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" style="width:20px;height:20px">{!! $arrow !!}</span>
          </a>
          <a href="/for-business" class="inline-flex items-center gap-2 text-base font-semibold uppercase tracking-widest text-black/55 transition-colors hover:text-black sm:text-lg">
            List your business
          </a>
        </div>
      </div>

      {{-- right: the animated app mockup = "the app in use" --}}
      <div class="h1-phone order-1 flex justify-center lg:order-2 lg:justify-end" style="animation-delay:.5s">
        <div class="h1-up" style="animation-delay:.5s">
          @include('site._appwalk', ['src' => '/app', 'cards' => $featured])
        </div>
      </div>
    </div>
  </div>

  {{-- ── bottom: stats ── --}}
  <div class="relative z-10 px-5 pb-10 sm:px-8 md:px-12 md:pb-14">
    <div class="mx-auto flex max-w-7xl items-end justify-center gap-7 sm:gap-12 md:justify-end md:gap-16">
      @foreach ($stats as $si => [$plus, $num, $label])
        <div class="h1-stat h1-up text-right" style="animation-delay:{{ 1.0 + $si * 0.12 }}s">
          <div class="font-semibold leading-none text-black" style="font-size:clamp(1.5rem,5vw,3.5rem)">
            <span class="text-[#059669]" style="font-size:.5em;vertical-align:.25em">{{ $plus }}</span>{{ $num }}
          </div>
          <div class="mt-1.5 whitespace-pre-line text-[10px] font-semibold uppercase leading-tight tracking-widest text-black/70 sm:text-xs md:text-sm">{{ $label }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>
@endsection
