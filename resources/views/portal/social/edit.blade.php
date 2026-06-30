@extends('portal.layout')
@section('title', $post->exists ? 'Edit post' : 'New post')

@php use App\Models\SocialAccount; @endphp

@section('content')
@include('portal.social._nav', ['tab' => 'calendar'])

@php
    $selected = old('platforms', $post->platforms ?? []);
    $mediaText = old('media', collect($post->media ?? [])->implode("\n"));
    $scheduledValue = old('scheduled_at',
        $post->scheduled_at ? $post->scheduled_at->format('Y-m-d\TH:i') : ($date ? $date.'T09:00' : ''));
@endphp

<div class="max-w-3xl">
    <a href="{{ url()->previous() }}" class="text-sm text-slate-500 hover:text-slate-700">&larr; Back to calendar</a>
    <h2 class="text-2xl font-bold text-slate-900 mt-2 mb-6">{{ $post->exists ? 'Edit post' : 'New post' }}</h2>

    @if ($errors->any())
        <div class="mb-5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $post->exists ? route('social.update', $post) : route('social.store') }}"
          x-data="{ body: @js(old('body', $post->body ?? '')) }" class="space-y-6">
        @csrf
        @if ($post->exists) @method('PUT') @endif

        {{-- Platforms --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Platforms</label>
            <div class="flex flex-wrap gap-2">
                @foreach ($platforms as $p)
                    @php $on = in_array($p, $selected, true); $live = $accounts->get($p)?->isConnected(); @endphp
                    <label class="inline-flex items-center gap-2 rounded-xl border px-3.5 py-2 cursor-pointer transition has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 {{ $on ? 'border-emerald-500 bg-emerald-50' : 'border-slate-200 bg-white' }}">
                        <input type="checkbox" name="platforms[]" value="{{ $p }}" @checked($on) class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="w-2.5 h-2.5 rounded-full" style="background: {{ SocialAccount::color($p) }}"></span>
                        <span class="text-sm font-semibold text-slate-700">{{ SocialAccount::label($p) }}</span>
                        @unless ($live)<span class="text-[10px] text-slate-400">(not connected)</span>@endunless
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Copy --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Copy</label>
            <textarea name="body" x-model="body" rows="6" maxlength="5000"
                      class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm"
                      placeholder="Write your post for {{ $llCity }}...">{{ old('body', $post->body) }}</textarea>
            <div class="mt-1 text-xs text-slate-400" x-text="body.length + ' / 5000 characters'"></div>
        </div>

        {{-- Media --}}
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-2">Media <span class="font-normal text-slate-400">(asset paths, one per line)</span></label>
            <textarea name="media" rows="3"
                      class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm font-mono"
                      placeholder="social/launch-poster.jpg">{{ $mediaText }}</textarea>
            <p class="mt-1 text-xs text-slate-400">Paths under storage/app/public, or full https URLs. Instagram needs an image; TikTok needs a video.</p>
        </div>

        {{-- Status + schedule --}}
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Status</label>
                <select name="status" class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected(old('status', $post->status) === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Schedule date & time</label>
                <input type="datetime-local" name="scheduled_at" value="{{ $scheduledValue }}"
                       class="w-full rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 text-sm">
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <button class="rounded-xl bg-emerald-600 text-white text-sm font-semibold px-5 py-2.5 hover:bg-emerald-700">Save post</button>
            <a href="{{ route('social.calendar') }}" class="text-sm text-slate-500 hover:text-slate-700">Cancel</a>
            @if ($post->exists)
                <div class="ml-auto flex items-center gap-2">
                    <form method="POST" action="{{ route('social.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
                        @csrf @method('DELETE')
                        <button class="text-sm font-semibold text-rose-600 hover:underline">Delete</button>
                    </form>
                </div>
            @endif
        </div>
    </form>

    @if ($post->exists)
        {{-- Publish now (manual). Reports "not connected" until the app is live. --}}
        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 p-4 flex items-center justify-between gap-4">
            <div class="text-sm text-slate-600">
                <span class="font-semibold text-slate-800">Publish now</span> - pushes to each connected platform via its API. Until the developer apps are approved this reports a clear "not connected" result.
                @if ($post->error)<div class="mt-1 text-xs text-rose-600">Last result: {{ $post->error }}</div>@endif
                @if ($post->external_id)<div class="mt-1 text-xs text-emerald-600">Published id: {{ $post->external_id }}</div>@endif
            </div>
            <form method="POST" action="{{ route('social.publish', $post) }}">
                @csrf
                <button class="shrink-0 rounded-lg bg-slate-900 text-white text-sm font-semibold px-4 py-2.5 hover:bg-slate-800">Publish now</button>
            </form>
        </div>
    @endif
</div>
@endsection
