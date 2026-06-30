@extends('portal.layout')
@section('title', 'Social calendar')

@php use App\Models\SocialAccount; use App\Models\SocialPost; @endphp

@section('content')
@include('portal.social._nav', ['tab' => 'calendar'])

{{-- ── Platform legend + connection state ──────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-3 mb-5">
    @foreach ($platforms as $p)
        @php $acct = $accounts->get($p); $live = $acct?->isConnected(); @endphp
        <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-sm">
            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ SocialAccount::color($p) }}"></span>
            <span class="font-semibold text-slate-700">{{ SocialAccount::label($p) }}</span>
            <span class="text-xs {{ $live ? 'text-emerald-600' : 'text-slate-400' }}">{{ $live ? 'connected' : ($acct?->handle ? $acct->handle : 'not connected') }}</span>
        </span>
    @endforeach
    <a href="{{ route('social.accounts') }}" class="text-sm font-semibold text-emerald-700 hover:underline ml-auto">Manage handles &rarr;</a>
</div>

{{-- ── Month header + nav ──────────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-4">
    <h2 class="text-xl font-bold text-slate-900">{{ $month->format('F Y') }}</h2>
    <div class="flex items-center gap-1.5">
        <a href="{{ route('social.calendar', ['month' => $prevMonth]) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-600 hover:bg-slate-50">&larr; Prev</a>
        <a href="{{ route('social.calendar') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-600 hover:bg-slate-50">Today</a>
        <a href="{{ route('social.calendar', ['month' => $nextMonth]) }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-sm text-slate-600 hover:bg-slate-50">Next &rarr;</a>
    </div>
</div>

{{-- ── Month grid ──────────────────────────────────────────────────────── --}}
<div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
    <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200 text-xs font-semibold uppercase tracking-wide text-slate-400">
        @foreach (['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $dow)
            <div class="px-2 py-2 text-center">{{ $dow }}</div>
        @endforeach
    </div>
    @foreach ($weeks as $week)
        <div class="grid grid-cols-7 border-b border-slate-100 last:border-b-0">
            @foreach ($week as $day)
                <div class="min-h-[7rem] border-r border-slate-100 last:border-r-0 p-1.5 align-top {{ $day['inMonth'] ? '' : 'bg-slate-50/60' }}">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-semibold {{ $day['isToday'] ? 'inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-600 text-white' : ($day['inMonth'] ? 'text-slate-500' : 'text-slate-300') }}">{{ $day['date']->day }}</span>
                        <a href="{{ route('social.create', ['date' => $day['date']->toDateString()]) }}" class="text-slate-300 hover:text-emerald-600" title="New post">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        </a>
                    </div>
                    <div class="space-y-1">
                        @foreach ($day['posts'] as $post)
                            <a href="{{ route('social.edit', $post) }}"
                               class="block rounded-md px-1.5 py-1 text-xs {{ SocialPost::statusStyle($post->status) }} hover:brightness-95">
                                <div class="flex items-center gap-1 mb-0.5">
                                    @foreach ($post->platforms ?? [] as $p)
                                        <span class="w-2 h-2 rounded-full" style="background: {{ SocialAccount::color($p) }}" title="{{ SocialAccount::label($p) }}"></span>
                                    @endforeach
                                    <span class="ml-auto font-semibold opacity-70">{{ optional($post->scheduled_at ?? $post->posted_at)->format('H:i') }}</span>
                                </div>
                                <div class="truncate font-medium">{{ \Illuminate\Support\Str::limit($post->body, 42) }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach
</div>

{{-- ── Status legend ───────────────────────────────────────────────────── --}}
<div class="flex flex-wrap items-center gap-2 mt-4 text-xs">
    @foreach (\App\Models\SocialPost::STATUSES as $s)
        <span class="inline-flex items-center rounded-md px-2 py-0.5 {{ SocialPost::statusStyle($s) }}">{{ ucfirst($s) }}</span>
    @endforeach
</div>

{{-- ── Ideas backlog (no date yet) ─────────────────────────────────────── --}}
@if ($ideas->isNotEmpty())
    <div class="mt-10">
        <h2 class="text-lg font-bold text-slate-900 mb-3">Ideas backlog</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach ($ideas as $idea)
                <a href="{{ route('social.edit', $idea) }}" class="block rounded-xl border border-slate-200 bg-white p-4 hover:border-emerald-300">
                    <div class="flex items-center gap-1 mb-2">
                        @foreach ($idea->platforms ?? [] as $p)
                            <span class="w-2.5 h-2.5 rounded-full" style="background: {{ SocialAccount::color($p) }}"></span>
                        @endforeach
                        <span class="ml-auto text-xs rounded-md px-2 py-0.5 {{ SocialPost::statusStyle('idea') }}">Idea</span>
                    </div>
                    <p class="text-sm text-slate-600">{{ \Illuminate\Support\Str::limit($idea->body, 120) }}</p>
                </a>
            @endforeach
        </div>
    </div>
@endif
@endsection
