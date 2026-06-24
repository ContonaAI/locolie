@extends('site.layout')

@section('title', 'Email & SMS preferences')

@section('content')
<div class="bg-white">
    <div class="mx-auto max-w-2xl px-5 pb-24 pt-28 sm:px-6 sm:pt-32">
        <header class="border-b border-hair pb-6">
            <h1 class="text-3xl font-extrabold tracking-tight text-ink sm:text-4xl">Your preferences</h1>
            <p class="mt-3 text-muted">Choose what you hear from locolie about. You're in control - change this any time, and we'll always honour it.</p>
        </header>

        @if (session('saved'))
            <div class="mt-6 flex items-center gap-2 rounded-2xl border border-emerald/30 bg-emerald-soft px-4 py-3 text-sm font-semibold text-emerald">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                Your preferences have been saved.
            </div>
        @endif

        @if (! $signed || ! $email)
            {{-- No valid signed link: ask for the email and we'll send a secure manage link. --}}
            <div class="mt-8 rounded-2xl border border-hair bg-[#f9f9f9] p-6">
                <h2 class="text-lg font-bold text-ink">Manage your subscription</h2>
                <p class="mt-2 text-sm text-muted">To keep your details safe, we send a secure link to your email rather than showing preferences to anyone who knows your address. Use the link in any locolie email to manage everything instantly, or pop in your email below.</p>
                <p class="mt-4 text-sm text-muted">For now, please use the <strong class="text-ink">"Manage preferences"</strong> or <strong class="text-ink">"Unsubscribe"</strong> link at the bottom of any locolie email - it opens this page already signed in for your address.</p>
            </div>
        @else
            <form method="POST" action="{{ url()->full() }}" class="mt-8">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">

                <p class="mb-4 text-sm text-muted">Showing preferences for <strong class="text-ink">{{ $email }}</strong></p>

                <div class="space-y-3">
                    @foreach ($topics as $key => $topic)
                        <label class="flex cursor-pointer items-start gap-4 rounded-2xl border border-hair p-4 transition hover:border-emerald/40 hover:bg-emerald-soft/30">
                            <div class="relative mt-0.5 inline-flex shrink-0"
                                 x-data="{ on: {{ ($statusMap[$key] ?? false) ? 'true' : 'false' }} }">
                                <input type="hidden" name="topics[{{ $key }}]" value="0">
                                <input type="checkbox" name="topics[{{ $key }}]" value="1" class="peer sr-only" x-model="on">
                                <span class="h-6 w-11 rounded-full bg-hair transition peer-checked:bg-emerald"></span>
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition" :class="on && 'translate-x-5'"></span>
                            </div>
                            <div>
                                <div class="font-bold text-ink">{{ $topic['label'] }}</div>
                                <div class="text-sm text-muted">{{ $topic['description'] }}</div>
                                <div class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-muted/70">{{ $topic['channel'] === 'sms' ? 'By text' : 'By email' }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="submit" class="rounded-full bg-ink px-6 py-3 text-sm font-bold text-white transition hover:bg-emerald">Save preferences</button>
                    <a href="{{ \App\Models\Subscription::unsubscribeUrl($email) }}"
                       class="text-sm font-semibold text-muted underline-offset-2 hover:text-ink hover:underline">Unsubscribe from everything</a>
                </div>
            </form>
        @endif

        <p class="mt-10 text-xs text-muted">See how we look after your data in our <a href="{{ route('legal.privacy') }}" class="font-semibold text-emerald">Privacy Policy</a>.</p>
    </div>
</div>
@endsection
