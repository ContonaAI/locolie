{{--
    Reusable SMS preview - a realistic phone mockup showing the message as the
    customer receives it. Standalone (no layout extend): accepts a single
    $preview array (see SmsChannel::previewData). Safe to @include from the
    studio and the retailer dashboard alike.

    $preview keys: sender, brand_name, brand_color, body, url, segments,
    char_count, stop_line.
--}}
@php
    $accent = $preview['brand_color'] ?? '#7c3aed';
    $sender = $preview['sender'] ?? 'locolie';
    $brandName = $preview['brand_name'] ?? 'locolie';
    $body = $preview['body'] ?? '';
    $url = $preview['url'] ?? null;
    $segments = $preview['segments'] ?? 1;
    $chars = $preview['char_count'] ?? 0;
    $stop = $preview['stop_line'] ?? 'Reply STOP to opt out';
    $now = now()->format('g:i');
@endphp

<div class="mx-auto w-full" style="max-width: 22rem;">
    {{-- Phone frame --}}
    <div class="relative mx-auto rounded-[2.75rem] bg-slate-900 p-2.5 shadow-2xl ring-1 ring-black/10"
         style="width: 20rem;">
        {{-- Screen --}}
        <div class="relative overflow-hidden rounded-[2.25rem] bg-gradient-to-b from-slate-50 to-slate-100">

            {{-- Dynamic island / notch --}}
            <div class="pointer-events-none absolute left-1/2 top-2 z-20 h-6 w-24 -translate-x-1/2 rounded-full bg-slate-900"></div>

            {{-- Status bar --}}
            <div class="relative z-10 flex items-center justify-between px-6 pt-3 pb-1 text-[11px] font-semibold text-slate-900">
                <span>{{ $now }}</span>
                <span class="flex items-center gap-1.5">
                    {{-- signal --}}
                    <svg class="h-3 w-3.5" viewBox="0 0 24 24" fill="currentColor"><rect x="1" y="14" width="3" height="6" rx="1"/><rect x="7" y="10" width="3" height="10" rx="1"/><rect x="13" y="6" width="3" height="14" rx="1"/><rect x="19" y="2" width="3" height="18" rx="1"/></svg>
                    {{-- wifi --}}
                    <svg class="h-3 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 18.5a1.6 1.6 0 100 3.2 1.6 1.6 0 000-3.2zM5.6 12.3a9 9 0 0112.8 0l-1.7 1.7a6.6 6.6 0 00-9.4 0zM2 8.7a14 14 0 0120 0l-1.7 1.7a11.6 11.6 0 00-16.6 0z"/></svg>
                    {{-- battery --}}
                    <span class="inline-flex items-center">
                        <span class="relative inline-block h-3 w-6 rounded-[3px] border border-slate-900/70">
                            <span class="absolute inset-y-0.5 left-0.5 w-4 rounded-[1px] bg-slate-900"></span>
                        </span>
                        <span class="ml-0.5 inline-block h-1.5 w-0.5 rounded-r bg-slate-900/70"></span>
                    </span>
                </span>
            </div>

            {{-- Messages app header --}}
            <div class="relative z-10 flex flex-col items-center border-b border-slate-200/80 bg-white/70 px-4 pb-3 pt-2 backdrop-blur">
                <div class="flex h-11 w-11 items-center justify-center rounded-full text-sm font-extrabold text-white shadow-sm"
                     style="background: {{ $accent }};">
                    {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $sender) ?: 'L', 0, 2)) }}
                </div>
                <div class="mt-1 flex items-center gap-1 text-[13px] font-semibold text-slate-900">
                    {{ $sender }}
                    <svg class="h-3 w-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                </div>
                <div class="text-[10px] text-slate-400">SMS / Text Message</div>
            </div>

            {{-- Conversation --}}
            <div class="relative z-10 min-h-[15rem] space-y-2 px-4 py-4">
                <div class="text-center text-[10px] font-medium text-slate-400">Text Message - Today {{ $now }}</div>

                {{-- Incoming bubble --}}
                <div class="flex justify-start">
                    <div class="max-w-[80%]">
                        <div class="rounded-[1.25rem] rounded-bl-md bg-slate-200 px-3.5 py-2.5 text-[13px] leading-snug text-slate-900 shadow-sm">
                            @if ($body !== '')
                                <span class="whitespace-pre-line break-words">{{ $body }}</span>
                            @else
                                <span class="italic text-slate-400">Your message preview appears here as the customer sees it.</span>
                            @endif
                            @if ($url)
                                <a href="#" class="mt-1 block break-all font-medium underline" style="color: {{ $accent }};">{{ $url }}</a>
                            @endif
                            <div class="mt-1.5 text-[10px] text-slate-500">{{ $stop }}</div>
                        </div>
                        <div class="mt-1 pl-1 text-[10px] text-slate-400">{{ $brandName }} - delivered</div>
                    </div>
                </div>
            </div>

            {{-- Home indicator --}}
            <div class="flex justify-center pb-2 pt-1">
                <span class="h-1 w-28 rounded-full bg-slate-300"></span>
            </div>
        </div>
    </div>

    {{-- Char / segment meta --}}
    <div class="mt-3 flex items-center justify-center gap-3 text-[11px] text-slate-400">
        <span><span class="font-semibold text-slate-600">{{ $chars }}</span> chars</span>
        <span class="text-slate-300">|</span>
        <span><span class="font-semibold text-slate-600">{{ $segments }}</span> {{ \Illuminate\Support\Str::plural('segment', $segments) }}</span>
    </div>
</div>
