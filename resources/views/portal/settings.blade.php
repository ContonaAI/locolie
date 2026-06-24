@extends('portal.layout')
@section('title', 'Settings')

@section('content')
<div class="mb-7">
  <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Settings</h1>
  <p class="text-slate-500 mt-2 max-w-2xl">Configuration for this environment, including the data sync used to push your local catalogue up to the live site.</p>
</div>

@if (session('status'))
  <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('status') }}</div>
@endif

{{-- ── Data sync ───────────────────────────────────────────────────────── --}}
<section class="rounded-2xl border border-slate-200 bg-white p-6 mb-6">
  <div class="flex items-center justify-between gap-4 mb-5">
    <div>
      <h2 class="text-lg font-bold text-slate-900">Data sync</h2>
      <p class="text-sm text-slate-500 mt-1">One-way push of categories, businesses, offers and photos from your local machine to this site.</p>
    </div>
    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $sync['configured'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
      {{ $sync['configured'] ? 'Configured' : 'No token set' }}
    </span>
  </div>

  {{-- This environment's data footprint --}}
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
    @foreach (['businesses' => 'Businesses', 'offers' => 'Offers', 'categories' => 'Categories', 'images' => 'Photos'] as $k => $label)
      <div class="rounded-xl border border-slate-100 bg-slate-50/60 px-4 py-3">
        <div class="text-2xl font-extrabold text-slate-900">{{ number_format($sync['counts'][$k]) }}</div>
        <div class="text-xs font-medium text-slate-500 mt-0.5">{{ $label }}</div>
      </div>
    @endforeach
  </div>

  <dl class="text-sm divide-y divide-slate-100">
    <div class="flex items-center justify-between gap-4 py-2.5">
      <dt class="text-slate-500">Last sync received</dt>
      <dd class="font-medium text-slate-900">{{ $sync['last_sync'] ? \Illuminate\Support\Carbon::parse($sync['last_sync'])->diffForHumans() : 'Never' }}</dd>
    </div>
    <div class="flex items-center justify-between gap-4 py-2.5">
      <dt class="text-slate-500">Endpoint</dt>
      <dd class="font-mono text-xs text-slate-700">{{ $sync['endpoint'] }}</dd>
    </div>
    <div class="flex items-center justify-between gap-4 py-2.5">
      <dt class="text-slate-500">Sync token</dt>
      <dd class="font-mono text-xs text-slate-700">{{ $sync['token_masked'] ?? '- not set -' }}</dd>
    </div>
  </dl>
</section>

{{-- ── How to run it ───────────────────────────────────────────────────── --}}
<section class="rounded-2xl border border-slate-200 bg-white p-6 mb-6" x-data="{ copied: false }">
  <h2 class="text-lg font-bold text-slate-900 mb-1">Push your local data up</h2>
  <p class="text-sm text-slate-500 mb-4">From the project folder on your machine, run:</p>

  <div class="flex items-center gap-2">
    <code class="flex-1 rounded-xl bg-slate-900 text-emerald-300 font-mono text-sm px-4 py-3 overflow-x-auto">php artisan sync:push</code>
    <button type="button"
      class="rounded-lg border border-slate-200 text-xs font-semibold px-3 py-2.5 bg-white hover:bg-slate-50 shrink-0"
      @click="navigator.clipboard.writeText('php artisan sync:push'); copied = true; setTimeout(() => copied = false, 1500)">
      <span x-show="!copied">Copy</span>
      <span x-show="copied" class="text-emerald-600">Copied</span>
    </button>
  </div>

  <ul class="text-sm text-slate-500 mt-4 space-y-1.5 list-disc list-inside">
    <li>Adds and updates records - it never deletes anything already live.</li>
    <li>Safe to run as often as you like; matching is by natural key.</li>
    <li>Add <code class="font-mono text-xs bg-slate-100 px-1 py-0.5 rounded">--skip-images</code> to push data only (faster).</li>
  </ul>
</section>

{{-- ── Security note: the portal gate is open ──────────────────────────── --}}
<section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
  <h2 class="text-sm font-bold text-amber-900">Heads up: this portal is currently public</h2>
  <p class="text-sm text-amber-800 mt-1">The shared-password gate is disabled, so anyone can reach these admin pages. The sync token is masked here for that reason and lives only in your env files. Re-enable the password gate before relying on this in production.</p>
</section>
@endsection
