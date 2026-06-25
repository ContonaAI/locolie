@extends('portal.layout')
@section('title', 'Setup')

@php
    // Status -> pill styling + label. emerald=Live, amber=Demo, slate=Not configured,
    // sky=Needs attention (operational, e.g. a worker must be running).
    $pill = function (string $status) {
        return match ($status) {
            'live' => ['bg-emerald-100 text-emerald-700', 'Live'],
            'attention' => ['bg-sky-100 text-sky-700', 'Action needed'],
            'demo' => ['bg-amber-100 text-amber-700', 'Demo mode'],
            default => ['bg-slate-100 text-slate-600', 'Not configured'],
        };
    };
@endphp

@section('content')
{{-- ── Header ──────────────────────────────────────────────────────────── --}}
<div class="mb-7">
  <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Go-live / Setup</h1>
  <p class="text-slate-500 mt-2 max-w-3xl">
    Everything works in demo mode right now: messages are logged and counted so the full flow is demoable.
    Add the credentials below to go live - no code change needed. This page only reads config; it never shows secret values.
  </p>
</div>

{{-- ── Launch checklist summary ────────────────────────────────────────── --}}
<section class="rounded-2xl border border-slate-200 bg-white p-6 mb-6">
  <div class="flex items-center justify-between gap-4 mb-4">
    <div>
      <h2 class="text-lg font-bold text-slate-900">Launch checklist</h2>
      <p class="text-sm text-slate-500 mt-1">{{ $live_count }} of {{ $total_count }} integrations are live.</p>
    </div>
    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $live_count === $total_count ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
      {{ $live_count === $total_count ? 'All live' : ($total_count - $live_count).' outstanding' }}
    </span>
  </div>

  <div class="grid sm:grid-cols-2 gap-x-8 gap-y-1.5">
    @foreach ($items as $it)
      @php [$cls, $label] = $pill($it['status']); @endphp
      <div class="flex items-center justify-between gap-3 py-1.5 border-b border-slate-50 last:border-0">
        <span class="text-sm text-slate-600">{{ $it['name'] }}</span>
        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold shrink-0 {{ $cls }}">{{ $label }}</span>
      </div>
    @endforeach
  </div>
</section>

{{-- ── Integration groups ──────────────────────────────────────────────── --}}
@foreach ($groups as $groupKey => $group)
  <section class="mb-8">
    <div class="flex items-center gap-3 mb-3">
      <h2 class="text-lg font-bold text-slate-900">{{ $group['label'] }}</h2>
      @if ($group['channel_live'])
        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">
          Channel live{{ $group['channel_provider'] ? ' · '.$group['channel_provider'] : '' }}
        </span>
      @endif
    </div>
    <p class="text-sm text-slate-500 mb-4 max-w-3xl">{{ $group['blurb'] }}</p>

    <div class="grid lg:grid-cols-2 gap-4">
      @foreach ($group['items'] as $it)
        @php [$cls, $label] = $pill($it['status']); @endphp
        <div class="rounded-2xl border border-slate-200 bg-white p-6" x-data="{ open: false, copied: false }">
          <div class="flex items-start justify-between gap-3 mb-2">
            <h3 class="font-bold text-slate-900">{{ $it['name'] }}</h3>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold shrink-0 {{ $cls }}">{{ $label }}</span>
          </div>
          <p class="text-sm text-slate-500">{{ $it['what'] }}</p>

          @if (!empty($it['note']))
            <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-3">{{ $it['note'] }}</p>
          @endif

          <button type="button" @click="open = !open"
            class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald-700 hover:text-emerald-800">
            <span x-text="open ? 'Hide setup' : 'How to enable'"></span>
            <svg class="w-3.5 h-3.5 transition-transform" :class="open && 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
          </button>

          <div x-show="open" x-transition x-cloak class="mt-4">
            {{-- Env vars --}}
            @php
              $envBlock = collect($it['vars'])->map(fn ($desc, $name) => $name.'=    # '.$desc)->implode("\n");
            @endphp
            <div class="flex items-center justify-between gap-2 mb-1.5">
              <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Environment variables</span>
              <button type="button"
                class="text-xs font-semibold text-slate-500 hover:text-slate-700"
                @click="navigator.clipboard.writeText(@js($envBlock)); copied = true; setTimeout(() => copied = false, 1500)">
                <span x-show="!copied">Copy</span>
                <span x-show="copied" class="text-emerald-600">Copied</span>
              </button>
            </div>
            <pre class="rounded-xl bg-slate-900 text-emerald-300 mono text-xs px-4 py-3 overflow-x-auto leading-relaxed"><code>@foreach ($it['vars'] as $name => $desc)<span class="text-slate-100">{{ $name }}</span><span class="text-slate-500">=    # {{ $desc }}</span>
@endforeach</code></pre>

            {{-- Steps --}}
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400 mt-4 mb-1.5">Steps</div>
            <ol class="text-sm text-slate-600 space-y-1.5 list-decimal list-inside">
              @foreach ($it['steps'] as $step)
                <li>{{ $step }}</li>
              @endforeach
            </ol>

            @if (!empty($it['doc']))
              <a href="{{ $it['doc'] }}" target="_blank" rel="noopener"
                 class="inline-flex items-center gap-1 text-sm font-semibold text-emerald-700 hover:text-emerald-800 mt-3">
                Provider docs
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17 17 7M9 7h8v8"/></svg>
              </a>
            @endif
          </div>
        </div>
      @endforeach
    </div>
  </section>
@endforeach

{{-- ── Live channel overview (from MessagingService) ───────────────────── --}}
<section class="rounded-2xl border border-slate-200 bg-white p-6 mb-6">
  <h2 class="text-lg font-bold text-slate-900 mb-1">Messaging channels at a glance</h2>
  <p class="text-sm text-slate-500 mb-4">Live connection state straight from the messaging service.</p>
  <div class="grid sm:grid-cols-3 gap-3">
    @foreach ($overview as $key => $ch)
      <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3">
        <div class="flex items-center justify-between gap-2">
          <span class="font-semibold text-slate-800">{{ $ch['label'] }}</span>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $ch['connected'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
            {{ $ch['connected'] ? 'Live' : 'Demo' }}
          </span>
        </div>
        <div class="text-xs text-slate-500 mt-1">{{ $ch['provider'] ? 'via '.$ch['provider'] : 'logged + counted only' }}</div>
      </div>
    @endforeach
  </div>
</section>

{{-- ── Reference ───────────────────────────────────────────────────────── --}}
<section class="rounded-2xl border border-slate-200 bg-white p-6">
  <h2 class="text-lg font-bold text-slate-900 mb-1">Where the details live</h2>
  <p class="text-sm text-slate-500">
    The full runbook is in <code class="mono text-xs bg-slate-100 px-1 py-0.5 rounded">SETUP.md</code> at the repo root, and a
    commented list of every env var is in <code class="mono text-xs bg-slate-100 px-1 py-0.5 rounded">.env.example.messaging</code>.
    After editing the env, run <code class="mono text-xs bg-slate-100 px-1 py-0.5 rounded">php artisan config:clear</code> to pick up the changes.
  </p>
</section>
@endsection
