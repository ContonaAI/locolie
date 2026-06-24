@extends('portal.layout')
@section('title', 'Design')

@section('content')
@include('portal._designnav')
<div class="flex items-center justify-between mb-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">App &amp; Brand Design</h1>
        <p class="text-sm text-slate-500">Logo concepts, brand system, 10 app screens, Google integration spec.</p>
    </div>
    <a href="{{ route('portal.design.raw') }}" target="_blank"
       class="text-sm rounded-lg border border-slate-300 px-3 py-1.5 hover:bg-slate-100">Open full screen ↗</a>
</div>

<div class="rounded-xl border border-slate-200 bg-white overflow-hidden shadow-sm">
    <iframe src="{{ route('portal.design.raw') }}" class="w-full" style="height: 80vh; border: 0;" title="locolie design exploration"></iframe>
</div>
@endsection
