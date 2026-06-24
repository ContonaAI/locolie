# locolie (GoLocal) - Master Implementation Plan

Synthesised from the six audit streams in this folder (00 surface maps, 01 benchmarks,
02 UX, 03 design, 04 copy, 05 performance). Every item below cites a real file/route read
during the audit. House style: hyphens only, no em/en-dashes.

---

## 1. Executive summary

### State of the product

locolie is a Laravel monolith that already covers the full loop - discovery (marketing
site `site/*`), action (the `/app` consumer + business SPA in `portal/home.blade.php`),
reflection (customer report `customer/*`), and a physical on-ramp (QR sticker, window
decal). The marketing site and the customer report are genuinely polished and on-voice
("Back the indies. Bag the deals.", honest "Savings shown are estimates" footers). The
business CRM reports page and the Messaging Studio UI are well built. The architecture is
coherent and the sync pipeline (`SyncController` + `php artisan sync:push`) is the one
properly-secured, complete subsystem.

But the product is a prototype wearing production clothes, and three structural problems
cut across everything:

1. **The Vite/Tailwind v4 build that the repo is configured for is dead on arrival.** Every
   rendered surface loads Tailwind, Inter and Alpine from CDNs at runtime; the compiled
   `public/build/assets/app-*.css` is served by nothing but `welcome.blade.php`. This is the
   root of the design drift (two greys, two emeralds, four wordmark encodings), the perf
   problems, and the offline failure of the Capacitor app.
2. **The core consumer loop is severed.** `init()` seeds an emailless "Guest" so the
   onboarding email step never runs; redemptions are written with a null email; the customer
   report `lookup()` therefore never finds anything. The headline feature ("Your locolie"
   savings) is unreachable in practice.
3. **Nothing is really secured or billable.** The team portal admin/send endpoints are
   public (the gate is disabled, `settings.blade.php` admits it), the API has zero auth and
   zero rate limiting (an open billable Google Places proxy), billing can be self-upgraded
   for free, and the native app points at a personal ngrok tunnel.

### The 5 biggest opportunities

1. **Ship the Vite build and the unified design system.** One `@theme` block
   (`03-design-system.md` section 9 is paste-ready), four layouts switched to `@vite(...)`,
   CDN tags deleted. Fixes design drift, perf P0s, offline styling, and the GoLocal->locolie
   brand leaks in one coordinated sweep. Highest leverage change in the codebase.
2. **Reconnect the redeem -> report loop.** Gate redemption (not browsing) on a real email
   with explicit opt-in. This single fix lights up the customer report, makes consent
   GDPR-honest, and turns the "own your customers" pitch real.
3. **Lock it down for launch.** Re-enable the portal gate, add API throttling, verify Stripe
   server-side, point the app at locolie.com. Cheap, mandatory, mostly low-risk.
4. **Make the public listing and category pages convert and rank.** Standardise one trust-line
   card, route category cards to the SEO `/shop/{slug}` page, add sort/filter chips, add an
   earned green "verified" badge distinct from the amber "Sponsored" pill (the benchmark moat).
5. **Make the business CRM honest and self-sufficient.** Stop the "Quick email" lying about
   sending, add offer management + a profile-completion checklist, ship the compliant
   unsubscribe footer, relabel stubbed channels "not wired" instead of "sent".

---

## 2. Prioritised backlog

Grouped P0 -> P1 -> P2, then by surface. Effort S (<half day), M (1-2 days), L (3+ days).
Surfaces: SITE (#1), APP (#2), CRM (#3 business), RPT (#4 customer report), QR (#5),
PORTAL (#6), MSG (#7 Messaging Studio), DESIGN (cross-cutting), PERF (cross-cutting).

### P0 - broken / blocking / security

| ID | Pri | Surface | Title | What & why | Files to change | Effort | Risk |
|---|---|---|---|---|---|---|---|
| P0-1 | P0 | PORTAL | Re-enable the admin gate | Portal `middleware('portal')` is a pass-through; `settings.blade.php` L75-78 admits "anyone can reach these admin pages". Exposes `admin.onboard/plan/prospect.add/campaign` and all `messaging.*` send/connect routes. `robots.txt` is not access control. | `app/Http/Middleware/*` (portal gate), `config/portal.php`, `routes/web.php:76-130` | S | Med - verify team can still log in |
| P0-2 | P0 | APP | Reconnect redeem -> report loop | `init()` (`portal/home.blade.php:945`) seeds emailless Guest, so onboarding email step never renders; `redeem()` (L1087) posts `customer_email: undefined`; report `lookup()` finds nothing. Gate the first reveal on an email + explicit opt-in sheet. | `resources/views/portal/home.blade.php` (init, redeem, onboarding gate) | M | Med - core flow, build-and-verify |
| P0-3 | P0 | APP | Point native app at locolie.com, not ngrok | `capacitor.config.json` and `ios/App/App/capacitor.config.json` `server.url` is `roger-...ngrok-free.dev/app`; the JS sends `ngrok-skip-browser-warning` on every call (L928). Ships a white screen when the tunnel is down. | `capacitor.config.json`, `ios/App/App/capacitor.config.json`, `portal/home.blade.php:928` | S | Med - re-run `npx cap sync`, verify iOS |
| P0-4 | P0 | APP | Hide internal portal chrome in solo mode | `/app` still renders "Live prototype" hero (L323-326) and a 6-card grid of internal `portal.*` links (L790-806). `.gl-chrome` hides the hero in solo but the quick-links grid is NOT wrapped, so consumers can scroll into founders' admin links. | `resources/views/portal/home.blade.php:790-806` (wrap in `$solo` guard) | S | Low |
| P0-5 | P0 | CRM | Close the free billing self-upgrade hole | `upgradeSuccess()` (`BusinessPortalController.php:345-352`) is a GET that trusts `?plan=` with no Stripe session/webhook verification; with Stripe off, `upgrade()` falls through to `applyPlan` so anyone clicks to Premium free. | `app/Http/Controllers/BusinessPortalController.php`, `app/Services/BillingService.php`, add webhook route | M | High - billing integrity, needs review |
| P0-6 | P0 | CRM | Stop "Quick email" lying about sending | `emailCustomers` (`BusinessPortalController.php:117-135`) only writes a `Campaign` row and flashes "Email queued to N opted-in customers" - no dispatch. Route through `MessagingService::dispatch` or point the button at `business.messaging`. | `app/Http/Controllers/BusinessPortalController.php:117-135`, `business/dashboard.blade.php` | S | Low |
| P0-7 | P0 | PERF | Add rate limiting to API + login | `routes/api.php` has zero throttle; `GET /api/places/search` (`BusinessController@placesSearch:19`) is an open, unauthed, billable Google proxy. Add `throttle:api`, tighter `throttle:20,1` on places/register/redeem, `throttle:5,1` on both login routes. | `routes/api.php`, `routes/web.php:46,78` | S | Low |
| P0-8 | P0 | APP | Fix oversell race on limited offers | `RedemptionController@redeem` (L29) checks `isSoldOut()` but `redeemed_count` only increments at till verify (`RedemptionService:61-63`), so N concurrent reveals all pass. Use atomic conditional UPDATE or `lockForUpdate()`. | `app/Http/Controllers/Api/RedemptionController.php`, `app/Services/RedemptionService.php`, `app/Models/Offer.php` | M | High - hot path, build-and-verify |
| P0-9 | P0 | PERF | Switch all layouts to the Vite build | `cdn.tailwindcss.com` on every layout (`site:35`, `business:7`, `portal:12`, `customer:15`) is render-blocking, ~400KB, prints a "not for production" warning, breaks offline. Compiled CSS exists but is unused. Delete CDN tags + inline `tailwind.config`, add `@vite(...)`. Pairs with DESIGN-1. | `site/layout.blade.php`, `business/layout.blade.php`, `portal/layout.blade.php`, `customer/layout.blade.php`, `resources/css/app.css`, `resources/js/app.js` | L | High - re-skins everything, build-and-verify |
| P0-10 | P0 | QR/SITE | Fix GoLocal brand leaks | `demo/sticker.blade.php` L37 renders `Go<span>Local` in old orange `#D9603B`/`#FFA41C`; `portal/login.blade.php:8` says "GoLocal Portal". The sticker is the offline trust mark printed in shop windows. | `resources/views/demo/sticker.blade.php`, `resources/views/portal/login.blade.php` | S | Low |

### P1 - hurts conversion / core polish / trust

| ID | Pri | Surface | Title | What & why | Files to change | Effort | Risk |
|---|---|---|---|---|---|---|---|
| P1-1 | P1 | DESIGN | Apply the unified token system | Paste `03-design-system.md` section 9 `@theme` into `app.css`; sweep cool slate -> sand, stock emerald -> brand ramp, kill rogue blue `#2563eb` in `_phone`, unify the two `gradient-text` defs. Removes "three different greys, two emeralds" across the journey. | `resources/css/app.css`, `portal/layout`, `business/layout`, `customer/layout`, `site/layout`, `site/_phone.blade.php` | L | Med - depends on P0-9 |
| P1-2 | P1 | DESIGN | One wordmark + one button | Extract `<x-wordmark>` from the 4-5 inline pins (`$pin/$ppin/.brand-pin/$mpin/_phone`); replace bespoke button utility-soup with `.btn` variants (pills everywhere). | `components/wordmark.blade.php` (new), all four layouts, `_phone`, `_pricing` | M | Med |
| P1-3 | P1 | SITE | Route category cards to SEO shop page | `category.blade.php:36` deep-links cards to `/app?b={slug}` (new tab), bypassing the JSON-LD-rich `/shop/{slug}` and dumping cold visitors into the guest app. Point cards at `route('site.business', slug)` same-tab; keep app handoff as the in-profile CTA. | `resources/views/site/category.blade.php:36` | S | Low |
| P1-4 | P1 | SITE | Add sort + filter chips to category grid | Zero controls on a results page (`category.blade.php`). Add Recommended/Nearest/Top-rated sort + "Open now", "Has a live offer", "Top rated" chips. `activeOffers` and `rating` already loaded. | `resources/views/site/category.blade.php` | M | Low |
| P1-5 | P1 | SITE | Standardise the card trust line + verified badge | One "★ rating (N reviews) · distance · Open/Closed" line across `category`/`business`/app cards; show "New" under 3 reviews; add earned green "Independent verified" badge (`onboarded`) distinct from amber "Sponsored". | `site/category.blade.php:43-51`, `site/business.blade.php:32-44`, `portal/home.blade.php` `.hcard/.row` | M | Low |
| P1-6 | P1 | SITE | Make shop-page offers the clickable CTA | `business.blade.php:58-63` offer rows are inert divs; only CTA is "Open in the app" (new tab). Wrap each offer row in `/app?b={slug}` same-tab; fix terms fallback (L61) that prints a long sentence for every termless offer. | `resources/views/site/business.blade.php:54-66` | S | Low |
| P1-7 | P1 | APP | Replace alert() validation with inline errors | Onboarding (`home.blade.php:986`), `createOffer` (L1242), `redeem` (L1091) use raw `alert()`. Use the toast component + inline field errors from `03-design-system.md`. | `resources/views/portal/home.blade.php` | M | Low |
| P1-8 | P1 | APP | Show applied filters + actionable empty states | Filters vanish after Apply (only a count badge, L407-460); empties are dead strings ("No offers match here." L533, "No results." L548). Add removable filter pills + "Clear filters"/"Browse nearby" actions. | `resources/views/portal/home.blade.php` | M | Low |
| P1-9 | P1 | APP | Value-first push permission prompt | Web `Notification.requestPermission()` fires immediately mid-onboarding (L1000); cold prompt at step 3 gets denied. Explain value first. (Native push also undelivered - see P2 native push.) | `resources/views/portal/home.blade.php:1000` | S | Low |
| P1-10 | P1 | CRM | Add offer management to the web CRM | Dashboard offers panel is read-only (`dashboard.blade.php:54-72`); all CRUD punts to `/app?as=business`. Offers are the core unit. Add at minimum create + pause/end here. | `app/Http/Controllers/BusinessPortalController.php`, `resources/views/business/dashboard.blade.php` | L | Med |
| P1-11 | P1 | CRM | Add category picker (or drop dead rule) | `updateListing` (L315) validates+saves `category_id` but no dashboard field submits it. Add the picker (helps ranking, bench #15) or remove the dead validation. | `resources/views/business/dashboard.blade.php`, `BusinessPortalController.php:315` | S | Low |
| P1-12 | P1 | CRM | Profile-completion checklist | Dashboard drops a bare description/phone/website form with no nudge (Thumbtack/Yelp completeness). Add a % checklist (photo, description, phone, website, first offer). | `resources/views/business/dashboard.blade.php` | M | Low |
| P1-13 | P1 | CRM/MSG | Scope or relabel platform-wide push | Push audience count is `PushSubscription::count() + DeviceToken::count()` (`BusinessPortalController.php:165`) - the whole platform, not the brand. Undercuts "your own list". Scope to brand tokens or clearly label "platform broadcast" and gate it. | `BusinessPortalController.php:165`, `business/messaging.blade.php`, `PushChannel` | M | Med |
| P1-14 | P1 | MSG | Ship compliant unsubscribe footer | `emails/branded.blade.php` hardcodes `<a href="#">Unsubscribe</a>`; the correct signed GDPR/PECR footer `emails/partials/footer.blade.php` sits unused. Any real send is non-compliant. Include the partial; wire the preview link too. | `resources/views/emails/branded.blade.php`, `emails/partials/footer.blade.php`, `portal/messaging/email.blade.php:250` | S | Low |
| P1-15 | P1 | MSG | Honest channel status (no false "sent") | 5 of 6 SMS providers + iOS/Android push are stubbed but report `sent` (`00-map-portal.md`); Google "Connect" callback never exchanges the token. A connected-but-not-deliverable channel must say "logged only, delivery not wired" / "Connect (coming soon)". | `app/Services/Messaging/{SmsChannel,EmailStudioController}`, `business/messaging.blade.php`, `portal/messaging/*` | M | Med |
| P1-16 | P1 | RPT | In-app entry to "Your locolie" | Report only reachable via site footer + signed email links; a shopper who just redeemed has no in-app savings link though the app holds their email (after P0-2). Add a Wallet/profile link deep-linking `/my-locolie` prefilled. | `resources/views/portal/home.blade.php`, `CustomerReportController` | S | Low - depends on P0-2 |
| P1-17 | P1 | PERF | Add missing DB indexes | No index on `businesses.status/onboarded/featured/priority/plan`, `offers.status`, `redemptions.customer_email/phone/status` - the hot filter/sort/group columns. One additive migration. | new `database/migrations/*_add_hot_indexes.php` | S | Low |
| P1-18 | P1 | PERF | Fix ReportingService whole-table loads + N+1 | `platform()` does `Redemption::with('offer')->get()` (every row ever) + PHP count; `forBusiness()` ignores `$days` in SQL; `forCustomer()` N+1s on `category`. Push date window into query, eager-load `offer.business.category`, use SQL aggregates. | `app/Services/ReportingService.php` | M | Med - verify numbers match |
| P1-19 | P1 | PERF | Self-host/bundle fonts + Alpine, fix og/favicon | Drop Google Fonts links (render-blocking, wrong family vs `@theme`), bundle Alpine via `app.js` (3 unpinned CDNs today), add real `public/og.png` (missing) + fix 0-byte `favicon.ico`. | all four layouts, `resources/js/app.js`, `vite.config.js`, `public/og.png`, `public/favicon.ico` | M | Med - depends on P0-9 |
| P1-20 | P1 | PERF | Remove Google Translate from public funnel | `site/layout.blade.php:123-145` injects translate.google.com on every public page for a scaffolded feature; forces `body{top:0!important}` overrides + layout shift. Remove script, keep picker as no-op. | `resources/views/site/layout.blade.php:123-145` | S | Low |

### P2 - nice-to-have / polish / consistency

| ID | Pri | Surface | Title | What & why | Files to change | Effort | Risk |
|---|---|---|---|---|---|---|---|
| P2-1 | P2 | SITE | Tokenise hardcoded "Newcastle NE1" | `category.blade.php` writes "in Newcastle NE1" into every H1/title/meta regardless of city; home H1 too. Will misrepresent when the 8 advertised cities go live. | `site/category.blade.php`, `site/home.blade.php:27,525` | S | Low |
| P2-2 | P2 | SITE | Label or wire the fake customer dashboard | `home.blade.php:399-408` shows "128 captured / 94 opted in" with fake names and an active "Email these customers" button, no disclaimer (case studies below have one). Label "Example dashboard". | `site/home.blade.php:399-408`, `site/for-business.blade.php` | S | Low |
| P2-3 | P2 | SITE | Disabled "Coming soon" app-store buttons | `home.blade.php:581,585` App Store/Play buttons are dead `href="#"`. Style as disabled badges or anchor to `/app`. | `site/home.blade.php:581-585` | S | Low |
| P2-4 | P2 | SITE | Open app CTAs in same tab | Featured/"Open the live app" use `target="_blank"` (L299,356); on mobile spawns orphan tabs, loses back button. | `site/home.blade.php`, `site/category.blade.php`, `site/business.blade.php` | S | Low |
| P2-5 | P2 | APP | Remove mock Google ad banner | `home.blade.php:496-505` "Ad · Google - Grow your local business" placeholder sits in the real feed, links nowhere. | `resources/views/portal/home.blade.php:496-505` | S | Low |
| P2-6 | P2 | APP | Remove prototype theme/brand cyclers | 8-palette `themes` object + `brandNames` cycler (L818-845) leftover from internal mockup tool, shipped to every consumer. | `resources/views/portal/home.blade.php:818-845` | S | Low |
| P2-7 | P2 | APP | Demo-data fallbacks fire in prod | `callBiz/directions/share` alert "(demo)" / fake "0191 000 0000" when data missing (L1077-1079). Handle gracefully. | `resources/views/portal/home.blade.php:1077-1079` | S | Low |
| P2-8 | P2 | APP | Wallet / persistent code home | `view==='code'` is transient; `lastCode` is single-slot (a 2nd redeem overwrites it); no "reveal again" after expiry. Add a Wallet tab (bench: Booking Trips). | `resources/views/portal/home.blade.php` | M | Low |
| P2-9 | P2 | APP | Cache last feed for native offline | `www/` is splash-only; offline = the 6s "Can't reach Locolie" screen. Cache last feed / add skeletons. | `public/sw.js`, `resources/views/portal/home.blade.php` | M | Med |
| P2-10 | P2 | APP/MSG | Wire native iOS push (APNs) | Stock `AppDelegate.swift` lacks `didRegisterForRemoteNotifications` forwarding; `Info.plist` lacks `aps-environment` + `remote-notification` background mode; `sendApns/sendFcm` are stubs. Native push never delivers. | `ios/App/App/AppDelegate.swift`, `ios/App/App/Info.plist`, `app/Services/Messaging/PushChannel` | L | High - needs Apple cert/build |
| P2-11 | P2 | APP | Fix stale `/m` refs in service worker | `public/sw.js` precaches/falls back to `/m` (now 301s to `/app`); offline fallback + notification landing are a stale redirect hop. | `public/sw.js` | S | Low |
| P2-12 | P2 | APP | Drop fabricated reviews/distance from prod shape | `BrowseController` synthesises sample reviews (`reviews()` L141) and `fakeDistance()` (L165); app can't tell real from fake. Gate behind `?demo=1`. | `app/Http/Controllers/Api/BrowseController.php` | S | Low |
| P2-13 | P2 | CRM | No self-registration / forgot-password | `business/login` "New here?" links to marketing page; account creation is admin-only. Real owner clicking expects signup; dead end. | `resources/views/business/login.blade.php`, `BusinessPortalController` | M | Med - decide on flow |
| P2-14 | P2 | CRM | Remove demo creds from login HTML | `login.blade.php:36-42` prints `demo@locolie.test` / `golocal` in plain HTML. Remove before launch. | `resources/views/business/login.blade.php:36-42` | S | Low |
| P2-15 | P2 | CRM | Reports CSV honours selected range | `reportsExport` hardcodes `forBusiness($business,90)` (L255) ignoring the 7/14/30/90 control. | `app/Http/Controllers/BusinessPortalController.php:255` | S | Low |
| P2-16 | P2 | CRM/RPT | Fix bare-dash placeholders + filename drift | `' - '` for not-opted-in (`dashboard:112`), `'—'` default name (`customersFor:92`, house-style violation), `'-'` favourite-category (`report:57`); CSV `golocal-customers.csv` vs `locolie-report.csv`. Use words + one brand string. | `business/dashboard.blade.php`, `customer/report.blade.php`, `BusinessPortalController.php:92,112` | S | Low |
| P2-17 | P2 | PORTAL | Fold legacy admin Campaigns tab | `admin.campaign` (`adminSendCampaign:206`) is an older send path; its email "send" is just a recipient count (no delivery). Duplicates Messaging Studio. Deprecate/fold. | `app/Http/Controllers/PortalController.php:206`, `portal/admin.blade.php` | M | Low |
| P2-18 | P2 | PORTAL | Commit untracked reports view | `portal/reports.blade.php` is `??` in git status - uncommitted, could be lost. | `resources/views/portal/reports.blade.php` (git add) | S | Low |
| P2-19 | P2 | PORTAL | Rename "Home" nav to "App preview" | Team nav labels the 119KB app prototype just "Home", overlapping `/app`. | `resources/views/portal/layout.blade.php` | S | Low |
| P2-20 | P2 | MSG | Seed starter templates or drop picker | Email studio "Start from a template" picker is guarded by `$templates->isNotEmpty()` but nothing creates a `MessageTemplate` - always empty. Seed a few or remove. | `database/seeders/*`, `portal/messaging/email.blade.php` | S | Low |
| P2-21 | P2 | MSG | Preview first-paints matching channel | `messaging.blade.php:122` hardcodes the email preview; SMS/push re-fetch on switch but first paint is always email. | `resources/views/business/messaging.blade.php:122` | S | Low |
| P2-22 | P2 | MSG | Default initials 'GL' -> 'LO' | `emails/branded.blade.php:11` and previews default to `'GL'` (GoLocal). | `emails/branded.blade.php`, `messaging/previews/{email,push}.blade.php` | S | Low |
| P2-23 | P2 | MSG/CRM | Soften dev-speak copy | "Demo mode" -> "Not connected yet"; "Send campaign" -> "Send to {n} inboxes" (match SMS); pricing blurbs to concrete units; "Sponsored" perk -> "Featured local". From copy audit. | `business/messaging.blade.php`, `portal/messaging/*`, `site/_pricing.blade.php`, `site/for-business.blade.php` | M | Low |
| P2-24 | P2 | COPY | Remove em-dashes (house style) | `emails/branded.blade.php:110` live footer em-dash; comments at `footer.blade.php:4-5`, `portal/home.blade.php:895`. | as listed | S | Low |
| P2-25 | P2 | DESIGN | Adopt `<x-seal>` on the sticker | `<x-seal>` is the one systematised brand artifact (site footer only). Promote it to the `/s/{secret}` window sticker as the verified trust mark (bench #11). Pairs with P0-10. | `resources/views/demo/sticker.blade.php`, `components/seal.blade.php` | S | Low |
| P2-26 | P2 | DESIGN | Housekeeping sweep | Unify Alpine to one source; set `theme-color` `#0a0a0a` everywhere (customer is `#059669`); rename `fl_*`/`FL_*` localStorage keys to `ll_*`. | all four layouts, `site/home.blade.php` | S | Low |
| P2-27 | P2 | PERF | Lazy-load homepage map | `site/home.blade.php:7` loads Maps JS + markerclusterer on the marketing homepage, competing with LCP. Lazy-load on scroll via the existing reveal observer. | `site/home.blade.php:7`, `partials/google-maps.blade.php`, `resources/js/app.js` | M | Low |
| P2-28 | P2 | PERF | Image dimensions + lazy-loading | Only 1 img has width/height across all views; hero `business.blade.php:18` has no dims/loading. Add `width/height` + `loading=lazy decoding=async`; `fetchpriority=high` on hero. | `site/business.blade.php`, `site/layout.blade.php` | M | Low |
| P2-29 | P2 | PERF | Move inline layout CSS/JS into Vite | `site/layout.blade.php:54-117` (~60 lines CSS) + L446-529 (~80 lines JS) duplicated into every page; Nominatim fetch on load. Move into bundled `app.css`/`app.js`; defer geocode. | `site/layout.blade.php`, `resources/css/app.css`, `resources/js/app.js` | M | Med - depends on P0-9 |
| P2-30 | P2 | PERF | a11y: aria-current, alt text, tap targets | No `aria-current` on active nav (0 hits); 2 imgs no alt (`email.blade.php:207,226`); hover-only dropdowns + sub-11px text on touch. | all four layouts, `portal/messaging/email.blade.php` | M | Low |
| P2-31 | P2 | PERF | De-dupe brand-save + customer-capture logic | `MessagingController@saveBrand` and `BusinessPortalController@saveBrand` are byte-identical; `customersFor`/`audienceFor` opt-in semantics diverge. Extract a `BrandIdentity` action + single customer-capture query. | `MessagingController`, `BusinessPortalController`, `EmailStudioController`, `ReportingService` | M | Med |
| P2-32 | P2 | PERF | Tighten mass-assignment + cache homepage | Drop `owner_secret/password/priority/featured` from `Business $fillable` (foot-gun); `Cache::remember` homepage stats/cityData/mapPoints (P2-8 perf) + category descendant tree; scope `mapPoints` to `live()` (leaks lead coords). | `app/Models/Business.php`, `SiteController.php` | M | Low |

**Backlog counts: P0 = 10, P1 = 20, P2 = 32. Total = 62.**

---

## 3. Recommended implementation sequence

The audits define a natural dependency order. Three rules: do security/brand-leak fixes
first (cheap, mandatory), do the Vite+design switch as one supervised wave (it re-skins
everything), and **always `npm run build` locally and commit the compiled assets before
relying on them in prod** - the production deploy does not build assets (see deploy gotcha
in MEMORY / `golocal-production-deploy.md`). Any task touching `app.css`/`app.js`/Vite
must be followed by a local build + verify, never deployed on the assumption prod will
compile.

### Wave 0 - Launch-blocking security & integrity (do first, mostly safe unattended)
P0-1 (re-enable gate), P0-3 (ngrok -> locolie.com), P0-4 (hide portal chrome), P0-6 (kill
lying quick-email), P0-7 (API throttle), P0-10 (GoLocal brand leaks), P1-17 (DB indexes).
These are small, isolated, low-risk and unblock everything else. **Needs review/verify:**
P0-5 (Stripe verification) and P0-8 (oversell race) touch money and the redemption hot
path - do these attended with build-and-verify.

### Wave 1 - The Vite + design-system switch (one supervised wave)
P0-9 (switch layouts to `@vite`), then P1-1 (paste `@theme`, sweep slate->sand and
emerald->brand), P1-2 (one wordmark + button), P1-19 (bundle fonts/Alpine, og/favicon),
P1-20 (drop Google Translate), P2-29 (move inline CSS/JS into Vite). Do these together
because they all touch the four layouts and the build; doing them piecemeal re-breaks the
skin. **Must build-and-verify every surface visually after this wave**, and **run
`npm run build` + commit `public/build` before deploy.** Highest-risk wave; review required.

### Wave 2 - Reconnect the loop & convert (depends on Wave 1 for design tokens)
P0-2 (gate redeem on email - unblocks the whole report surface), then P1-7 (inline errors),
P1-8 (filter chips/empty states), P1-16 (in-app report entry), P1-3 (category->shop SEO),
P1-4 (sort/filter chips), P1-5 (trust-line card + verified badge), P1-6 (clickable offers).
P0-2 is core-flow: build-and-verify the redeem->report round trip end to end.

### Wave 3 - Business CRM honesty & self-sufficiency
P1-14 (compliant unsubscribe - do early, it is a compliance gap), P1-15 (honest channel
status), P1-10 (offer management - largest CRM item), P1-11 (category picker), P1-12
(completion checklist), P1-13 (scope/label push). P1-18 (ReportingService perf) fits here;
verify report numbers match before/after.

### Wave 4 - Polish, copy, perf cleanup (mostly safe unattended)
All P2s. Safe to batch: P2-1..P2-7, P2-11, P2-12, P2-14..P2-26 (copy/labels/dead code),
P2-18 (commit the untracked view). Needs verify: P2-9/P2-10 (native offline + APNs, needs an
iOS build + Apple cert), P2-13 (self-registration - product decision), P2-27..P2-32 (perf
refactors - verify behaviour). Em-dash sweep (P2-24) any time.

### Safe-to-do-unattended vs needs review
- **Unattended (isolated, low risk):** P0-1, P0-4, P0-6, P0-7, P0-10, P1-3, P1-6, P1-9,
  P1-11, P1-14, P1-17, P1-20, and almost all P2 copy/label/dead-code items.
- **Needs build-and-verify:** anything touching Vite/layouts (Wave 1), the redeem flow
  (P0-2, P0-8), reporting math (P1-18), and any native iOS change (P0-3, P2-9, P2-10).
- **Needs Tom's review (money/auth/product):** P0-5, P1-10, P1-13, P1-15, P2-13.

---

## 4. Items that need Tom's decision before building

1. **Billing model (P0-5, P1-4 pricing copy).** Charge now via Stripe (finish webhook +
   session verification), or stay "free at launch" and hide paid CTAs behind "coming soon"?
   This decides whether the self-upgrade hole is closed with real billing or by disabling
   the buttons. Cannot ship the CRM upgrade flow safely until decided.
2. **Retailer auth model (`00-map-app-api.md` 6.1, P0-3 perf).** `owner_secret` in URL
   paths is the entire retailer auth and is already in the wild (printed stickers,
   `localStorage gl_secret`). Move to header/bearer/Sanctum - but secrets must NOT be
   silently rotated. Needs a migration strategy decision.
3. **Business self-registration (P2-13).** Should owners self-serve register + claim a
   listing (with ownership proof / email verification), or stay admin-provisioned? Today
   anyone can POST `/api/businesses` with a `place_id` and claim a real business and its
   offers. This is both a UX dead end and a launch-blocking trust gap.
4. **Category card link target (P1-3).** Confirmed direction: cards -> `/shop/{slug}` for
   SEO, app handoff as in-profile CTA. Flagged for sign-off only because it changes the
   funnel shape from current behaviour.
5. **Push positioning (P1-13).** Is push a per-brand channel or a platform-wide broadcast?
   Today the count is platform-wide while sold as "your own list". Decide before relabelling.
6. **Brand face: Inter or Instrument Sans (P0-9, P1-19).** `@theme` declares Instrument
   Sans (self-hosted via Vite) but every page renders Inter from Google. Pick one before the
   Vite switch self-hosts it. The design system recommends Inter for visual continuity.
7. **Trust framing: "Sponsored" vs "Featured local" (P1-5, copy audit C/E/F).** The
   benchmark mandate is an earned green badge distinct from paid amber. Confirm the
   verification criteria (the `onboarded` flag) and the renamed badge copy.
8. **Native release readiness (P0-3, P2-9, P2-10).** A real iOS build needs the
   locolie.com URL, APNs entitlement + cert, AppDelegate hooks, and an offline story.
   Decide whether to ship native now (web-only push) or hold the App Store build until
   APNs is wired.
