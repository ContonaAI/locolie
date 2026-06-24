@extends('customer.layout')

@section('title', 'Your locolie')

@section('content')
<div class="pt-12 pb-8 text-center animate-pop">
  <div class="inline-flex items-center justify-center h-16 w-16 rounded-2xl bg-emerald-soft mb-6">
    <svg class="h-8 w-8 text-emerald" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>
  </div>
  <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Your <span class="gradient-text">locolie</span></h1>
  <p class="mt-3 text-base text-slate-600 leading-relaxed">See how much you've saved shopping local with locolie.</p>
</div>

@if (session('notfound'))
  <div class="mb-5 rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3.5 text-sm text-amber-800 flex items-start gap-2.5">
    <svg class="h-5 w-5 shrink-0 mt-px text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>{{ session('notfound') }}</span>
  </div>
@endif

<div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-6">
  <form method="POST" action="{{ route('customer.report.lookup') }}" class="space-y-4">
    @csrf
    <div>
      <label for="email" class="block text-sm font-semibold text-slate-700 mb-1.5">Your email</label>
      <input
        id="email"
        type="email"
        name="email"
        value="{{ old('email') }}"
        inputmode="email"
        autocomplete="email"
        autofocus
        placeholder="you@example.com"
        class="w-full rounded-xl border @error('email') border-rose-300 ring-1 ring-rose-200 @else border-slate-300 @enderror bg-slate-50 px-4 py-3 text-base text-slate-900 placeholder-slate-400 focus:bg-white focus:border-emerald focus:ring-2 focus:ring-emerald/30 focus:outline-none transition">
      @error('email')
        <p class="mt-1.5 text-sm font-medium text-rose-600">{{ $message }}</p>
      @enderror
      <p class="mt-2 text-xs text-slate-500">Use the same email you gave when redeeming an offer.</p>
    </div>
    <button type="submit" class="w-full rounded-xl bg-emerald px-5 py-3.5 text-base font-bold text-white transition hover:bg-emerald-700 active:scale-[.99]">
      Show my locolie
    </button>
  </form>
</div>

<p class="mt-6 text-center text-sm text-slate-500">
  No account needed. <a href="{{ route('app') }}" class="font-semibold text-emerald hover:underline">Find local offers</a>
</p>
@endsection
