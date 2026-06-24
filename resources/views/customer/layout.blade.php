<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title>@yield('title', 'Your locolie')</title>
  <meta name="description" content="See how much you've saved shopping local with locolie.">
  <meta name="theme-color" content="#059669">
  <link rel="icon" href="/icon.svg" type="image/svg+xml">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] },
          colors: { emerald: { DEFAULT: '#059669', soft: '#d1fae5' } },
        },
      },
    };
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <style>
    html, body { font-family: 'Inter', system-ui, sans-serif; }
    body { -webkit-font-smoothing: antialiased; }
    [x-cloak] { display: none !important; }
    ::selection { background: #05966933; }

    /* Emerald map-pin dots that replace the two "o"s in the locolie wordmark. */
    .brand-pin { display:inline-block; width:.62em; height:.62em; border-radius:50% 50% 50% 0; background:#059669; transform:rotate(-45deg); margin:0 .04em; }

    .gradient-text { background: linear-gradient(120deg, #059669, #10b981 45%, #047857); -webkit-background-clip: text; background-clip: text; color: transparent; }

    @keyframes pop { 0% { transform: scale(.92); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
    .animate-pop { animation: pop .6s cubic-bezier(.16,.8,.3,1) both; }
    @media (prefers-reduced-motion: reduce) { .animate-pop { animation: none; } }
  </style>
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased">

  {{-- Minimal top bar with the locolie wordmark --}}
  <header class="sticky top-0 z-30 bg-white/85 backdrop-blur border-b border-slate-200">
    <div class="max-w-lg mx-auto px-5 h-14 flex items-center justify-center">
      <a href="{{ route('app') }}" class="text-xl font-extrabold lowercase tracking-tight text-slate-900">l<span class="brand-pin"></span>c<span class="brand-pin"></span>lie</a>
    </div>
  </header>

  <main class="max-w-lg mx-auto px-5 pb-16">
    @yield('content')
  </main>

</body>
</html>
