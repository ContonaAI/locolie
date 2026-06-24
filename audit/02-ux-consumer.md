# Audit 02 - UX of the consumer journey (public site + /app + customer report)

Scope: surfaces #1 (marketing/discovery), #2 (the `/app` consumer experience, web AND Capacitor),
and #4 (customer report "Your locolie"). Walks the full visitor journey: land -> category ->
shop profile -> claim/redeem -> report. Files read in full on `main` (2026-06-24):
`resources/views/site/{home,category,business}.blade.php`,
`resources/views/customer/{entry,report,layout}.blade.php`,
`resources/views/portal/home.blade.php` (the `/app` view, 1271 lines incl. `goLocalApp()` JS).
Grounded in `00-map-site.md`, `00-map-app-api.md`, `01-bench-marketplaces.md`,
`01-bench-services.md`, `03-current-design.md`.

Priority key: **P0** broken/blocking, **P1** hurts conversion, **P2** polish.
Tally: **6 P0, 14 P1, 12 P2** (32 findings).

---

## THE JOURNEY-BREAKING BUG (read first)

### P0-1. The redeem -> report loop is severed: redemptions carry no email, so "Your locolie" can never find them.
On first load `init()` seeds a fake customer with no email so the feed shows instantly:
`if(!this.customer){ this.customer = { name:'Guest', location:'NE', prefs:[] }; ...}` (`home.blade.php:945`).
The onboarding screen that actually captures `form.cEmail` (`home.blade.php:347`) is therefore
**skipped for every default visitor** - `customer` is already truthy, so `<template x-if="!customer">`
never renders. When the user redeems, `redeem()` posts
`customer_email: this.customer?.email` (`home.blade.php:1087`), which is `undefined` for the
guest. The API records a `Redemption` with a null/empty email. Then the customer report
`lookup()` checks `Redemption::where('customer_email', ...)->exists()` and finds nothing, so
`/my-locolie` always shows the "We could not find any locolie activity" empty state
(`customer/entry.blade.php:14-19`). The reflection half of the loop the map calls "coherent"
(`00-map-site.md:87`) is dead in practice.
**Fix:** require an email before the first reveal (gate redemption, not browsing, per
`01-bench-marketplaces.md:69`): if `!customer.email`, route into the onboarding email step on
"Reveal code" instead of seeding a permanent emailless guest. Minimum: a one-field
"Where should we send your code + savings?" sheet on first redeem.

---

## SURFACE #1 - Public marketing/discovery site

### Home (`site/home.blade.php`)

- **P1-2. Every primary CTA dumps the cold visitor into a guest app with no orientation.**
  "Find local deals", "Find something for...", featured cards and the hero all point at `/app`
  (or `/app?b=`) which, per P0-1, opens as an emailless "Guest". There is no welcome, no
  "you're browsing as a guest" state, and the onboarding that would set prefs/location never
  runs. The whole top-of-funnel promise ("get a nudge when there's a deal near you",
  `home.blade.php:104`) is unreachable because notifications require the onboarding push step.
  **Fix:** make the home CTAs land on the onboarding flow (or pass `?onboard=1`), so the
  geo/prefs/notify capture the marketing page advertises actually happens.

- **P1-3. Fabricated customer data shown as real, with no disclaimer.** The `#data` section
  hardcodes "128 captured", "94 opted in to marketing" and fake rows Sarah J./Mark T./Priya K./
  Dan W. (`home.blade.php:399-408`) plus an active "Email these customers" button. The case
  studies below at least carry "Illustrative results from early locolie pilots"
  (`home.blade.php:453`); this panel does not. Pre-launch, presenting invented metrics as a
  live dashboard erodes trust - the opposite of the "honest, plain-spoken" trust the benchmark
  prizes (`01-bench-services.md:62`).
  **Fix:** label it "Example dashboard" or wire it to real (even if zero) numbers.

- **P2-4. Newcastle/NE1 is hardcoded into the H1/subhead/stats band** ("Live in Newcastle now",
  `home.blade.php:27`; "Newcastle city centre / NE1" band, `home.blade.php:525`). Honest today,
  but the region globe already advertises 8 cities (`00-map-site.md:105`). Tokenise the city so
  the page doesn't lie the day Durham goes live.

- **P2-5. App Store / Google Play buttons are dead `href="#"`** (`home.blade.php:581,585`).
  They have `aria-label="...(coming soon)"` but visually look tappable and silently do nothing.
  **Fix:** style them as disabled "Coming soon" badges or anchor them to `/app`.

- **P2-6. "Open the live app" / featured cards open in a NEW TAB** (`target="_blank"`,
  `home.blade.php:299,356`). On mobile this spawns orphan tabs and loses the back button - the
  app is the destination, not a side trip. Open it in the same tab.

### Category (`site/category.blade.php`)

- **P1-7. Category cards skip the SEO shop page and deep-link straight to `/app?b={slug}`**
  (`category.blade.php:36`, new tab). The dedicated, JSON-LD-rich `/shop/{slug}` profile is
  bypassed entirely from the grid (confirmed `00-map-site.md:95`), so the strongest organic
  page is unreachable from its own category list, and the visitor is thrown into the guest app
  (see P0-1) before seeing offers, reviews or directions.
  **Fix:** card -> `/shop/{slug}` (keep the app handoff as the in-profile CTA), matching how
  `business.blade.php` related cards already link (`business.blade.php:105`).

- **P1-8. Zero sort/filter controls on a results page.** Bench flags this explicitly: Trivago
  has a sort dropdown, TaskRabbit cheap filter chips (`01-bench-marketplaces.md:17`,
  `01-bench-services.md:31`). The grid renders in raw `ranked()` order with no "Open now",
  "Has a live offer", "Top rated", "Nearest". `activeOffers` and `rating` are already loaded,
  so chips are cheap.
  **Fix:** add a filter-chip row + a Recommended/Nearest/Top-rated sort, per bench.

- **P2-9. Rating shown as "★ 4.0" with no review count and no open/closed**
  (`category.blade.php:48`). Bench: withhold/soften a rating under 3 reviews and pair it with a
  word label (`01-bench-marketplaces.md:45-47`). Standardise the trust line
  `★ rating (N reviews) · distance · Open/Closed` across all card surfaces.

- **P2-10. "Back more indies" lists sibling categories with no counts** and can be a long
  unscannable wrap (`category.blade.php:64-71`). Add the live-business count per chip (the home
  category grid already shows `{{ $c->live_count }}`, `home.blade.php:279`).

### Shop profile (`site/business.blade.php`)

- **P1-11. The page's only real action is "Open in the app" (new tab); the offers are not
  clickable.** The strongest element - the live discount rows (`business.blade.php:58-63`) - are
  inert divs. Bench: make the offer itself the CTA so the discount is the click target
  (`01-bench-marketplaces.md:59`).
  **Fix:** wrap each offer row in `/app?b={slug}` (same tab) so tapping a deal opens it.

- **P1-12. Offer terms fall back to a sentence that breaks the layout and states the obvious:**
  `{{ $o->terms ?: 'Open it in the locolie app and show your code at the till' }}`
  (`business.blade.php:61`). Every termless offer renders this long string as if it were terms.
  **Fix:** show nothing (or a quiet "No conditions") when `terms` is empty.

- **P2-13. No remaining-count urgency on the public offers.** The app already computes
  "X left" / "Sold out" (`home.blade.php:589`); `business.blade.php` shows none
  (`01-bench-marketplaces.md:51`). Surface real `remaining` here too (truthful scarcity only).

- **P2-14. Sidebar offers only "Get directions"; no Call / Share.** The app profile has a
  Call/Directions/Share row (`home.blade.php:583`); the SEO page is the one a visitor is most
  likely to land on cold and it can't call the shop (`01-bench-marketplaces.md:61`).

- **P2-15. "Sponsored" pill is the only differentiator and it reads pay-to-win.** No
  verification/trust mark next to the name (`business.blade.php:32` amber Sponsored only).
  Bench (Yelp/Thumbtack/Checkatrade) wants an earned green-shield "Independent verified" badge
  distinct from the amber paid pill (`01-bench-services.md:19,47,59`); the `onboarded` flag
  already exists.

- **P2-16. Reviews silently say "via Google" but the API fabricates sample reviews when a
  business has none** (`00-map-app-api.md:219`). A visitor can't tell real from synthesised.
  Either drop fabricated reviews or label them.

---

## SURFACE #2 - The /app consumer experience (web + Capacitor)

### Onboarding / first run

- **P0-17. iOS app loads a personal ngrok dev tunnel and the JS sends
  `ngrok-skip-browser-warning` on every API call.** `capacitor.config.json` `server.url` is
  `https://roger-...ngrok-free.dev/app` (`00-map-app-api.md:20`) and `api()` hardcodes the ngrok
  header (`home.blade.php:928`). A shipped build white-screens the moment the tunnel is down -
  i.e. always. **Blocking for any native release.** Point at `https://locolie.com/app`.

- **P0-18. The `/app` page still wears the internal mockup chrome.** Above the app it renders
  "locolie · Live prototype" and an H1 "One app, two logins" with dev copy
  (`home.blade.php:323-326`), and at the bottom a 6-card grid of INTERNAL TEAM links - "Business
  Plan", "Brand & Logos", "App Screens", "Data & Admin (Every business... in the live
  database)", "Mockups", "Ideas" (`home.blade.php:790-806`) linking to `portal.*` routes. In
  `solo` mode `.gl-chrome` is `display:none` (`home.blade.php:272`) so the hero hides, but the
  quick-links grid is NOT `.gl-chrome` and is NOT hidden - a real consumer can scroll past their
  app into the founders' internal admin links.
  **Fix:** wrap the quick-links grid (and the hero) so both are suppressed when `$solo`.

- **P1-19. Push permission is requested mid-onboarding behind "Enable alerts & finish"** without
  explaining value first, and on web `Notification.requestPermission()` fires immediately
  (`home.blade.php:1000`). A cold browser permission prompt at step 3 of 3 gets denied. Bench
  pattern is value-first, low-friction (`01-bench-marketplaces.md:69`). Also: native push won't
  deliver anyway (stock AppDelegate, no APNs entitlement - `00-map-app-api.md:149`), so the
  promise is hollow on iOS.

- **P1-20. Onboarding validates with `alert()`.** "Enter your name", "Please agree to the
  Terms..." are raw `alert()` calls (`home.blade.php:986`); business signup uses `alert()` too
  (`createOffer`, `redeem` errors, `home.blade.php:1091,1242`). Browser alerts feel broken/
  spammy and look nothing like the brand. **Fix:** inline field errors (the `entry.blade.php`
  `@error` pattern is the right model).

### Browse / search / map

- **P1-21. Applied filters vanish once the sheet closes.** The filter sheet is good
  (`home.blade.php:407-460`) but after Apply there are no removable chips under the search bar;
  only a count badge on the funnel icon. Bench: show removable filter pills so state is visible
  (`01-bench-marketplaces.md:19`).

- **P1-22. Bare dead-end empty states.** "No offers match here." (`home.blade.php:533`), "No
  results." (`home.blade.php:548`) - no "Clear filters", no "Browse all nearby". The Saved tab
  does it right ("Tap the heart on any shop to save it.", `home.blade.php:566`). Bench wants a
  next action in every empty state (`01-bench-marketplaces.md:75`).

- **P2-23. Map and list are separate tabs with no sync and no "Search this area".** Home list
  vs a Map tab; panning the map doesn't refilter the list and there's no redo-search affordance
  (`01-bench-marketplaces.md:37,39`). Add a bottom-sheet list on the Map tab and a "Search this
  area" button reusing `mapRefresh()`.

- **P2-24. No sort control in the list view and no heart on feed cards.** `visible` sorts by a
  fixed `this.sort` with no UI toggle (`home.blade.php:874`); the heart only appears once a
  profile is open (`home.blade.php:575`), not on `.hcard`/`.row` (`01-bench-marketplaces.md:29`).
  `toggleFav`/`isFav` already exist.

- **P2-25. The mock Google ad banner ("Ad · Google - Grow your local business") sits in the real
  consumer feed** (`home.blade.php:496-505`). It is a hardcoded placeholder, not a real ad, and
  links nowhere. In a live app it reads as broken/confusing. Remove until real ads exist.

- **P2-26. `callBiz`/`directions`/`share` fall back to `alert('... (demo)')`** when data is
  missing (`home.blade.php:1077-1079`) and `callBiz` even alerts a fake "0191 000 0000 (demo)".
  In production these demo alerts will fire on any business lacking a phone/coords.

### Redeem / code

- **P1-27. The reveal happens with no confirmation of what's being claimed and no consent
  recap.** `redeem()` fires immediately on tap and silently sends
  `marketing_opt_in: this.customer?.mkt !== false` (`home.blade.php:1087`) - i.e. a guest who
  never saw a consent box is opted IN to marketing by default. That is a consent/GDPR problem
  given the product's whole pitch is owning customer data.
  **Fix:** capture explicit opt-in at the email-gate (P0-1) and default to false.

- **P2-28. The code screen has no "add to wallet" / permanent home.** `view==='code'` is a
  transient screen; tapping "Done" returns home and the live code (with its countdown) is gone
  from the UI. Bench suggests a Wallet/My-codes tab so redeemed offers persist
  (`01-bench-marketplaces.md:81`). `lastCode` is single-slot; a second redeem overwrites it.

- **P2-29. Countdown + "Code expired" with no recovery.** When the TTL lapses the ticket shows
  "Code expired" (`home.blade.php:623`) with no "reveal again" button; the user is stuck on a
  dead ticket and must navigate back and re-find the offer.

### Capacitor-specific

- **P1-30. Native offline = a 6s "Can't reach Locolie" splash, then nothing usable.** `www/` is
  a splash-only shell; there is no offline bundle (`00-map-app-api.md:45,216`). On flaky mobile
  data the app is a white/again screen. At minimum cache the last feed.

- **P2-31. Camera scanner failure copy is fine but the scan tab can land on a black screen.**
  `startScan` sets "Camera unavailable - browse instead." on failure (`home.blade.php:1065`),
  but the tab opens to `background:#0b0b0c` with a scan frame before permission resolves; on
  desktop (no camera) it's a black box with a frame for a beat. Show a neutral placeholder first.

---

## SURFACE #4 - Customer report "Your locolie"

This surface is the **most polished and trustworthy** of the three: clean standalone shell
(`customer/layout.blade.php`), signed link, honest "Savings shown are estimates" footer
(`report.blade.php:156`), good empty state with a next action (`report.blade.php:25`), and
sensible whole-vs-pence formatting (`report.blade.php:9`). Findings are mostly upstream.

- **P0-32. It is functionally unreachable because of P0-1** - no redemption ever carries an
  email, so `lookup()` always flashes "We could not find any locolie activity..."
  (`entry.blade.php:14`, controller per `00-map-site.md:58`). Fix P0-1 and this surface lights
  up. (Counted once here; the root cause is P0-1.)

- **P1-33. No entry point for a logged-in app user.** The report is only reachable via the
  footer "Your savings" link on the marketing site (`00-map-site.md:61`) and signed email links.
  A shopper who just redeemed in `/app` has no in-app "Your savings" link, despite the app
  already holding their email after the gate.
  **Fix:** add a "Your locolie / savings" entry in the app (profile or a Wallet tab) that
  deep-links to `/my-locolie` prefilled.

- **P2-34. "Use the same email you gave when redeeming an offer" assumes the user remembers**
  (`entry.blade.php:39`). Combined with the guest flow giving no email at all, most users have
  no idea what they typed. Once P0-1 is fixed, prefill from `localStorage gl_customer.email`
  when the report is opened from the app.

- **P2-35. The savings figure is an estimate but the hero presents it as a hard "£X"** in a
  6xl number (`report.blade.php:41`) with the disclaimer 100+px below the fold of the card.
  Add a small "(estimated)" next to the figure so the headline isn't read as exact.

---

## Cross-cutting (affect every consumer screen)

- **P1-36. Two design systems collide across the journey.** Marketing site uses warm-grey/black
  + custom emerald `#059669`; the app uses CSS-var themes (default `mono`) and the customer
  report uses cool slate + stock Tailwind emerald (`03-current-design.md:76-110`). A visitor
  going home -> app -> report sees three different greys, two emeralds, and the wordmark in 4-5
  encodings (`03-current-design.md:114-131`). The handoff feels like three apps.

- **P1-37. Everything runs on CDN Tailwind play + CDN Inter, not the Vite build.** All consumer
  layouts load `cdn.tailwindcss.com` and Google Fonts at runtime (`03-current-design.md:36`),
  blocking first paint and explicitly not-for-production. The stated stack (Vite + compiled
  Tailwind v4) is unused. This is both a perf and a trust/polish issue on first load.

- **P2-38. The app left-over theme/brand cyclers and "Live prototype" tells leak prototype
  status** (`themes` object with 8 palettes, `brandNames` cycler, `home.blade.php:818-845`).
  Dead weight shipped to every consumer; harmless but signals "unfinished".

---

## Quick-win shortlist (highest leverage, lowest effort)

1. P0-1 / P0-32: gate redeem on email -> the entire redeem->report loop starts working.
2. P0-18: hide the internal `portal.*` quick-links + dev hero in `$solo` (one wrapper div).
3. P0-17: swap the ngrok `server.url` for `https://locolie.com/app`.
4. P1-7: point category cards at `/shop/{slug}` (one attribute).
5. P1-20: replace `alert()` validation with inline errors.
6. P2-25 / P2-3: remove the mock Google ad banner and label the fake customer dashboard.
