@extends('portal.layout')
@section('title', 'Sign in')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Go<span class="text-emerald-600">Local</span> Portal</h1>
            <p class="text-sm text-slate-500 mt-1">Private working space - enter the password to continue.</p>
        </div>
        <form method="POST" action="{{ route('portal.login.submit') }}" class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            @csrf
            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <input type="password" name="password" autofocus required
                   class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-emerald-500 focus:ring-emerald-500 focus:outline-none">
            @error('password')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
            <button type="submit" class="mt-4 w-full rounded-lg bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">
                Enter
            </button>
        </form>
    </div>
</div>
@endsection
