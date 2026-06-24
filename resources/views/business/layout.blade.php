<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Business') - locolie</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    body{ font-family:'Inter',system-ui,sans-serif; }
    [x-cloak]{ display:none !important; }
    .brand-pin{ display:inline-block; width:.62em; height:.62em; border-radius:50% 50% 50% 0; background:#059669; transform:rotate(-45deg); margin:0 .04em; }
  </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased">
  <header class="bg-white border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
      <a href="{{ route('site.home') }}" class="text-xl font-extrabold lowercase tracking-tight text-slate-900">l<span class="brand-pin"></span>c<span class="brand-pin"></span>lie <span class="text-slate-400 font-semibold text-sm normal-case">· Business</span></a>
      @auth('business')
        <div class="flex items-center gap-4">
          <span class="hidden sm:block text-sm text-slate-500">{{ auth('business')->user()->name }}</span>
          <form method="POST" action="{{ route('business.logout') }}">@csrf
            <button class="text-sm font-semibold text-slate-600 hover:text-slate-900">Log out</button>
          </form>
        </div>
      @endauth
    </div>
  </header>

  @if (session('status'))
    <div class="max-w-6xl mx-auto px-4 sm:px-6 mt-4">
      <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('status') }}</div>
    </div>
  @endif

  <main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    @yield('content')
  </main>
</body>
</html>
