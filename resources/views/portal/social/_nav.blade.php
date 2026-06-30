{{-- Social control centre sub-navigation. $tab = calendar|accounts --}}
@php
    $tabs = [
        ['key' => 'calendar', 'route' => 'social.calendar', 'label' => 'Calendar', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['key' => 'accounts', 'route' => 'social.accounts', 'label' => 'Handles & accounts', 'icon' => 'M12 11c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v3h16v-3c0-2.66-5.33-4-8-4z'],
    ];
@endphp
<div class="mb-8">
    <div class="flex items-center gap-2.5 mb-1.5">
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-emerald-600 text-white shadow-sm">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">Social control centre</h1>
    </div>
    <p class="text-slate-500 max-w-2xl">Plan, draft and schedule {{ $llCity }} posts across Facebook, Instagram, TikTok and LinkedIn from one calendar. Direct publishing goes live once each platform app is approved.</p>

    <nav class="mt-6 flex items-center gap-1 overflow-x-auto border-b border-slate-200 -mb-px">
        @foreach ($tabs as $t)
            <a href="{{ route($t['route']) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold whitespace-nowrap border-b-2 transition
                      {{ ($tab ?? 'calendar') === $t['key'] ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-slate-500 hover:text-slate-800 hover:border-slate-300' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/></svg>
                {{ $t['label'] }}
            </a>
        @endforeach
        <a href="{{ route('social.create') }}"
           class="ml-auto mb-1 inline-flex items-center gap-1.5 rounded-lg bg-slate-900 text-white text-sm font-semibold px-3.5 py-2 hover:bg-slate-800">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New post
        </a>
    </nav>
</div>
