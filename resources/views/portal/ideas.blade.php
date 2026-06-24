@extends('portal.layout')
@section('title', 'Ideas')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Ideas</h1>
    <p class="text-sm text-slate-500">A shared scratchpad - drop any idea for the app, brand, or go-to-market here.</p>
</div>

<form method="POST" action="{{ route('portal.ideas.store') }}"
      class="mb-8 rounded-xl border border-slate-200 bg-white p-4 sm:p-6 shadow-sm space-y-3">
    @csrf
    <div class="grid gap-3 sm:grid-cols-3">
        <input type="text" name="title" required maxlength="160" placeholder="Idea title *"
               class="sm:col-span-2 w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
        <input type="text" name="author" maxlength="80" placeholder="Your name (optional)"
               class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
    </div>
    <textarea name="body" rows="3" maxlength="5000" placeholder="More detail (optional)"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none"></textarea>
    @error('title')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
    <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
        Add idea
    </button>
</form>

@if ($ideas->isEmpty())
    <div class="rounded-xl border border-slate-200 bg-white p-12 text-center text-slate-400">
        No ideas yet. Add the first one above.
    </div>
@else
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ($ideas as $idea)
            <div class="rounded-xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="font-semibold text-slate-900">{{ $idea->title }}</h2>
                    <form method="POST" action="{{ route('portal.ideas.delete', $idea) }}"
                          onsubmit="return confirm('Delete this idea?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-slate-300 hover:text-red-500 text-sm" title="Delete">✕</button>
                    </form>
                </div>
                @if ($idea->body)
                    <p class="mt-2 text-sm text-slate-600 whitespace-pre-line">{{ $idea->body }}</p>
                @endif
                <p class="mt-3 text-xs text-slate-400">
                    {{ $idea->author ?: 'Anonymous' }} · {{ $idea->created_at->diffForHumans() }}
                </p>
            </div>
        @endforeach
    </div>
@endif
@endsection
