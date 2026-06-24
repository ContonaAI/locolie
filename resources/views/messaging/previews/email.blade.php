{{--
    Reusable inbox-style email preview. Standalone (no layout) so it can be
    @included from the portal studio AND the retailer business dashboard.
    Expects a single $preview array (the shape of EmailChannel::previewData()).
    Renders an email-client chrome wrapping a faithful rendering of the email.
    Uses Tailwind (loaded by both host contexts).
--}}
@php
    $brandColor = $preview['brand_color'] ?? '#059669';
    $brandName = $preview['brand_name'] ?? 'locolie';
    $fromName = $preview['from_name'] ?? $brandName;
    $fromEmail = $preview['from_email'] ?? 'hello@locolie.com';
    $subject = $preview['subject'] ?? '(no subject)';
    $preheader = $preview['preheader'] ?? '';
    $bodyHtml = $preview['body_html'] ?? '';
    $ctaLabel = $preview['cta_label'] ?? '';
    $ctaUrl = $preview['cta_url'] ?? '';
    $logoUrl = $preview['logo_url'] ?? null;
    $initials = $preview['brand_initials'] ?? 'LO';
    $footer = $preview['footer'] ?? '';
    $snippet = $preheader ?: \Illuminate\Support\Str::limit(trim(strip_tags($bodyHtml)), 80);
@endphp

<div class="mx-auto w-full max-w-md">
    {{-- Email-client window chrome --}}
    <div class="rounded-2xl overflow-hidden border border-slate-200 bg-white shadow-xl shadow-slate-900/5">
        {{-- Title bar --}}
        <div class="flex items-center gap-1.5 px-4 py-2.5 bg-slate-100 border-b border-slate-200">
            <span class="w-3 h-3 rounded-full bg-rose-400"></span>
            <span class="w-3 h-3 rounded-full bg-amber-400"></span>
            <span class="w-3 h-3 rounded-full bg-emerald-400"></span>
            <span class="ml-3 text-xs font-medium text-slate-400">Inbox</span>
        </div>

        {{-- Inbox row: sender avatar + from + subject + snippet --}}
        <div class="flex items-start gap-3 px-4 py-3.5 border-b border-slate-100">
            <div class="w-10 h-10 rounded-full shrink-0 flex items-center justify-center overflow-hidden text-white font-extrabold text-sm"
                 style="background-color: {{ $brandColor }};">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="w-full h-full object-cover">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div class="min-w-0 flex-1">
                <div class="flex items-center justify-between gap-2">
                    <span class="font-semibold text-slate-900 text-sm truncate">{{ $fromName }}</span>
                    <span class="text-xs text-slate-400 shrink-0">now</span>
                </div>
                <div class="text-sm font-medium text-slate-800 truncate">{{ $subject }}</div>
                <div class="text-xs text-slate-400 truncate">{{ $snippet ?: 'No preview text yet.' }}</div>
            </div>
        </div>

        {{-- Rendered email body (scaled, faithful to the real send) --}}
        <div class="bg-slate-50 p-4 max-h-[480px] overflow-y-auto">
            <div class="rounded-xl overflow-hidden bg-white border border-slate-200">
                {{-- Brand band --}}
                <div class="flex items-center gap-3 px-5 py-4" style="background-color: {{ $brandColor }};">
                    <div class="w-9 h-9 rounded-lg shrink-0 flex items-center justify-center overflow-hidden text-white font-extrabold text-xs"
                         style="background-color: rgba(255,255,255,0.18);">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="w-full h-full object-contain bg-white">
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <span class="text-white font-bold text-sm">{{ $brandName }}</span>
                </div>

                {{-- Body --}}
                <div class="px-5 pt-5 pb-1">
                    <h1 class="text-lg font-extrabold tracking-tight text-slate-900 leading-snug mb-3">{{ $subject }}</h1>
                    <div class="text-sm leading-relaxed text-slate-600 prose-sm">
                        @if (trim(strip_tags($bodyHtml)) !== '')
                            {!! $bodyHtml !!}
                        @else
                            <p class="text-slate-300 italic">Your message body will appear here as you type.</p>
                        @endif
                    </div>
                </div>

                {{-- CTA --}}
                @if ($ctaLabel)
                    <div class="px-5 py-4">
                        <span class="inline-block rounded-lg px-5 py-2.5 text-sm font-bold text-white"
                              style="background-color: {{ $brandColor }};">{{ $ctaLabel }}</span>
                    </div>
                @endif

                {{-- Footer --}}
                <div class="px-5 py-4 border-t border-slate-100">
                    <p class="text-xs text-slate-400 leading-relaxed">{{ $footer }}</p>
                    <p class="text-[11px] text-slate-300 mt-1.5">
                        Sent by {{ $brandName }} on <span class="text-emerald-600 font-semibold">locolie</span>.
                        <span class="underline">Unsubscribe</span>.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <p class="text-center text-xs text-slate-400 mt-3">
        From {{ $fromName }} &lt;{{ $fromEmail }}&gt;
    </p>
</div>
