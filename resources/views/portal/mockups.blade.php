@extends('portal.layout')
@section('title', 'App Mockups')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">App Mockups</h1>
        <p class="text-sm text-slate-500">Upload screens, wireframes and design references for the locolie app.</p>
    </div>
</div>

<form method="POST" action="{{ route('portal.mockups.upload') }}" enctype="multipart/form-data"
      class="mb-8 rounded-xl border border-dashed border-slate-300 bg-white p-6">
    @csrf
    <input type="file" name="images[]" accept="image/*" multiple required
           class="block w-full text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-white hover:file:bg-emerald-700">
    @error('images.*')
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
    <button type="submit" class="mt-4 rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700">
        Upload
    </button>
    <p class="mt-2 text-xs text-slate-400">PNG, JPG, GIF or WebP - up to 10MB each.</p>
</form>

@if ($mockups->isEmpty())
    <div class="rounded-xl border border-slate-200 bg-white p-12 text-center text-slate-400">
        No mockups yet. Upload your first design above.
    </div>
@else
    <div class="grid gap-4 grid-cols-2 sm:grid-cols-3 md:grid-cols-4">
        @foreach ($mockups as $mockup)
            <a href="{{ $mockup['url'] }}" target="_blank"
               class="group block rounded-lg overflow-hidden border border-slate-200 bg-white hover:border-emerald-300 hover:shadow transition">
                <img src="{{ $mockup['url'] }}" alt="{{ $mockup['name'] }}" class="w-full h-48 object-cover bg-slate-100">
                <div class="px-2 py-1.5 text-xs text-slate-500 truncate">{{ $mockup['name'] }}</div>
            </a>
        @endforeach
    </div>
@endif
@endsection
