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

{{-- ── Google Search Console ───────────────────────────────────────────── --}}
<section class="rounded-2xl border border-slate-200 bg-white p-6 mb-6" x-data="{ copied: '' }">
  <div class="flex items-center justify-between gap-4 mb-1">
    <div>
      <h2 class="text-lg font-bold text-slate-900">Google Search Console</h2>
      <p class="text-sm text-slate-500 mt-1">Verify ownership of locolie.com so it can be indexed and tracked in Google. Add a property in Search Console, verify with one of the methods below, then submit the sitemap.</p>
    </div>
    <span class="px-2.5 py-1 rounded-full text-xs font-bold shrink-0 {{ $gsc['verified'] ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
      {{ $gsc['verified'] ? 'Verification set' : 'Not verified' }}
    </span>
  </div>

  {{-- Quick links to everything you need in Search Console --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-2.5 my-5">
    @php
      $links = [
        ['Open Search Console', 'https://search.google.com/search-console', 'Your dashboard'],
        ['Add a property', 'https://search.google.com/search-console/welcome', 'Start verifying locolie.com'],
        ['Submit sitemap', 'https://search.google.com/search-console/sitemaps', 'Paste the sitemap URL below'],
        ['Inspect a URL', 'https://search.google.com/search-console/inspect', 'Check indexing of any page'],
        ['Performance', 'https://search.google.com/search-console/performance/search-analytics', 'Clicks, impressions, queries'],
        ['Verification help', 'https://support.google.com/webmasters/answer/9008080', 'Google ownership guide'],
      ];
    @endphp
    @foreach ($links as [$label, $href, $sub])
      <a href="{{ $href }}" target="_blank" rel="noopener"
         class="group rounded-xl border border-slate-200 px-4 py-3 hover:border-emerald-300 hover:bg-emerald-50/40 transition">
        <div class="flex items-center gap-1.5 text-sm font-semibold text-slate-800 group-hover:text-emerald-700">
          {{ $label }}
          <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5M19 5l-9 9M5 7v12h12"/></svg>
        </div>
        <div class="text-xs text-slate-400 mt-0.5">{{ $sub }}</div>
      </a>
    @endforeach
  </div>

  {{-- Property + sitemap URLs to paste into Search Console --}}
  <dl class="text-sm divide-y divide-slate-100 border-y border-slate-100 mb-6">
    @foreach ([['Property URL', $gsc['home_url']], ['Sitemap (submit this)', $gsc['sitemap_url']], ['Robots', $gsc['robots_url']]] as [$dt, $dd])
      <div class="flex items-center justify-between gap-4 py-2.5">
        <dt class="text-slate-500 shrink-0">{{ $dt }}</dt>
        <dd class="flex items-center gap-2 min-w-0">
          <a href="{{ $dd }}" target="_blank" rel="noopener" class="font-mono text-xs text-slate-700 truncate hover:text-emerald-700">{{ $dd }}</a>
          <button type="button" class="rounded-md border border-slate-200 text-[11px] font-semibold px-2 py-1 hover:bg-slate-50 shrink-0"
                  @click="navigator.clipboard.writeText('{{ $dd }}'); copied='{{ $dt }}'; setTimeout(() => copied='', 1500)">
            <span x-show="copied !== '{{ $dt }}'">Copy</span><span x-show="copied === '{{ $dt }}'" class="text-emerald-600">Copied</span>
          </button>
        </dd>
      </div>
    @endforeach
  </dl>

  {{-- The two verification methods --}}
  <form method="POST" action="{{ route('admin.search-console') }}" class="space-y-5">
    @csrf
    <div>
      <label class="block text-sm font-semibold text-slate-800 mb-1">Method 1 - HTML meta tag <span class="font-normal text-slate-400">(recommended)</span></label>
      <p class="text-xs text-slate-500 mb-2">In Search Console pick "HTML tag", copy the tag (or just its content token) and paste it here. We add it to the site's home page automatically. One per line for multiple owners.</p>
      <textarea name="verification_tags" rows="3" placeholder='<meta name="google-site-verification" content="abc123..." />'
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none">{{ implode("\n", $gsc['tags']) }}</textarea>
      @if ($gsc['tags'])
        <p class="text-xs text-emerald-700 mt-1.5">Live on the homepage now: {{ count($gsc['tags']) }} verification tag(s) rendered in &lt;head&gt;.</p>
      @endif
    </div>

    <div>
      <label class="block text-sm font-semibold text-slate-800 mb-1">Method 2 - HTML file</label>
      <p class="text-xs text-slate-500 mb-2">If you pick "HTML file" in Search Console, paste the file name (e.g. <code class="font-mono bg-slate-100 px-1 rounded">google1a2b3c.html</code>). We serve it at the site root for you - no upload needed. One per line.</p>
      <textarea name="html_files" rows="2" placeholder="google1a2b3c4d5e6f.html"
                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none">{{ implode("\n", $gsc['files']) }}</textarea>
      @foreach ($gsc['files'] as $f)
        <p class="text-xs text-emerald-700 mt-1.5">Serving <a href="{{ url('/'.$f) }}" target="_blank" rel="noopener" class="font-mono underline">/{{ $f }}</a></p>
      @endforeach
    </div>

    <button class="rounded-lg bg-emerald-600 text-white text-sm font-bold px-5 py-2.5 hover:bg-emerald-700">Save verification</button>
    <p class="text-xs text-slate-400">After saving, go to Search Console and click <span class="font-semibold text-slate-600">Verify</span>. Then submit the sitemap URL above.</p>
  </form>
</section>

{{-- ── Security note: the portal gate is open ──────────────────────────── --}}
<section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
  <h2 class="text-sm font-bold text-amber-900">Heads up: this portal is currently public</h2>
  <p class="text-sm text-amber-800 mt-1">The shared-password gate is disabled, so anyone can reach these admin pages. The sync token is masked here for that reason and lives only in your env files. Re-enable the password gate before relying on this in production.</p>
</section>
@endsection
