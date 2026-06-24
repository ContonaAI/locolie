{{--
  Reusable area+line trend chart. Pure inline SVG, no JS.
  Params:
    $series : array of ['label'=>string,'value'=>int]   (oldest first)
    $color  : hex accent (default emerald)
    $id     : unique string id (for the gradient)
    $height : px height of the plot (default 70)
--}}
@php
    $series = $series ?? [];
    $color = $color ?? '#059669';
    $id = $id ?? 'trend';
    $height = $height ?? 70;
    $w = 100; $h = 40; // viewBox units
    $vals = array_map(fn ($p) => (float) $p['value'], $series);
    $max = max(1, ...($vals ?: [1]));
    $n = max(1, count($vals));
    $step = $n > 1 ? $w / ($n - 1) : 0;
    $pts = [];
    foreach ($vals as $i => $v) {
        $x = round($i * $step, 2);
        $y = round($h - ($v / $max) * ($h - 4) - 2, 2);
        $pts[] = "$x,$y";
    }
    $line = implode(' ', $pts);
    $area = $n > 1 ? "0,$h ".$line." ".round(($n - 1) * $step, 2).",$h" : "0,$h $line $w,$h";
@endphp
<div class="w-full" style="height: {{ $height }}px">
    <svg viewBox="0 0 {{ $w }} {{ $h }}" preserveAspectRatio="none" class="w-full h-full overflow-visible">
        <defs>
            <linearGradient id="grad-{{ $id }}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="{{ $color }}" stop-opacity="0.28"/>
                <stop offset="100%" stop-color="{{ $color }}" stop-opacity="0"/>
            </linearGradient>
        </defs>
        <polygon points="{{ $area }}" fill="url(#grad-{{ $id }})"/>
        <polyline points="{{ $line }}" fill="none" stroke="{{ $color }}" stroke-width="1.4" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
        @if ($n)
            @php $lx = $n > 1 ? round(($n - 1) * $step, 2) : 0; $ly = round($h - (end($vals) / $max) * ($h - 4) - 2, 2); @endphp
            <circle cx="{{ $lx }}" cy="{{ $ly }}" r="1.8" fill="{{ $color }}" vector-effect="non-scaling-stroke"/>
        @endif
    </svg>
</div>
