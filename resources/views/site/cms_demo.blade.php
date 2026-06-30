<!DOCTYPE html>
{{--
  Standalone proof of the DB-backed CMS round-trip. Every value below is read
  via the cms() helper with a hardcoded fallback, so editing it in
  /portal/content updates this page live. Deliberately self-contained so it
  touches no existing site views. Uses the shared $ll launch-market vars rather
  than re-hardcoding the city/price.
--}}
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title>CMS demo - {{ cms('home.hero.title', 'locolie') }}</title>
  <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    html { font-family: 'Inter', ui-sans-serif, system-ui, sans-serif; }
    body { background:
        radial-gradient(60rem 60rem at 110% -10%, rgba(5,150,105,.10), transparent 60%),
        radial-gradient(50rem 50rem at -10% 0%, rgba(15,45,56,.06), transparent 55%),
        #f8fafc; }
  </style>
</head>
<body class="text-slate-800 antialiased">
  <div class="mx-auto w-full max-w-3xl px-5 py-12 sm:py-16">

    <div class="mb-8 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
      <span class="font-bold">CMS round-trip demo.</span> Every field below is pulled from the database via <code class="font-mono bg-white/70 px-1 rounded">cms()</code>. Edit them in
      <a href="{{ url('/portal/content') }}" class="font-semibold underline">/portal/content</a> and refresh - this page updates with no redeploy.
    </div>

    {{-- ── Hero (home.* blocks) ─────────────────────────────────────────── --}}
    <section class="rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden">
      @php $heroImage = cms('home.hero.image'); @endphp
      @if ($heroImage)
        <img src="{{ $heroImage }}" alt="" class="w-full h-56 object-cover">
      @endif
      <div class="p-8 sm:p-10">
        <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">{{ cms('home.hero.eyebrow', 'Your high street, rewarded') }}</p>
        <h1 class="mt-2 text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900">
          {{ cms('home.hero.title', 'Discover and support local businesses') }}
        </h1>
        <p class="mt-3 text-slate-600 leading-relaxed">
          {{ cms('home.hero.subtitle', 'Find exclusive offers, collect loyalty rewards and back the independent shops near you.') }}
        </p>
        <p class="mt-1 text-sm text-slate-400">Launching in {{ $llCity }}.</p>
        <a href="{{ cms('home.hero.cta_url', '/local') }}"
           class="mt-6 inline-flex items-center rounded-xl bg-emerald-600 text-white text-sm font-bold px-5 py-3 hover:bg-emerald-700">
          {{ cms('home.hero.cta_label', 'Explore offers near me') }}
        </a>
      </div>
    </section>

    {{-- ── For-business (business.* blocks) ─────────────────────────────── --}}
    <section class="mt-6 rounded-3xl bg-white border border-slate-200 shadow-sm p-8 sm:p-10">
      <h2 class="text-2xl font-extrabold tracking-tight text-slate-900">{{ cms('business.hero.title', 'Grow your local business') }}</h2>
      <p class="mt-3 text-slate-600 leading-relaxed">{{ cms('business.hero.subtitle', 'Reach nearby shoppers, run offers and build a loyal customer base.') }}</p>
      <button class="mt-5 inline-flex items-center rounded-xl border border-slate-300 text-sm font-bold px-5 py-3 text-slate-700 hover:bg-slate-50">
        {{ cms('business.cta.label', 'List your business') }}
      </button>
    </section>

    {{-- ── Footer (footer.* blocks) ─────────────────────────────────────── --}}
    <footer class="mt-8 text-center text-sm text-slate-400">
      <p class="font-semibold text-slate-500">{{ cms('footer.tagline', 'Local first. Always.') }}</p>
      <p class="mt-1">{{ cms('footer.contact_email', 'hello@locolie.com') }}</p>
    </footer>

    {{-- ── Key/value debug table so the round-trip is obvious ───────────── --}}
    <section class="mt-10">
      <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wide mb-3">Keys rendered on this page</h3>
      <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
          <tbody class="divide-y divide-slate-100">
            @foreach ([
              'home.hero.eyebrow', 'home.hero.title', 'home.hero.subtitle',
              'home.hero.cta_label', 'home.hero.cta_url', 'home.hero.image',
              'business.hero.title', 'business.hero.subtitle', 'business.cta.label',
              'footer.tagline', 'footer.contact_email',
            ] as $k)
              <tr>
                <td class="px-4 py-2 font-mono text-xs text-slate-500 whitespace-nowrap align-top">{{ $k }}</td>
                <td class="px-4 py-2 text-slate-700">{{ \Illuminate\Support\Str::limit(cms($k, '(falls back to default)'), 90) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>

  </div>
</body>
</html>
