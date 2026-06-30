@extends('portal.layout')
@section('title', 'Content')

@section('content')
<div class="mb-7 flex flex-wrap items-end justify-between gap-4">
  <div>
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Content</h1>
    <p class="text-slate-500 mt-2 max-w-2xl">Edit the words and images used across the site. Changes save live - no redeploy. Each block is addressed by a key (e.g. <code class="font-mono text-xs bg-slate-100 px-1 rounded">home.hero.title</code>) that pages read via the <code class="font-mono text-xs bg-slate-100 px-1 rounded">cms()</code> helper.</p>
  </div>
  <a href="{{ url('/cms-demo') }}" target="_blank" rel="noopener"
     class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-sm font-semibold text-slate-700 hover:border-emerald-300 hover:bg-emerald-50/40">
    Preview demo page
    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5h5v5M19 5l-9 9M5 7v12h12"/></svg>
  </a>
</div>

@if (session('status'))
  <div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('status') }}</div>
@endif

@if ($total === 0)
  <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-800">
    No content blocks yet. Run <code class="font-mono bg-amber-100 px-1.5 py-0.5 rounded">php artisan db:seed --class=ContentBlockSeeder</code> to load the starter set.
  </div>
@endif

{{-- Quick jump nav across the content sections --}}
@if ($groups->count() > 1)
  <nav class="mb-6 flex flex-wrap gap-2">
    @foreach ($groups as $group => $data)
      <a href="#group-{{ $group }}" class="rounded-full bg-white border border-slate-200 px-3.5 py-1.5 text-sm font-semibold text-slate-600 hover:border-emerald-300 hover:text-emerald-700">{{ $data['label'] }}</a>
    @endforeach
  </nav>
@endif

@foreach ($groups as $group => $data)
  <section id="group-{{ $group }}" class="mb-8 scroll-mt-24">
    <h2 class="text-lg font-bold text-slate-900 mb-3">{{ $data['label'] }}</h2>
    <div class="space-y-3">
      @foreach ($data['blocks'] as $block)
        <div class="rounded-2xl border border-slate-200 bg-white p-5" x-data="{ open: false }">
          <form method="POST" action="{{ route('portal.content.update', $block) }}">
            @csrf
            @method('PUT')

            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
              <div class="min-w-0">
                <div class="text-sm font-semibold text-slate-900">{{ $block->label ?: $block->key }}</div>
                <div class="flex items-center gap-2 mt-0.5">
                  <code class="font-mono text-[11px] text-slate-400">{{ $block->key }}</code>
                  <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-slate-100 text-slate-500">{{ $block->type }}</span>
                </div>
                @if ($block->help)
                  <p class="text-xs text-slate-400 mt-1">{{ $block->help }}</p>
                @endif
              </div>
            </div>

            @php $isLong = in_array($block->type, ['richtext', 'html'], true); @endphp

            @if ($block->type === 'image')
              {{-- Image: URL field + live preview --}}
              <div x-data="{ url: @js($block->value) }">
                <input type="text" name="value" x-model="url"
                       class="w-full rounded-lg border border-slate-300 px-3 py-2.5 font-mono text-xs focus:ring-2 focus:ring-emerald-500 outline-none"
                       placeholder="/storage/... or https://...">
                <template x-if="url">
                  <img :src="url" alt="" class="mt-3 max-h-40 rounded-lg border border-slate-100 object-cover">
                </template>
              </div>
            @elseif ($isLong)
              <textarea name="value" rows="3"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm leading-relaxed focus:ring-2 focus:ring-emerald-500 outline-none">{{ $block->value }}</textarea>
            @else
              {{-- text + url --}}
              <input type="text" name="value" value="{{ $block->value }}"
                     class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm focus:ring-2 focus:ring-emerald-500 outline-none {{ $block->type === 'url' ? 'font-mono text-xs' : '' }}">
            @endif

            <div class="flex items-center justify-between gap-3 mt-3">
              <div class="text-xs text-slate-400">
                @if ($block->updated_at)
                  Updated {{ $block->updated_at->diffForHumans() }}@if ($block->updated_by) by {{ $block->updated_by }}@endif
                @endif
              </div>
              <button class="rounded-lg bg-emerald-600 text-white text-sm font-bold px-4 py-2 hover:bg-emerald-700">Save</button>
            </div>
          </form>
        </div>
      @endforeach
    </div>
  </section>
@endforeach
@endsection
