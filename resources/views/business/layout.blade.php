<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Business') - locolie</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <style>
    body{ font-family:'Inter',system-ui,sans-serif; }
    [x-cloak]{ display:none !important; }
    .brand-pin{ display:inline-block; width:.62em; height:.62em; border-radius:50% 50% 50% 0; background:#059669; transform:rotate(-45deg); margin:0 .04em; }

    /* Animated map-pin "o" in the Locolie wordmark */
    .gl-logo .gl-pin{
      transform-origin:50% 92%;
      animation:gl-pin-bob 3.2s ease-in-out infinite;
      will-change:transform;
      transition:filter .25s ease;
    }
    .gl-logo:hover .gl-pin{ animation:gl-pin-hop .6s ease-in-out; filter:drop-shadow(0 3px 5px rgba(5,150,105,.55)); }
    @keyframes gl-pin-bob{
      0%,100%{ transform:translateY(0) rotate(0deg); }
      45%    { transform:translateY(-0.16em) rotate(-4deg); }
      70%    { transform:translateY(-0.03em) rotate(2deg); }
    }
    @keyframes gl-pin-hop{
      0%  { transform:translateY(0) scale(1); }
      35% { transform:translateY(-0.32em) scale(1.06); }
      65% { transform:translateY(0.02em) scale(.97); }
      100%{ transform:translateY(0) scale(1); }
    }
    @media (prefers-reduced-motion:reduce){ .gl-logo .gl-pin,.gl-logo:hover .gl-pin{ animation:none; } }
  </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased">
  <header class="bg-white border-b border-slate-200">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
      <a href="{{ route('business.dashboard') }}" class="gl-logo text-xl font-extrabold tracking-tight text-slate-900 shrink-0">L<svg class="gl-pin" style="height:0.9em;width:auto;display:inline-block;vertical-align:-0.13em;margin:0 -0.012em;filter:drop-shadow(0 1px 3px rgba(5,150,105,.4))" viewBox="0 0 24 24" fill="#059669" aria-hidden="true"><path fill-rule="evenodd" clip-rule="evenodd" d="M12 1.6C7.3 1.6 3.5 5.4 3.5 10.1c0 5.6 8.5 12.3 8.5 12.3s8.5-6.7 8.5-12.3C20.5 5.4 16.7 1.6 12 1.6Zm0 5.9a2.7 2.7 0 1 0 0 5.4 2.7 2.7 0 0 0 0-5.4Z"/></svg>colie <span class="text-slate-400 font-semibold text-sm normal-case">· Business</span></a>
      @auth('business')
        @php
          $bnav = [
            ['route' => 'business.dashboard', 'label' => 'Dashboard'],
            ['route' => 'business.loyalty', 'label' => 'Loyalty'],
            ['route' => 'business.messaging', 'label' => 'Messaging'],
            ['route' => 'business.reports', 'label' => 'Reports'],
          ];
        @endphp
        <nav class="hidden sm:flex items-center gap-1 text-sm flex-1 ml-4">
          @foreach ($bnav as $item)
            <a href="{{ route($item['route']) }}"
               class="px-3 py-1.5 rounded-lg transition {{ request()->routeIs($item['route']) ? 'font-semibold text-emerald-700 bg-emerald-50' : 'text-slate-600 hover:bg-slate-100' }}">{{ $item['label'] }}</a>
          @endforeach
        </nav>
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
