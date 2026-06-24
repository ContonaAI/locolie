@extends('site.layout')

@section('title', 'Unsubscribed')

@section('content')
<div class="bg-white">
    <div class="mx-auto max-w-xl px-5 pb-24 pt-28 text-center sm:px-6 sm:pt-32">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-soft text-emerald">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>

        <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-ink">You're unsubscribed</h1>

        @if ($topicLabel)
            <p class="mt-3 text-muted"><strong class="text-ink">{{ $email }}</strong> will no longer receive <strong class="text-ink">{{ strtolower($topicLabel) }}</strong>.</p>
        @else
            <p class="mt-3 text-muted"><strong class="text-ink">{{ $email }}</strong> has been removed from all locolie marketing emails and texts.</p>
        @endif

        <p class="mt-2 text-sm text-muted">Changed your mind, or only want to stop some messages? You can fine-tune exactly what you hear about.</p>

        <div class="mt-8 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ $manageUrl }}" class="rounded-full bg-ink px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald">Manage preferences</a>
            <a href="/" class="rounded-full border border-hair px-6 py-3 text-sm font-semibold text-ink transition hover:bg-black/[0.04]">Back to locolie</a>
        </div>
    </div>
</div>
@endsection
