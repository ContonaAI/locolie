{{--
  Reusable vertical bar chart. Pure CSS, no JS, no SVG.
  Params:
    $bars  : associative array label => value
    $color : hex accent (default emerald)
    $height: px height of the tallest bar track (default 96)
--}}
@php
    $bars = $bars ?? [];
    $color = $color ?? '#059669';
    $height = $height ?? 96;
    $max = max(1, ...(array_values($bars) ?: [1]));
@endphp
<div class="flex items-end justify-between gap-1.5" style="height: {{ $height + 22 }}px">
    @foreach ($bars as $label => $value)
        <div class="flex-1 flex flex-col items-center justify-end gap-1.5 group">
            <div class="text-[11px] font-bold text-slate-700 opacity-0 group-hover:opacity-100 transition">{{ $value }}</div>
            <div class="w-full rounded-t-md transition-all" style="height: {{ max(2, round($value / $max * $height)) }}px; background: {{ $color }}; opacity: {{ $value ? 0.85 : 0.18 }}"></div>
            <div class="text-[11px] font-medium text-slate-400">{{ $label }}</div>
        </div>
    @endforeach
</div>
