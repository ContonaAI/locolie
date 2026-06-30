@extends('site.layout')

@section('title', 'Contact us')
@section('meta_description', 'Get in touch with locolie. Marketing, sales and general enquiries - we are here to help you back your high street in '.$llPlace.'.')

@section('content')
@php
    $inbox = \App\Http\Controllers\ContactController::INBOX;

    // Three contact streams - all routing to the single shared inbox for now.
    $streams = [
        [
            'key' => 'marketing',
            'title' => 'Marketing',
            'blurb' => 'Press, partnerships, content and community. Want to feature an indie or work together? Say hello.',
            'icon' => '<path d="M3 11l18-5v12L3 14v-3Z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>',
        ],
        [
            'key' => 'sales',
            'title' => 'Sales',
            'blurb' => 'Running a local business? Talk to us about listings, priority placement and getting set up.',
            'icon' => '<path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.8L7 14"/>',
        ],
        [
            'key' => 'general',
            'title' => 'General enquiries',
            'blurb' => 'Questions about how locolie works, your account, or anything else. We read every message.',
            'icon' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v10Z"/>',
        ],
    ];
@endphp

{{-- ===================== HERO ===================== --}}
<section class="relative overflow-hidden bg-white">
    <div class="mesh" aria-hidden="true"><i class="b1"></i><i class="b2"></i><i class="b3"></i></div>
    <div class="hero-grid absolute inset-0" aria-hidden="true"></div>
    <div class="relative mx-auto max-w-4xl px-5 pb-10 pt-28 text-center sm:px-6 sm:pt-36">
        <span class="reveal inline-flex items-center gap-2 rounded-full border border-emerald/20 bg-emerald-soft px-3 py-1.5 text-xs font-semibold text-emerald">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>
            We are based in {{ $llCity }}
        </span>
        <h1 class="reveal text-balance mt-5 text-4xl font-extrabold tracking-tight text-ink sm:text-5xl" data-d="1">
            Let's <span class="gradient-text animate-gradient">talk</span>
        </h1>
        <p class="reveal text-balance mx-auto mt-4 max-w-xl text-base leading-relaxed text-muted sm:text-lg" data-d="2">
            Whether you run an indie, want to partner with us, or just have a question, we would love to hear from you. Every message lands with a real person.
        </p>
    </div>
</section>

{{-- ===================== CONTACT STREAMS ===================== --}}
<section class="bg-white pb-6">
    <div class="mx-auto max-w-6xl px-5 sm:px-6">
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ($streams as $i => $s)
                <div class="reveal card-hover rounded-2xl border border-hair bg-white p-6" data-d="{{ $i + 1 }}">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-soft text-emerald">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $s['icon'] !!}</svg>
                    </span>
                    <h2 class="mt-4 text-lg font-bold text-ink">{{ $s['title'] }}</h2>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $s['blurb'] }}</p>
                    <a href="mailto:{{ $inbox }}?subject={{ rawurlencode($s['title'].' enquiry') }}"
                       class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-emerald transition hover:gap-2.5">
                        {{ $inbox }}
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ===================== FORM ===================== --}}
<section class="bg-white py-14 sm:py-20">
    <div class="mx-auto max-w-2xl px-5 sm:px-6">
        <div class="reveal glass-card rounded-3xl p-6 sm:p-9">
            <h2 class="text-2xl font-extrabold tracking-tight text-ink">Send us a message</h2>
            <p class="mt-2 text-sm text-muted">Fill this in and we will get back to you by email. It all goes to <a href="mailto:{{ $inbox }}" class="font-semibold text-emerald">{{ $inbox }}</a>.</p>

            @if (session('contact_sent'))
                <div class="mt-6 flex items-start gap-3 rounded-2xl border border-emerald/20 bg-emerald-soft p-4">
                    <svg class="mt-0.5 h-5 w-5 shrink-0 text-emerald" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <div>
                        <p class="text-sm font-bold text-emerald">Thanks, your message is on its way.</p>
                        <p class="mt-0.5 text-sm text-emerald/90">We will reply to the email you gave us as soon as we can.</p>
                    </div>
                </div>
            @endif

            @if (session('contact_error'))
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
                    {{ session('contact_error') }}
                </div>
            @endif

            <form action="{{ route('site.contact.submit') }}" method="POST" class="mt-7 space-y-5">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="c-name" class="mb-1.5 block text-sm font-semibold text-ink">Your name</label>
                        <input id="c-name" name="name" type="text" required value="{{ old('name') }}"
                               class="w-full rounded-xl border border-hair bg-white px-4 py-3 text-sm text-ink outline-none transition focus:border-emerald focus:ring-2 focus:ring-emerald/20"
                               placeholder="Jane Smith">
                        @error('name')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="c-email" class="mb-1.5 block text-sm font-semibold text-ink">Email</label>
                        <input id="c-email" name="email" type="email" required value="{{ old('email') }}"
                               class="w-full rounded-xl border border-hair bg-white px-4 py-3 text-sm text-ink outline-none transition focus:border-emerald focus:ring-2 focus:ring-emerald/20"
                               placeholder="you@example.com">
                        @error('email')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div>
                    <label for="c-topic" class="mb-1.5 block text-sm font-semibold text-ink">What's it about?</label>
                    <select id="c-topic" name="topic"
                            class="w-full rounded-xl border border-hair bg-white px-4 py-3 text-sm text-ink outline-none transition focus:border-emerald focus:ring-2 focus:ring-emerald/20">
                        <option value="general" @selected(old('topic') === 'general')>General enquiry</option>
                        <option value="marketing" @selected(old('topic') === 'marketing')>Marketing</option>
                        <option value="sales" @selected(old('topic') === 'sales')>Sales</option>
                    </select>
                </div>
                <div>
                    <label for="c-message" class="mb-1.5 block text-sm font-semibold text-ink">Message</label>
                    <textarea id="c-message" name="message" rows="5" required
                              class="w-full rounded-xl border border-hair bg-white px-4 py-3 text-sm text-ink outline-none transition focus:border-emerald focus:ring-2 focus:ring-emerald/20"
                              placeholder="How can we help?">{{ old('message') }}</textarea>
                    @error('message')<p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit"
                        class="group inline-flex w-full items-center justify-center gap-2 rounded-full bg-ink px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-emerald sm:w-auto">
                    Send message
                    <svg class="h-3.5 w-3.5 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>
        </div>
    </div>
</section>
@endsection
