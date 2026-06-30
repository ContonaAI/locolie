{{--
    Public scan-to-join landing. This is what a shopper sees after scanning the
    in-store QR poster. No login, no app install: just a branded opt-in form that
    adds them to this shop's email (and optional SMS) list with explicit consent.

    Self-contained page (does NOT extend the business layout) - the shopper is a
    member of the public on their phone, not a logged-in retailer.
--}}
@php
    $accent = $business->brandColor();
    $headline = $program?->active && $program?->headline ? $program->headline : 'Join '.$business->name;
    $blurb = $program?->active && $program?->blurb
        ? $program->blurb
        : 'Get '.$business->name."'s offers and news straight to your phone - and never miss a deal on the high street.";
@endphp
<!DOCTYPE html>
<html lang="en-GB" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Join {{ $business->name }} - locolie</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body{ font-family:'Inter',system-ui,sans-serif; }
        [x-cloak]{ display:none !important; }
    </style>
</head>
<body class="min-h-full antialiased" style="background:
        radial-gradient(120% 80% at 50% -10%, {{ $accent }}22, transparent 60%), #f1f5f9;">

    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col px-5 py-8">

        {{-- locolie wordmark --}}
        <div class="mb-6 flex items-center justify-center gap-1.5 text-sm font-bold text-slate-500">
            <span class="inline-block h-2.5 w-2.5 rounded-full" style="background: {{ $accent }}"></span>
            powered by locolie
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-900/5 sm:p-8">

            {{-- Brand header --}}
            <div class="flex flex-col items-center text-center">
                <div class="flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl border border-slate-200"
                     style="background: {{ $accent }}1a;">
                    @if ($business->logoUrl())
                        <img src="{{ $business->logoUrl() }}" alt="{{ $business->name }}" class="h-full w-full object-contain">
                    @else
                        <span class="text-xl font-extrabold" style="color: {{ $accent }}">{{ $business->brandInitials() }}</span>
                    @endif
                </div>
                <h1 class="mt-4 text-2xl font-extrabold tracking-tight text-slate-900">{{ $headline }}</h1>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $blurb }}</p>
                @if ($program?->active)
                    <span class="mt-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-bold"
                          style="background: {{ $accent }}1a; color: {{ $accent }};">
                        Loyalty rewards included
                    </span>
                @endif
            </div>

            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Opt-in form --}}
            <form method="POST" action="{{ route('marketing.capture.store', $business->slug) }}"
                  x-data="{ sms: false }" class="mt-6 space-y-4">
                @csrf

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">First name (optional)</label>
                    <input type="text" name="name" value="{{ old('name') }}" autocomplete="given-name"
                           placeholder="e.g. Alex"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $accent }};">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">Email address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                           placeholder="you@email.com"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $accent }};">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-500">Mobile (optional, for text alerts)</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel"
                           placeholder="07700 900000"
                           @input="sms = $event.target.value.replace(/\D/g,'').length >= 7"
                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:ring-2"
                           style="--tw-ring-color: {{ $accent }};">
                </div>

                {{-- Consent --}}
                <div class="space-y-3 rounded-2xl bg-slate-50 p-4">
                    <label class="flex cursor-pointer items-start gap-3 text-sm text-slate-700">
                        <input type="checkbox" name="email_opt_in" value="1" checked required
                               class="mt-0.5 h-4 w-4 rounded border-slate-300"
                               style="accent-color: {{ $accent }};">
                        <span>Yes, email me <b>{{ $business->name }}</b>'s offers and news. I can unsubscribe any time.</span>
                    </label>

                    <label class="flex cursor-pointer items-start gap-3 text-sm text-slate-700" x-show="sms" x-cloak>
                        <input type="checkbox" name="sms_opt_in" value="1"
                               class="mt-0.5 h-4 w-4 rounded border-slate-300"
                               style="accent-color: {{ $accent }};">
                        <span>Also text me time-sensitive deals. Reply STOP to opt out. Standard rates may apply.</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full rounded-xl px-5 py-3.5 text-sm font-bold text-white transition hover:brightness-110"
                        style="background: {{ $accent }};">
                    Join the list
                </button>

                <p class="text-center text-[11px] leading-relaxed text-slate-400">
                    Your details go to <b>{{ $business->name }}</b> and are protected by locolie. We never sell your data.
                    See our <a href="{{ route('legal.privacy') }}" class="underline">privacy promise</a>.
                </p>
            </form>
        </div>

        {{-- Small reassurance row --}}
        <div class="mt-6 flex items-center justify-center gap-5 text-[11px] font-semibold text-slate-400">
            <span>GDPR consent recorded</span>
            <span>One-tap unsubscribe</span>
            <span>No spam</span>
        </div>
    </div>
</body>
</html>
