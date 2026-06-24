@extends('business.layout')
@section('title', 'Business login')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center">
  <div class="w-full max-w-md">
    <div class="text-center mb-7">
      <h1 class="text-3xl font-extrabold tracking-tight text-slate-900">Business login</h1>
      <p class="text-slate-500 mt-2">Manage your listing, offers and plan.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
      @if ($errors->any())
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
      @endif
      <form method="POST" action="{{ route('business.login.submit') }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Email</label>
          <input name="email" type="email" value="{{ old('email') }}" required autofocus
            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1">Password</label>
          <input name="password" type="password" required
            class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none">
        </div>
        <button class="w-full rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 transition">Log in</button>
      </form>
    </div>

    <p class="text-center text-sm text-slate-500 mt-6">
      New here? <a href="{{ route('site.for-business') }}" class="font-semibold text-emerald-700 hover:underline">List your business →</a>
    </p>
  </div>
</div>
@endsection
