@php
    $tabs = [
        ['label' => 'Logos & Names', 'href' => route('portal.brand'),          'on' => request()->routeIs('portal.brand')],
        ['label' => 'Style Directions', 'href' => route('portal.brand').'#styles', 'on' => false],
        ['label' => 'App Screens',    'href' => route('portal.design'),         'on' => request()->routeIs('portal.design')],
    ];
@endphp
<div class="mb-6 flex items-center gap-2 overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
    <span class="mono text-[11px] uppercase tracking-widest text-slate-400 shrink-0 pr-1">Design</span>
    @foreach ($tabs as $t)
        <a href="{{ $t['href'] }}"
           class="shrink-0 px-3.5 py-1.5 rounded-full text-sm font-medium transition {{ $t['on'] ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 border border-slate-200 hover:border-slate-300' }}">
            {{ $t['label'] }}
        </a>
    @endforeach
</div>
