# 05 - Frontend Performance, Accessibility & Asset Audit

Grounded in 00-map-site.md, 00-map-business.md, 00-map-portal.md, 00-map-app-api.md, 01-bench-marketplaces.md, 01-bench-services.md, 03-current-design.md.

Scope read: `resources/css/app.css`, `resources/js/app.js`, `vite.config.js`, all four shell layouts (`site/layout`, `business/layout`, `portal/layout`, `customer/layout`), `public/` (build manifest, sw.js, icon.svg, manifest.webmanifest, favicon.ico), and heavy views (`site/home`, `site/business`, `business/dashboard`, `portal/design`, `partials/google-maps`).

## Headline finding

The whole product ships a **Vite + Tailwind v4 build that is never loaded by any real surface**. Every shell layout instead pulls Tailwind, fonts and Alpine from third-party CDNs at runtime. The compiled `@vite(['resources/css/app.css','resources/js/app.js'])` is only referenced by `resources/views/welcome.blade.php` (the stock Laravel page, not a routed surface). So the 89 KB compiled CSS in `public/build/assets/app-q-EUUMYd.css` and the self-hosted Instrument Sans woff2 fonts (also in the build) are dead weight that nothing serves, while production pages load uncompiled Play-CDN Tailwind, Google Fonts (Inter), and CDN Alpine on every request. This is the single biggest issue and the root of most P0s below.

---

## P0 - Critical

### P0-1 Tailwind Play CDN in production on every surface
- Where: `resources/views/site/layout.blade.php:35` `<script src="https://cdn.tailwindcss.com"></script>`; same in `business/layout.blade.php:7`, `portal/layout.blade.php:12` (`?plugins=typography`), `customer/layout.blade.php:15`.
- Why P0: `cdn.tailwindcss.com` is render-blocking, ~400 KB+ of JS, compiles utility CSS in the browser on the client's main thread (FOUC + slow LCP), and the script itself prints a console warning that it must not be used in production. Tailwind v4 is already built locally (`public/build/assets/app-q-EUUMYd.css`, 89 KB, and gzips far smaller) but is unused. On the Capacitor iOS wrapper this CDN call also means the app cannot style itself offline despite the service worker.
- Fix: Delete all four `cdn.tailwindcss.com` script tags and the inline `tailwind.config = {...}` blocks. Move the theme tokens (`ink #0a0a0a`, `emerald #059669`/`soft #d1fae5`, `muted #737373`, `hair #e5e5e5`, `borderRadius card 18px`) into `resources/css/app.css` `@theme {}` (it currently only declares `--font-sans`). Add `@vite(['resources/css/app.css','resources/js/app.js'])` to the `<head>` of all four layouts. Then `@source` scanning in app.css already covers `storage/framework/views` so utilities used in Blade get emitted.

### P0-2 Render-blocking Google Fonts (Inter) loaded over network, duplicating self-hosted fonts
- Where: `site/layout.blade.php:31-33`, `business/layout.blade.php:9`, `customer/layout.blade.php:11-13` load `fonts.googleapis.com/css2?family=Inter:...900`; `portal/layout.blade.php:16` loads Inter + JetBrains Mono + Instrument Serif.
- Why P0: render-blocking external stylesheet on the critical path, plus a font the design system does not actually standardise on. `vite.config.js` already self-hosts **Instrument Sans** via `bunny('Instrument Sans', { weights:[400,500,600] })` and the woff2 files exist in `public/build/assets/`. The site loads Inter weights 400-900 (9 weights) from Google instead. So fonts are both render-blocking AND the wrong family AND never preloaded.
- Fix: Drop the Google Fonts `<link>`s and the `preconnect`s to googleapis/gstatic. Use the Vite-bundled Instrument Sans (the `@vite` include in P0-1 pulls `fonts-DkuEHybc.css`). If Inter is the intended brand face, switch the bunny() font in vite.config.js to Inter and self-host it; do not load it from Google. Decide on ONE family - right now `app.css` theme says Instrument Sans while every live page renders Inter.

### P0-3 Google Translate widget injected on the public marketing layout
- Where: `site/layout.blade.php:123-145` - inline init script plus `<script src="//translate.google.com/translate_a/element.js...">`.
- Why P0: this loads a heavy, render-affecting third-party bundle on every public page (home, category, business detail) for a feature that is effectively scaffolding (`languages` array comment line 150: "English live; rest scaffolded"). It mutates the DOM, forces `body { top: 0 !important; }` overrides (lines 113-116) to undo its banner, and adds layout shift. Protocol-relative `//translate.google.com` will also break under the app's CSP if one is added.
- Fix: Remove the Google Translate include and `flTranslate`/`googleTranslateElementInit` scripts until translation is a shipping feature. Keep the language picker UI but make it a no-op or hide it. This alone removes a large blocking dependency from the primary funnel.

---

## P1 - High

### P1-1 No `og:image` default and a referenced `/og.png` that does not exist
- Where: `site/layout.blade.php:15` declares `twitter:card = summary_large_image` but the head has no `og:image`/`twitter:image`. `site/business.blade.php:9` LD+JSON falls back to `url('/og.png')`. `ls public/og.png` -> not found.
- Why P1: every share/social card renders blank; `summary_large_image` with no image is a broken card. Also `public/favicon.ico` is **0 bytes** (`wc -c` = 0), so browsers/tabs show a broken favicon (only `/icon.svg` works).
- Fix: Add a real `public/og.png` (1200x630) and a default `<meta property="og:image">`/`twitter:image` in `site/layout` head. Either generate a real `favicon.ico` or delete it and rely on the SVG `<link rel="icon" href="/icon.svg">`.

### P1-2 Alpine.js loaded from three different CDNs, none pinned, none bundled
- Where: `site/layout.blade.php:52` and `business/layout.blade.php:8` use `cdn.jsdelivr.net/npm/alpinejs@3.x.x`; `portal/layout.blade.php:13` uses `unpkg.com/alpinejs@3.x.x`; `customer/layout.blade.php:26` uses jsdelivr.
- Why P1: `@3.x.x` is unpinned (supply-chain + cache-busting risk), three hosts means three preconnects/handshakes across the product, and Alpine drives core UI (mobile nav `x-data="{open}"`, cookie banner, dropdowns). On the Capacitor build a CDN Alpine means the menu does not work offline.
- Fix: `npm i alpinejs`, import + `Alpine.start()` in `resources/js/app.js` (currently just `//`), and let Vite bundle it. Remove all three CDN `<script>` tags.

### P1-3 Google Maps + markerclusterer loaded on the homepage
- Where: `site/home.blade.php:7` `@include('partials.google-maps')`; `partials/google-maps.blade.php:15` loads `https://unpkg.com/@googlemaps/markerclusterer@2.5.3`. Home also injects `window.FL_POINTS = @json($mapPoints)` inline.
- Why P1: the Maps JS API plus an unpkg cluster lib are heavy and run on the marketing homepage, competing with LCP. The inline `FL_POINTS`/`FL_CITIES` JSON also bloats the HTML document.
- Fix: Lazy-load the map only when its container scrolls into view (IntersectionObserver - the reveal observer already exists in `site/layout.blade.php:497`). Self-host/bundle markerclusterer or load it with `defer` only after the map section is visible. Consider passing map points via a small JSON endpoint rather than inlining into the document.

### P1-4 Listing images have no width/height and no lazy-loading -> layout shift
- Where: `site/business.blade.php:18` hero `<img ... class="h-full w-full object-cover">` (no `width`/`height`, no `loading`, no `decoding`); related-shop thumbs line 106 have `loading="lazy"` but still no intrinsic dimensions. The mega-menu featured image (`site/layout.blade.php:235`) is a CSS `background-image` so it cannot lazy-load. Across all views only **1 img has width= and 1 has height=**.
- Why P1: missing intrinsic dimensions cause CLS as photos load; the LCP hero image on a business page is not prioritised. Business `photos` are author-uploaded URLs with no size constraint, so a large original can dominate LCP.
- Fix: Add explicit `width`/`height` (or aspect-ratio container, which the `h-64`/`h-36` wrappers partly give) AND `loading="lazy" decoding="async"` to every `<img>` except the above-the-fold hero, which should get `fetchpriority="high"`. Constrain/transform uploaded photos to a sensible max width at storage time.

### P1-5 Large inline `<style>` and `<script>` blocks in the marketing layout
- Where: `site/layout.blade.php:54-117` (~60 lines of CSS: glassmorphism, mesh blobs, keyframes, reveal) and lines 446-529 (~80 lines of JS: geolocation reverse-geocode, IntersectionObserver reveal, parallax, count-up).
- Why P1: this CSS/JS is duplicated into every page's HTML (no caching across navigations), inflating document size and TTFB-to-render. The reverse-geocode `fetch` to `nominatim.openstreetmap.org` (line 460) also fires on load and is a third-party dependency in the critical path.
- Fix: Move the static CSS into `resources/css/app.css` and the static JS (reveal, parallax, count-up, geoArea) into `resources/js/app.js` so Vite bundles + caches them once. Gate the Nominatim call behind a user gesture or defer it well past load.

---

## P2 - Medium

### P2-1 Two `<img>` with no `alt`
- Where: `portal/messaging/email.blade.php:207` and `:226` - `<img :src="preview.logoUrl" ...>` inside `x-if`, no `alt`. (Internal portal preview, hence P2 not P1.)
- Fix: add `alt=""` (decorative preview) or `alt="Brand logo"`.

### P2-2 No `aria-current` on active nav links
- Where: navs in `business/layout.blade.php:28-33`, `portal/layout.blade.php:60-87`, `site/layout.blade.php` - active state is conveyed by colour/background only (`request()->routeIs(...)`). `grep aria-current` = 0 hits.
- Why: screen-reader users get no programmatic "you are here". Active state is also colour-only (emerald text), an a11y contrast/affordance gap.
- Fix: add `@if(request()->routeIs($r)) aria-current="page" @endif` to active nav links.

### P2-3 Icon-only / low-affordance interactive elements
- Where: `site/layout.blade.php:420` cookie-settings is a `<button onclick="...localStorage...">` (fine) but the language buttons (lines 285-288, 335-338) are unlabeled `<button>`s with only flag+text; the region globe button has `aria-label` (good, line 256) but the categories mega-menu button (line 204) has none. Mobile hamburger has `aria-label="Menu"` (good).
- Fix: add `aria-label` to the categories trigger and ensure the dropdown panels are reachable by keyboard (currently open on `@mouseenter` only - keyboard users can `@click` it, verify focus is trapped/escapable).

### P2-4 Hover-only dropdowns and tiny tap/----text targets
- Where: desktop Categories + region menus open via `@mouseenter`/`@mouseleave` (`site/layout.blade.php:203,255`); portal Design dropdown same (`portal/layout.blade.php:67`). Heavy use of `text-[10px]`/`text-[11px]`/`text-[9px]` (12 occurrences in site layout, 4 in home).
- Why: hover-only menus are unusable on touch (the Capacitor iOS app is touch-first) unless the `@click` fallback fully works; sub-11px text is below comfortable mobile legibility and some `text-[9px]` "SOON" labels (layout line 327) risk contrast issues.
- Fix: confirm the `@click` toggle works without hover on touch; bump the smallest labels to >=11px and verify the muted greys (`text-muted #737373` ~4.7:1 OK on white, but `text-ink/50` and `text-slate-400` on white drop below 4.5:1) meet WCAG AA.

### P2-5 Service worker cache + manifest mismatch with routes
- Where: `public/sw.js:6` precaches `'/m'` and on fetch-fail falls back to `caches.match('/m')` (lines 28, 51), but the app route is `/app` (`routes/web.php:40`), not `/m`. `public/manifest.webmanifest` should be checked for matching `start_url`.
- Why: the offline fallback navigates to a route that may not exist, and the SW caches third-party CDN responses (Tailwind/Alpine/fonts) opaquely with no versioning beyond `golocal-v2` - so the CDN dependencies in P0/P1 also pollute the cache.
- Fix: align the SW app-shell path with `/app`; once assets are Vite-bundled (P0-1/P1-2) the SW can cache the local hashed assets reliably instead of opaque CDN responses.

### P2-6 `portal/design.blade.php` embeds an 80vh iframe
- Where: `portal/design.blade.php:16` `<iframe src="{{ route('portal.design.raw') }}" style="height:80vh">`.
- Why: the iframe loads a second full HTML document (its own CDN Tailwind/fonts again). Internal-only, hence P2, but it doubles the asset cost of that page.
- Fix: after P0-1, the raw design page will at least share the bundled CSS; consider `loading="lazy"` on the iframe.

---

## Quick wins (do first)
1. Add `@vite(...)` to the four layouts and delete the `cdn.tailwindcss.com` tags (P0-1).
2. Delete Google Fonts links; use bundled font (P0-2).
3. Remove Google Translate scripts (P0-3).
4. Add real `og.png` + fix 0-byte `favicon.ico` (P1-1).
5. Bundle Alpine via `app.js` (P1-2).

## Severity counts
- P0: 3
- P1: 5
- P2: 6
- Total: 14
