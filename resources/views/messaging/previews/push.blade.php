{{--
    Reusable push notification preview. Accepts a single $preview array
    (see App\Services\Messaging\PushChannel::previewData). Renders three
    believable mockups - iOS lock-screen, Android heads-up, and a desktop
    browser toast - each rendered in the sending brand's colour + logo.

    Standalone (no layout) so it can be dropped into the studio AND the
    retailer dashboard. Tailwind classes only.

    For LIVE preview the studio swaps these text nodes via Alpine x-text and the
    icon via x-bind, using element ids/refs that mirror the keys below.
--}}
@php
    $p = $preview;
    $title = $p['title'] ?? 'A deal near you';
    $body = $p['body'] ?? '';
    $brandName = $p['brand_name'] ?? 'locolie';
    $brandColor = $p['brand_color'] ?? '#059669';
    $initials = $p['brand_initials'] ?? 'GL';
    $logo = $p['logo_url'] ?? null;
    $appName = $p['app_name'] ?? 'locolie';
    $cta = $p['cta_label'] ?? '';
    $time = $p['time'] ?? 'now';
@endphp

<div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">

    {{-- ── (a) iOS lock-screen banner ──────────────────────────────────── --}}
    <figure class="flex flex-col">
        <figcaption class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-slate-400">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M17.05 11.97c-.02-2.07 1.69-3.06 1.77-3.11-0.96-1.41-2.46-1.6-2.99-1.62-1.27-.13-2.48.75-3.13.75-.64 0-1.64-.73-2.7-.71-1.39.02-2.67.81-3.38 2.05-1.44 2.5-.37 6.2 1.04 8.23.69.99 1.51 2.1 2.59 2.06 1.04-.04 1.43-.67 2.69-.67 1.25 0 1.61.67 2.71.65 1.12-.02 1.83-1.01 2.51-2.01.79-1.15 1.12-2.27 1.13-2.32-.02-.01-2.17-.83-2.19-3.29zM15.0 5.36c.57-.7.96-1.66.85-2.62-.83.03-1.83.55-2.42 1.24-.53.61-1 1.6-.87 2.54.92.07 1.87-.47 2.44-1.16z"/></svg>
            iOS lock screen
        </figcaption>
        <div class="relative flex-1 rounded-[2rem] bg-gradient-to-b from-slate-800 to-slate-900 p-3 shadow-lg ring-1 ring-black/20">
            {{-- status row --}}
            <div class="mb-6 mt-1 flex items-center justify-between px-2 text-[11px] font-semibold text-white/80">
                <span>9:41</span>
                <span class="inline-flex items-center gap-1">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M2 17h2v-3H2v3zm4 0h2v-7H6v7zm4 0h2V7h-2v10zm4 0h2V4h-2v13zm4 0h2v-9h-2v9z"/></svg>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M3 9a9 9 0 0118 0M6 12.5a5 5 0 0112 0M12 16a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"/></svg>
                </span>
            </div>
            {{-- clock --}}
            <div class="mb-1 text-center text-5xl font-light tracking-tight text-white">9:41</div>
            <div class="mb-5 text-center text-sm text-white/70">Tuesday, 24 June</div>
            {{-- notification card --}}
            <div class="rounded-2xl bg-white/15 p-3 backdrop-blur-md ring-1 ring-white/10">
                <div class="flex items-center gap-2.5">
                    <span class="gl-push-swatch flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-[10px] text-xs font-extrabold text-white shadow"
                          style="background: {{ $brandColor }}">
                        @if ($logo)
                            <img data-push="icon" src="{{ $logo }}" alt="{{ $brandName }}" class="h-full w-full object-cover">
                        @else
                            <span data-push="initials">{{ $initials }}</span>
                        @endif
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate text-xs font-semibold uppercase tracking-wide text-white/90" data-push="app">{{ $appName }}</span>
                            <span class="shrink-0 text-[11px] text-white/60" data-push="time">{{ $time }}</span>
                        </div>
                    </div>
                </div>
                <div class="mt-2 text-sm font-semibold leading-tight text-white" data-push="title">{{ $title }}</div>
                <div class="mt-0.5 text-[13px] leading-snug text-white/85" data-push="body">{{ $body }}</div>
            </div>
        </div>
    </figure>

    {{-- ── (b) Android heads-up notification ───────────────────────────── --}}
    <figure class="flex flex-col">
        <figcaption class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-slate-400">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M6 18c0 .55.45 1 1 1h1v3.5a1.5 1.5 0 003 0V19h2v3.5a1.5 1.5 0 003 0V19h1c.55 0 1-.45 1-1V8H6v10zM3.5 8A1.5 1.5 0 002 9.5v7a1.5 1.5 0 003 0v-7A1.5 1.5 0 003.5 8zm17 0a1.5 1.5 0 00-1.5 1.5v7a1.5 1.5 0 003 0v-7A1.5 1.5 0 0020.5 8zM15.53 2.16l1.3-1.3a.5.5 0 00-.7-.7l-1.48 1.47A6 6 0 0012 1c-.96 0-1.86.22-2.66.61L7.87.15a.5.5 0 10-.71.7l1.31 1.31A5.98 5.98 0 006 7h12c0-2-.98-3.77-2.47-4.84zM10 5H9V4h1v1zm5 0h-1V4h1v1z"/></svg>
            Android heads-up
        </figcaption>
        <div class="flex-1 rounded-[1.75rem] bg-gradient-to-br from-indigo-950 to-slate-900 p-3 shadow-lg ring-1 ring-black/20">
            <div class="mb-3 flex items-center justify-between px-1.5 text-[11px] font-medium text-white/70">
                <span>9:41</span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 3C7 3 2.7 5.1 0 8.3L12 23 24 8.3C21.3 5.1 17 3 12 3z"/></svg>
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M15.67 4H14V2h-4v2H8.33C7.6 4 7 4.6 7 5.33v15.33C7 21.4 7.6 22 8.33 22h7.33c.74 0 1.34-.6 1.34-1.33V5.33C17 4.6 16.4 4 15.67 4z"/></svg>
                </span>
            </div>
            {{-- Material card --}}
            <div class="rounded-2xl bg-white p-3.5 shadow-md">
                <div class="flex items-center gap-2 text-xs text-slate-500">
                    <span class="gl-push-swatch flex h-5 w-5 shrink-0 items-center justify-center overflow-hidden rounded-full text-[9px] font-extrabold text-white"
                          style="background: {{ $brandColor }}">
                        @if ($logo)
                            <img data-push="icon" src="{{ $logo }}" alt="{{ $brandName }}" class="h-full w-full object-cover">
                        @else
                            <span data-push="initials">{{ $initials }}</span>
                        @endif
                    </span>
                    <span class="truncate font-semibold text-slate-600" data-push="app">{{ $appName }}</span>
                    <span class="text-slate-300">&middot;</span>
                    <span class="shrink-0" data-push="time">{{ $time }}</span>
                    <svg class="ml-auto w-4 h-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </div>
                <div class="mt-2 text-[15px] font-semibold leading-tight text-slate-900" data-push="title">{{ $title }}</div>
                <div class="mt-0.5 text-[13px] leading-snug text-slate-600" data-push="body">{{ $body }}</div>
                @if ($cta)
                    <div class="mt-3 border-t border-slate-100 pt-2">
                        <span class="text-[13px] font-bold uppercase tracking-wide" style="color: {{ $brandColor }}" data-push="cta">{{ $cta }}</span>
                    </div>
                @endif
            </div>
        </div>
    </figure>

    {{-- ── (c) Desktop / web browser toast ─────────────────────────────── --}}
    <figure class="flex flex-col">
        <figcaption class="mb-2 flex items-center gap-1.5 text-xs font-semibold text-slate-400">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 010 18M12 3a14 14 0 000 18"/></svg>
            Web / desktop
        </figcaption>
        <div class="flex flex-1 items-center justify-center rounded-[1.5rem] bg-slate-100 p-4 ring-1 ring-slate-200">
            {{-- macOS / Chrome style toast --}}
            <div class="w-full max-w-sm rounded-xl bg-white p-3.5 shadow-[0_10px_40px_-12px_rgba(0,0,0,0.35)] ring-1 ring-slate-900/5">
                <div class="flex items-start gap-3">
                    <span class="gl-push-swatch flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl text-sm font-extrabold text-white shadow-sm"
                          style="background: {{ $brandColor }}">
                        @if ($logo)
                            <img data-push="icon" src="{{ $logo }}" alt="{{ $brandName }}" class="h-full w-full object-cover">
                        @else
                            <span data-push="initials">{{ $initials }}</span>
                        @endif
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="text-[15px] font-semibold leading-tight text-slate-900" data-push="title">{{ $title }}</div>
                        <div class="mt-0.5 text-[13px] leading-snug text-slate-600" data-push="body">{{ $body }}</div>
                        <div class="mt-1.5 flex items-center gap-1.5 text-[11px] text-slate-400">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/></svg>
                            <span data-push="app">{{ $appName }}</span>
                            <span class="text-slate-300">&middot;</span>
                            <span>via Chrome</span>
                        </div>
                    </div>
                    <svg class="w-4 h-4 shrink-0 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </div>
                @if ($cta)
                    <div class="mt-3 flex gap-2">
                        <span class="rounded-lg px-3 py-1.5 text-xs font-bold text-white" style="background: {{ $brandColor }}" data-push="cta">{{ $cta }}</span>
                    </div>
                @endif
            </div>
        </div>
    </figure>

</div>
