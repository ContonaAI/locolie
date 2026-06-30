{{--
    Confirmation shown after a shopper opts in via the scan-to-join flow.
    Self-contained (the shopper is not logged in).
--}}
@php
    $accent = $business->brandColor();
    $name = $captured['name'] ?? null;
    $sms = $captured['sms'] ?? false;
@endphp
<!DOCTYPE html>
<html lang="en-GB" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>You're in - {{ $business->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style> body{ font-family:'Inter',system-ui,sans-serif; } </style>
</head>
<body class="min-h-full antialiased" style="background:
        radial-gradient(120% 80% at 50% -10%, {{ $accent }}22, transparent 60%), #f1f5f9;">

    <div class="mx-auto flex min-h-screen w-full max-w-md flex-col items-center justify-center px-5 py-10 text-center">

        <div class="flex h-20 w-20 items-center justify-center rounded-full" style="background: {{ $accent }}1a;">
            <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="{{ $accent }}" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 6 9 17l-5-5"/>
            </svg>
        </div>

        <h1 class="mt-6 text-3xl font-extrabold tracking-tight text-slate-900">
            {{ $name ? "You're in, $name!" : "You're in!" }}
        </h1>
        <p class="mt-3 max-w-sm text-sm leading-relaxed text-slate-500">
            You'll be the first to hear about {{ $business->name }}'s offers
            @if ($sms) by email and text. @else by email. @endif
            @if ($program?->active) You're also earning loyalty rewards from now on. @endif
        </p>

        <div class="mt-8 w-full rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">What happens next</p>
            <ul class="mt-3 space-y-2.5 text-sm text-slate-600">
                <li class="flex items-start gap-2.5">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background: {{ $accent }};"></span>
                    {{ $business->name }} sends you their next offer - landing in your inbox@if($sms) or texts@endif.
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background: {{ $accent }};"></span>
                    Every message has a one-tap unsubscribe. You're always in control.
                </li>
                <li class="flex items-start gap-2.5">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background: {{ $accent }};"></span>
                    Discover more independents near you in the locolie app.
                </li>
            </ul>
        </div>

        <a href="{{ route('site.home') }}"
           class="mt-6 inline-flex items-center gap-1.5 text-sm font-bold" style="color: {{ $accent }};">
            Explore locolie
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>

        <p class="mt-8 text-[11px] font-semibold text-slate-400">powered by locolie</p>
    </div>
</body>
</html>
