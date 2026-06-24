# Surface map: Public marketing/discovery site (#1), Customer report (#4), QR sticker (#5)

Audited from real files on `main` (2026-06-24). Controllers: `app/Http/Controllers/SiteController.php`, `app/Http/Controllers/CustomerReportController.php`, `app/Http/Controllers/PortalController.php` (qrRedirect/sticker). Data service: `app/Services/ReportingService.php::forCustomer()`.

---

## 1. Public marketing + discovery site (SiteController)

Shared chrome lives in `resources/views/site/layout.blade.php`: floating glass nav, categories mega-menu, region/language globe, footer, cookie consent, scroll-reveal/parallax/count-up JS. All four routed pages `@extends('site.layout')`.

### Route table

| Route name | URL | Controller method | View | State |
|---|---|---|---|---|
| `site.home` | `/` | `home()` | `site/home.blade.php` | Done, polished, real data |
| `site.for-business` | `/for-business` | `forBusiness()` | `site/for-business.blade.php` | Done, polished, mostly static copy |
| `site.category` | `/category/{slug}` | `category()` | `site/category.blade.php` | Done, real data |
| `site.business` | `/shop/{slug}` | `business()` | `site/business.blade.php` | Done, real data |

### `/` home (`site/home.blade.php`, 51KB)
- Purpose: top-of-funnel marketing for two audiences (shoppers + retailers) plus discovery entry points. Used by anyone landing cold.
- Real data from controller: `Business::live()->count()`, active offer count, leaf-category count (`stats`), `categoriesWithCounts()`, 6 `featured` businesses with photos, `cityData` (per-city counts for the geo hero), `mapPoints` (lat/lng dots for the reach map).
- Sections in order: hero (geo-aware badge via `geoArea()` + Nominatim reverse-geocode, `_phone` mock, floating "Email/SMS/Push sent" chips), two-sided value cards, Google reach map (`#reachmap`), problem stats, USP grid (`#how`), "Find something for..." occasion chips, browse-by-category (`#categories`), featured indies (`#demo` live demo), own-your-customers (`#data`), case studies (`#stories`), comparison table, 3-step how-it-works, stats band, pricing (`#pricing` via `_pricing`), founders (`#founders`), app download (`#download`), final CTA.
- Key copy: H1 "Back the indies. Bag the deals." Subhead "Live in Newcastle now, rolling out across the UK next." Founders named Tom / Joe / Roddy with roles.
- Connections out: `/app` (many CTAs), `/app?b={slug}` (featured cards, new tab), `/app?as=business` (business view), `/for-business`, `/category/{slug}` (occasion chips + category grid), `/business/login` (via `_pricing`).

### `/for-business` (`site/for-business.blade.php`, 30KB)
- Purpose: retailer conversion page. Used by shop owners.
- Only dynamic input is `plans => Business::PLANS` (passed to `_pricing` with `big=true`); everything else is hardcoded marketing copy.
- Sections: hero ("Get found by locals who want to back the indies"), 3 value props, own-your-customers (dummy customer list "128 captured", names Sarah J./Mark T./Priya K./Dan W.), marketing toolkit (Email/SMS/Push/Win-back), "Replace your stack" (Klaviyo/Twilio/OneSignal comparison, "£84+/mo & 4 logins" vs "From £0 up to £49/mo"), pricing, 4-step onboarding, "Badge of Honour" window-decal + till mockups with a deterministic decorative QR and `<x-seal>`.
- All conversion CTAs point to `/business/login` (surface #3) and `/app?as=business`.

### `/category/{slug}` (`site/category.blade.php`)
- Purpose: SEO landing per category. `category()` resolves slug, gathers the category id + every descendant id via recursive `categoryAndDescendantIds()` (handles parent -> sub -> sub-trade tree), lists `Business::live()->ranked()`.
- Cards link to `/app?b={slug}` (NOT `/shop/{slug}`). Empty state CTA -> `/for-business`. "Back more indies" chips -> sibling `/category/{slug}`.

### `/shop/{slug}` (`site/business.blade.php`)
- Purpose: SEO + share landing for one business. `business()` loads a live business with category + activeOffers, plus 3 `related` in same category. Emits `LocalBusiness` JSON-LD + og:image.
- Shows hero photo, breadcrumb, rating/reviews count, live offers, Google reviews (from `$business->reviews` array), Visit card with Google Maps directions link, "Is this your shop?" claim box -> `/for-business`.
- Primary CTA "Open in the app" -> `/app?b={slug}` (new tab). Related cards link to `/shop/{slug}` (consistent here).

### Partials
- `site/_phone.blade.php` (10KB): static pixel-perfect phone mock of the app home screen with a looping CSS "Code redeemed" toast. Renders real `$cards` (featured businesses) when passed, else `$demoCards` ("Bones Club Barbershop", "Newcastle Fitness Club"). Whole frame is an `<a href="/app">` opening the real app.
- `site/_pricing.blade.php`: 3-card grid from `Business::PLANS` with hardcoded `$blurbs`. Every plan CTA -> `/business/login` (free plan = "List my shop free", paid = "Choose {label}").
- `partials/google-maps.blade.php`: async Maps loader, only emits if `$key` present.

---

## 4. Customer report "Your locolie" (CustomerReportController)

| Route name | URL | Method | View | State |
|---|---|---|---|---|
| `customer.report.entry` | GET `/my-locolie` | `entry()` | `customer/entry.blade.php` | Done |
| `customer.report.lookup` | POST `/my-locolie` | `lookup()` | (redirect) | Done |
| `customer.report` | GET `/my-locolie/view` | `show()` | `customer/report.blade.php` | Done; **`signed` middleware** |

- Layout `customer/layout.blade.php` is its own minimal mobile shell (NOT `site.layout`): centered locolie wordmark linking to `/app`, max-w-lg. So this surface is intentionally standalone, feels like the app, not the marketing site.
- `entry`: email form. On submit `lookup()` validates email, checks `Redemption::where('customer_email',...)->exists()`. If none: flashes `notfound` "We could not find any locolie activity for {email} yet." If found: redirects to a 30-day `URL::temporarySignedRoute('customer.report', ..., ['email'=>...])` so the report can't be guessed by editing the URL.
- `show()` renders `ReportingService::forCustomer($email)`: KPIs (saved £, redemptions, businesses, favourite_category), `places` (with logo/brandColor/initials/visits), `timeline` (last 10 redeemed), `categories` bar chart. Savings are estimates (`estimateSaving($offer)`); footer says "Savings shown are estimates based on the offers you redeemed."
- `report.blade.php` has a clean empty state (`found=false`) and a populated state. Places link to `route('site.business', slug)` -> `/shop/{slug}` (ties report back into surface #1). All other CTAs -> `/app`.
- Entry point in: footer "Your savings" link in `site/layout.blade.php` (`route('customer.report.entry')`). Also designed to be the target of signed links in customer emails/SMS (per controller docblock).

---

## 5. QR window sticker (PortalController)

| Route name | URL | Method | View | State |
|---|---|---|---|---|
| `qr.redirect` | `/c/{token}` | `qrRedirect()` | (redirect) | Done |
| `qr.sticker` | `/s/{secret}` | `sticker()` | `demo/sticker.blade.php` | Rough/demo |

- `qrRedirect($token)`: looks up `Business::where('qr_token', $token)`, 404 or redirects to `route('app', ['b'=>slug])`. This is the live deep link printed in the QR. Clean.
- `sticker($secret)`: owner-secret-scoped printable sticker. Generates QR client-side and passes `url = route('qr.redirect', token)`.
- `demo/sticker.blade.php`: **inconsistent / dated.** It is a standalone HTML doc (not Blade layout), uses the OLD brand `Go<span class="pin"></span>Local` ("GoLocal") in the `.brand` div even though the footer text and everything else now says "locolie". Pulls fonts + qrcodejs from external CDNs (jsdelivr, Google Fonts). Color vars `--accent:#D9603B; --cta:#FFA41C` are an old orange palette that does not match the current emerald `#059669` brand. `noindex,nofollow`. View path `demo/` and method comment "Printable window sticker" suggest this is leftover demo scaffolding not aligned with the For-Business "Badge of Honour" seal design.

---

## Visitor journey: land -> browse -> shop -> redeem -> report

1. **Land** on `/` (or a shared `/shop/{slug}` / `/category/{slug}`). Geo badge tries to localise; everything funnels to `/app` or a category.
2. **Browse a category**: `/category/{slug}` lists live indies (parent rolls up descendants).
3. **View a shop**: `/shop/{slug}` SEO profile with offers, reviews, directions.
4. **Into the app / redeem**: every "open / offer" CTA jumps to `/app?b={slug}` (surface #2, PortalController@mobile) where the actual reveal-code + at-till redemption happens. Marketing site itself does NOT redeem.
5. **Customer report**: after redeeming (which writes a `Redemption` with `customer_email`), the shopper reaches `/my-locolie`, enters email, and lands on the signed `/my-locolie/view` showing savings/impact, with "Your places" linking back to `/shop/{slug}`.
6. The QR sticker (`/c/{token}`) is the physical-world on-ramp: scan in a shop window -> straight to `/app?b={slug}`.

The loop is coherent: discovery (site) -> action (app) -> reflection (report) -> back to discovery.

---

## Flags: orphaned / duplicated / half-built / untrustworthy

1. **`demo/sticker.blade.php` is off-brand and stale (highest priority).** Renders the dead "GoLocal" wordmark and an orange palette (`#D9603B`/`#FFA41C`) against the live emerald "locolie" brand. It is the only live-routed page (`/s/{secret}`) still showing the old identity. The For-Business page already has a polished "Badge of Honour" sticker design + `<x-seal>` component the real printable should adopt. Looks unfinished/untrustworthy if a retailer prints it.

2. **Inconsistent link target between category and shop cards.** `category.blade.php` cards link to `/app?b={slug}`, but `business.blade.php` related cards link to `/shop/{slug}`, and home featured cards link to `/app?b={slug}`. The dedicated SEO `/shop/{slug}` page is therefore bypassed from the category grid - it is only reachable via breadcrumbs, related cards, the nav mega-menu featured shop, and the customer report. Worth deciding whether category cards should deep-link the app or the SEO profile.

3. **Dummy/illustrative data presented as real on marketing pages.** Home `#data` and For-Business both hardcode a "128 captured / 94 opted in" customer list with fake names (Sarah J., Mark T., Priya K., Dan W.) and case-study stats (+38%, 210 customers, £0). Home case studies do carry a disclaimer ("Illustrative results from early locolie pilots"); the For-Business "128 captured" panel does not. Pre-launch this risks reading as inflated/untrustworthy.

4. **Newcastle/NE1 is hardcoded across category copy.** `category.blade.php` literally writes "in Newcastle NE1" and "near you" into the H1/title/meta for every category regardless of the business's actual `city`. Fine while single-city, but will misrepresent once the "coming soon" cities (Durham, Leeds, etc. listed in `layout.blade.php $regions`) go live.

5. **External CDN dependencies on a Tailwind-v4/Vite project.** Both the site layout and `customer/layout` load Tailwind via `https://cdn.tailwindcss.com` + Alpine + Google Fonts + Google Translate, and home loads markerclusterer from unpkg; the sticker loads qrcodejs from jsdelivr. The project's stated stack is compiled Tailwind v4 via `npm run build`, so these runtime CDN scripts are a duplication/inconsistency (and a perf/offline risk), not the built pipeline.

6. **`/app?as=business` query param.** Marketing repeatedly links to `/app?as=business` ("See the business view"). Worth confirming PortalController@mobile actually honours `as=business` (not verified here - lives in surface #2).

7. **Region/language UI is partly scaffolding.** The globe selector lists 6 languages (only English live; rest via Google Translate cookie) and 8 cities (only Newcastle `live=true`, others non-clickable "Soon"). Honest but clearly pre-built ahead of capability.
