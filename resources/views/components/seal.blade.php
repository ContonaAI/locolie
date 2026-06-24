{{--
    locolie "verified local" seal - a modern trust-mark.
    Usage:  <x-seal class="h-32 w-32" />            (dark, default)
            <x-seal variant="light" class="h-24 w-24" />
            <x-seal variant="mono"  class="h-16 w-16" />
    Sizing is controlled by the wrapper/utility classes; the SVG scales to fit.
--}}
@props(['variant' => 'dark'])
@php
    $id = 'seal'.\Illuminate\Support\Str::random(6);
    $markPin = 'M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Z';

    $p = match ($variant) {
        'light' => [
            'disc' => '#ffffff', 'edge' => '#0a0a0a', 'edgeOp' => '0.08',
            'ring' => '#059669', 'inner' => '#0a0a0a', 'innerOp' => '0.1',
            'top' => '#0a0a0a', 'bot' => '#047857', 'dot' => '#059669',
            'mark' => '#059669', 'word' => '#0a0a0a', 'botOp' => '1',
        ],
        'mono' => [
            'disc' => 'none', 'edge' => 'none', 'edgeOp' => '0',
            'ring' => '#ffffff', 'inner' => '#ffffff', 'innerOp' => '0.35',
            'top' => '#ffffff', 'bot' => '#ffffff', 'dot' => '#ffffff',
            'mark' => '#ffffff', 'word' => '#ffffff', 'botOp' => '0.75',
        ],
        default => [
            'disc' => '#0a0a0a', 'edge' => 'none', 'edgeOp' => '0',
            'ring' => '#059669', 'inner' => '#ffffff', 'innerOp' => '0.12',
            'top' => '#ffffff', 'bot' => '#6ee7b7', 'dot' => '#059669',
            'mark' => '#059669', 'word' => '#ffffff', 'botOp' => '1',
        ],
    };
@endphp
<svg viewBox="0 0 240 240" {{ $attributes->merge(['class' => 'h-full w-full']) }} role="img" aria-label="locolie verified local seal">
    <defs>
        <path id="{{ $id }}-t" d="M30 120 a90 90 0 0 1 180 0" fill="none"/>
        <path id="{{ $id }}-b" d="M34 120 a86 86 0 0 0 172 0" fill="none"/>
    </defs>
    @if ($p['disc'] !== 'none')
        <circle cx="120" cy="120" r="119" fill="{{ $p['disc'] }}"/>
    @endif
    @if ($p['edge'] !== 'none')
        <circle cx="120" cy="120" r="119" fill="none" stroke="{{ $p['edge'] }}" stroke-opacity="{{ $p['edgeOp'] }}" stroke-width="1"/>
    @endif
    <circle cx="120" cy="120" r="110" fill="none" stroke="{{ $p['ring'] }}" stroke-width="1.5"/>
    <circle cx="120" cy="120" r="93" fill="none" stroke="{{ $p['inner'] }}" stroke-opacity="{{ $p['innerOp'] }}" stroke-width="1"/>
    <text font-family="Inter,sans-serif" font-size="13" font-weight="700" letter-spacing="4" fill="{{ $p['top'] }}"><textPath href="#{{ $id }}-t" startOffset="50%" text-anchor="middle">VERIFIED LOCAL</textPath></text>
    <text font-family="Inter,sans-serif" font-size="10.5" font-weight="600" letter-spacing="3.5" fill="{{ $p['bot'] }}" opacity="{{ $p['botOp'] }}"><textPath href="#{{ $id }}-b" startOffset="50%" text-anchor="middle">BACKED BY LOCOLIE</textPath></text>
    <circle cx="30.5" cy="120" r="1.8" fill="{{ $p['dot'] }}"/>
    <circle cx="209.5" cy="120" r="1.8" fill="{{ $p['dot'] }}"/>
    <g transform="translate(120 96) scale(2.0833) translate(-12 -12)">
        <path d="{{ $markPin }}" fill="{{ $p['mark'] }}"/>
        <path d="M8.4 10 11 12.6 15.7 7.2" fill="none" stroke="{{ $variant === 'mono' ? '#0a0a0a' : '#ffffff' }}" stroke-width="2.05" stroke-linecap="round" stroke-linejoin="round"/>
    </g>
    <text x="120" y="150" text-anchor="middle" font-family="Inter,sans-serif" font-size="26" font-weight="800" letter-spacing="-0.6" fill="{{ $p['word'] }}">locolie</text>
</svg>
