{{-- Animated, self-contained phone that walks through the app being USED: it
     auto-cycles Discover -> Offer -> Scan -> Savings, with a moving tap cursor and
     a caption that tracks each step. Tapping the frame opens the real app at $src.
     Props: $src, $class, $dark (bool), $cards (collection|null). --}}
@php
    $dark = $dark ?? false;
    $realCard = (isset($cards) && $cards && count($cards)) ? collect($cards)->first() : null;

    // Deterministic decorative QR so the "scan" screen looks the part.
    $qn = 17; $qc = 100 / $qn; $qmods = '';
    for ($r = 0; $r < $qn; $r++) {
        for ($k = 0; $k < $qn; $k++) {
            if (($r < 6 && $k < 6) || ($r < 6 && $k >= $qn - 6) || ($r >= $qn - 6 && $k < 6)) continue;
            if (((($r + 1) * ($k + 3) + $r * 5 + $k * 2) % 5) < 2) {
                $qmods .= '<rect x="'.round($k * $qc, 2).'" y="'.round($r * $qc, 2).'" width="'.round($qc + .3, 2).'" height="'.round($qc + .3, 2).'"/>';
            }
        }
    }
    $qfind = function ($gr, $gk) use ($qc) {
        $x = $gk * $qc; $y = $gr * $qc; $s = 6 * $qc;
        return '<rect x="'.round($x, 2).'" y="'.round($y, 2).'" width="'.round($s, 2).'" height="'.round($s, 2).'" rx="3" fill="#0a0a0a"/>'
            .'<rect x="'.round($x + $qc, 2).'" y="'.round($y + $qc, 2).'" width="'.round($s - 2 * $qc, 2).'" height="'.round($s - 2 * $qc, 2).'" rx="2" fill="#fff"/>'
            .'<rect x="'.round($x + 2 * $qc, 2).'" y="'.round($y + 2 * $qc, 2).'" width="'.round($s - 4 * $qc, 2).'" height="'.round($s - 4 * $qc, 2).'" rx="1.5" fill="#059669"/>';
    };
    $qr = '<svg viewBox="0 0 100 100" class="h-full w-full"><g fill="#0a0a0a">'.$qmods.'</g>'.$qfind(0, 0).$qfind(0, $qn - 6).$qfind($qn - 6, 0).'</svg>';

    $steps = ['Discover offers near you', 'Reveal your code', 'Scan it at the till', 'Watch your savings add up'];
@endphp

<div class="{{ $class ?? '' }} relative"
     x-data="{ i: 0, n: 4, t: null, paused: false,
               start() { this.t = setInterval(() => { if (!this.paused) this.i = (this.i + 1) % this.n; }, 2800); },
               go(k) { this.i = k; clearInterval(this.t); this.start(); } }"
     x-init="start()" @mouseenter="paused = true" @mouseleave="paused = false">

  {{-- caption that tracks the step --}}
  <div class="absolute -top-9 left-1/2 hidden -translate-x-1/2 sm:block">
    <template x-for="(s, k) in {{ \Illuminate\Support\Js::from($steps) }}" :key="k">
      <span x-show="i === k" x-transition.opacity
            class="whitespace-nowrap rounded-full {{ $dark ? 'bg-white/10 text-white' : 'bg-ink/90 text-white' }} px-3.5 py-1.5 text-xs font-semibold shadow-lg" x-text="s"></span>
    </template>
  </div>

  <a href="{{ $src }}" target="_blank" rel="noopener"
     class="group relative block overflow-hidden rounded-[2.7rem] border-[12px] {{ $dark ? 'border-[#1c1c1c] ring-1 ring-white/10' : 'border-[#111]' }} bg-[#111] shadow-[0_30px_70px_-25px_rgba(0,0,0,0.55)]"
     style="width:300px;max-width:82vw;" aria-label="Open the locolie app">
    <span class="absolute -left-[14px] top-24 z-30 h-12 w-[3px] rounded-l bg-[#222]"></span>
    <span class="absolute -right-[14px] top-28 z-30 h-16 w-[3px] rounded-r bg-[#222]"></span>
    <div class="flex items-center justify-center bg-black" style="height:24px;"><span class="h-[16px] w-20 rounded-full bg-[#0a0a0a] ring-1 ring-white/10"></span></div>

    <div class="relative bg-[#eef1f4]" style="height:600px;">

      {{-- ============ SCREEN 0 · DISCOVER ============ --}}
      <div x-show="i === 0" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-x-6" x-transition:enter-end="opacity-100 translate-x-0" class="absolute inset-0 flex flex-col">
        <div class="bg-[#0a0a0a] px-3 pb-2.5 pt-2 text-white">
          <div class="flex items-center justify-between">
            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-white/70"><svg class="h-3 w-3 text-emerald" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a8 8 0 0 0-8 8c0 5.25 8 12 8 12s8-6.75 8-12a8 8 0 0 0-8-8Zm0 5.5A2.5 2.5 0 1 1 12 12.5 2.5 2.5 0 0 1 12 7.5Z"/></svg>Newcastle</span>
            @php $mpin = '<span class="inline-block h-2 w-2 bg-emerald align-middle" style="border-radius:50% 50% 50% 0;transform:rotate(-45deg)"></span>'; @endphp
            <span class="text-sm font-extrabold lowercase tracking-tight">l{!! $mpin !!}c{!! $mpin !!}lie</span>
            <svg class="h-4 w-4 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.7 21a2 2 0 0 1-3.4 0"/></svg>
          </div>
          <div class="mt-2 flex items-center gap-2 rounded-xl bg-white/10 px-3 py-2">
            <svg class="h-3.5 w-3.5 text-white/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <span class="text-[11px] text-white/50">Search shops &amp; offers</span>
          </div>
        </div>
        <div class="flex-1 overflow-hidden px-3 pt-3">
          <div class="mb-2 text-[13px] font-extrabold text-ink">Featured today</div>
          @if ($realCard)
            @php $o = $realCard->activeOffers->first(); @endphp
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
              <div class="relative h-28 bg-[#e2e8f0]">
                <img src="{{ $realCard->photos[0] }}" alt="{{ $realCard->name }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
                @if ($o)<span class="absolute left-2 top-2 rounded-md bg-emerald px-1.5 py-0.5 text-[9px] font-extrabold text-white">{{ $o->badge }}</span>@endif
              </div>
              <div class="p-2.5">
                <div class="truncate text-[12px] font-bold text-ink">{{ $realCard->name }}</div>
                <div class="mt-0.5 flex items-center gap-1 text-[9px] text-muted"><span class="text-amber-500">★</span>{{ number_format((float) $realCard->rating, 1) }} · {{ $realCard->category?->name }}</div>
                @if ($o)<div class="mt-1.5 truncate rounded-md bg-emerald-soft px-2 py-1 text-[10px] font-bold text-emerald">{{ $o->title }}</div>@endif
              </div>
            </div>
          @else
            <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
              <div class="relative h-28 bg-gradient-to-br from-[#3a2f2a] to-[#1a1410]"><span class="absolute left-2 top-2 rounded-md bg-emerald px-1.5 py-0.5 text-[9px] font-extrabold text-white">25% OFF</span></div>
              <div class="p-2.5"><div class="text-[12px] font-bold text-ink">The Corner Café</div><div class="mt-0.5 text-[9px] text-muted"><span class="text-amber-500">★</span> 4.9 · Coffee</div><div class="mt-1.5 rounded-md bg-emerald-soft px-2 py-1 text-[10px] font-bold text-emerald">Any breakfast, all week</div></div>
            </div>
          @endif
          <div class="mt-2.5 grid grid-cols-2 gap-2.5">
            @foreach (['Newcastle Fitness','Bones Barbers'] as $idx => $nm)
              <div class="overflow-hidden rounded-2xl bg-white shadow-sm">
                <div class="h-14 bg-gradient-to-br {{ $idx ? 'from-[#1f2937] to-[#0b1220]' : 'from-[#0e7490] to-[#155e75]' }}"></div>
                <div class="p-2"><div class="truncate text-[10px] font-bold text-ink">{{ $nm }}</div><div class="mt-1 rounded bg-emerald-soft px-1.5 py-0.5 text-[8px] font-bold text-emerald">Free taster</div></div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      {{-- ============ SCREEN 1 · OFFER DETAIL ============ --}}
      <div x-show="i === 1" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-x-6" x-transition:enter-end="opacity-100 translate-x-0" class="absolute inset-0 flex flex-col bg-white">
        <div class="relative h-44 bg-gradient-to-br from-[#3a2f2a] to-[#1a1410]">
          <div class="absolute left-3 top-3 flex h-7 w-7 items-center justify-center rounded-full bg-black/40 text-white"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></div>
          <span class="absolute left-3 bottom-3 rounded-lg bg-emerald px-2 py-1 text-[11px] font-extrabold text-white">25% OFF</span>
        </div>
        <div class="flex-1 px-4 pt-4">
          <div class="text-[15px] font-extrabold text-ink">The Corner Café</div>
          <div class="mt-0.5 flex items-center gap-1 text-[10px] text-muted"><span class="text-amber-500">★</span> 4.9 · Coffee · 0.3 mi</div>
          <div class="mt-3 rounded-xl border border-emerald-soft bg-emerald-soft/40 p-3">
            <div class="text-[12px] font-bold text-ink">Any breakfast, all week</div>
            <div class="mt-0.5 text-[10px] text-muted">Show the code at the till. One per visit.</div>
          </div>
          <div class="mt-3 rounded-xl border-2 border-dashed border-emerald/40 bg-white p-3 text-center">
            <div class="text-[9px] font-bold uppercase tracking-widest text-muted">Your code</div>
            <div class="mt-1 font-mono text-xl font-extrabold tracking-[0.2em] text-emerald">LOCO-7K2</div>
          </div>
        </div>
        <div class="px-4 pb-5">
          <div class="rounded-xl bg-emerald py-3 text-center text-[13px] font-bold text-white shadow-lg shadow-emerald/30">Reveal &amp; redeem</div>
        </div>
      </div>

      {{-- ============ SCREEN 2 · SCAN AT TILL ============ --}}
      <div x-show="i === 2" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-x-6" x-transition:enter-end="opacity-100 translate-x-0" class="absolute inset-0 flex flex-col items-center justify-center bg-[#0a0a0a] px-6 text-center text-white">
        <div class="text-[13px] font-bold uppercase tracking-[0.18em] text-emerald-soft">Show this at the till</div>
        <div class="relative mt-5 h-44 w-44 overflow-hidden rounded-2xl bg-white p-3">
          {!! $qr !!}
          <div class="pointer-events-none absolute inset-x-0 h-0.5 bg-emerald/80 shadow-[0_0_12px_4px_rgba(5,150,105,.7)]" style="animation: scanline 2.2s ease-in-out infinite;"></div>
        </div>
        <div class="mt-5 font-mono text-lg font-extrabold tracking-[0.2em]">LOCO-7K2</div>
        <div class="mt-1 text-[11px] text-white/50">The Corner Café · 25% off breakfast</div>
      </div>

      {{-- ============ SCREEN 3 · SAVINGS ============ --}}
      <div x-show="i === 3" x-transition:enter="transition duration-500" x-transition:enter-start="opacity-0 translate-x-6" x-transition:enter-end="opacity-100 translate-x-0" class="absolute inset-0 flex flex-col bg-[#eef1f4]">
        <div class="bg-[#0a0a0a] px-4 pb-4 pt-3 text-white">
          <div class="text-[11px] font-semibold text-white/60">Your locolie</div>
          <div class="mt-1 flex items-end gap-1.5"><span class="text-3xl font-extrabold">£42.50</span><span class="mb-1 text-[11px] text-emerald-soft">saved this month</span></div>
        </div>
        <div class="flex-1 px-3 pt-3">
          <div class="mb-2 text-[12px] font-extrabold text-ink">Recently redeemed</div>
          @foreach ([['The Corner Café','25% off breakfast','£4.20'],['Bones Barbers','Free fringe trim','£8.00'],['Newcastle Fitness','Free class taster','£12.00']] as $row)
            <div class="mb-2 flex items-center justify-between rounded-xl bg-white p-2.5 shadow-sm">
              <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-soft text-emerald"><svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                <div><div class="text-[11px] font-bold text-ink">{{ $row[0] }}</div><div class="text-[9px] text-muted">{{ $row[1] }}</div></div>
              </div>
              <span class="text-[11px] font-extrabold text-emerald">{{ $row[2] }}</span>
            </div>
          @endforeach
        </div>
      </div>

      {{-- faux tap cursor: nudges toward the primary action on each screen --}}
      <div class="pointer-events-none absolute z-20 h-7 w-7 rounded-full border-2 border-white/90 bg-white/30 shadow-lg backdrop-blur-sm"
           style="animation: tapcursor 2.8s ease-in-out infinite;"></div>

      <div class="pointer-events-none absolute inset-x-0 bottom-0 flex justify-center pb-3 opacity-0 transition group-hover:opacity-100">
        <span class="rounded-full bg-ink/85 px-3 py-1.5 text-[10px] font-semibold text-white">Open the live app ↗</span>
      </div>
    </div>
  </a>

  {{-- step dots --}}
  <div class="mt-5 flex items-center justify-center gap-2">
    <template x-for="k in [0,1,2,3]" :key="k">
      <button type="button" @click="go(k)" :aria-label="'Step ' + (k+1)"
              :class="i === k ? 'w-6 bg-emerald' : 'w-2 {{ $dark ? 'bg-white/30' : 'bg-ink/15' }}'"
              class="h-2 rounded-full transition-all"></button>
    </template>
  </div>
</div>

@once
@push('head')
<style>
  @keyframes scanline { 0%,100% { top: 8%; } 50% { top: 88%; } }
  @keyframes tapcursor {
    0%, 18%   { opacity: 0; top: 64%; left: 50%; transform: translate(-50%,-50%) scale(1.4); }
    24%       { opacity: .9; }
    30%       { transform: translate(-50%,-50%) scale(.8); }
    36%, 100% { opacity: 0; transform: translate(-50%,-50%) scale(1.4); }
  }
  @media (prefers-reduced-motion: reduce) {
    [style*="scanline"], [style*="tapcursor"] { animation: none !important; }
  }
</style>
@endpush
@endonce
