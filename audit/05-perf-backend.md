# Audit 05 - Backend performance & code quality

Scope: every controller (`app/Http/Controllers/**` incl. `Api/*` and `Messaging/*`), the
`app/Models/*`, `app/Services/*`, and `database/migrations/*`. Grounded against
`00-map-app-api.md` (API + owner-secret findings) and `00-map-business.md` (CRM findings) -
this file adds the query/index/code-quality layer and does not repeat those security
write-ups except where the DB is implicated.

Severity key: P0 = launch blocker / abuse or data risk now. P1 = will hurt at modest scale or
real money. P2 = cleanup / correctness polish.

---

## P0 - blockers

### P0-1. No rate limiting anywhere on the API; `placesSearch` is an open billable Google proxy
`routes/api.php` has zero `throttle` middleware (confirmed: `grep throttle routes/ bootstrap/`
returns nothing). Every endpoint is wide open and unlimited. The sharpest one:
`GET /api/places/search` (`BusinessController@placesSearch`, line 19) validates only
`q: min:2,max:120` then calls `$this->places->search()` straight through to the Google Places
API. `PortalController@adminProspectSearch` (line 151) does the same. Anyone can loop this and
run up a Google bill, and there is no auth in front of it (see `00-map-app-api.md` section 6).
- Fix (low risk): wrap `routes/api.php` in `Route::middleware('throttle:api')` (or a custom
  limiter), and put a tighter limiter (e.g. `throttle:20,1`) specifically on `places/search`,
  `/businesses` (POST), and `/offers/{offer}/redeem`. Add `throttle:5,1` on
  `POST /business/login` and `POST /login` (`routes/web.php:46,78`) too - no brute-force guard
  there today.

### P0-2. `POST /api/offers/{offer}/redeem` can oversell a limited offer (race condition)
`RedemptionController@redeem` checks `abort_if($offer->isSoldOut(), 422)` (line 29), but the
stock counter (`offers.redeemed_count`) is only incremented later, at till `verify`
(`RedemptionService::verify` line 61-63). `isSoldOut()` (`Offer.php:31`) compares
`redeemed_count >= quantity`. So N concurrent reveals all pass the sold-out gate before any
verify happens, and `Offer::scopeActive`'s `redeemed_count < quantity` check has the same
TOCTOU gap. There is no DB-level guard (no unique constraint, no atomic decrement). For a
"limited to 20" offer this hands out unbounded codes.
- Fix (moderate risk - touches the redemption hot path): decide what "quantity" gates -
  reveals or verifies. If reveals: do the count + increment inside a transaction with
  `lockForUpdate()` or an atomic conditional `UPDATE offers SET redeemed_count = redeemed_count
  + 1 WHERE id = ? AND (quantity IS NULL OR redeemed_count < quantity)` and treat 0 affected
  rows as sold out. Flag: test the countdown/wallet flow after, since `remaining()` and the
  app's live counter read this field.

### P0-3. `owner_secret` and `qr_token` are only `Str::random()`, used as bearer credentials in URLs
Covered for the URL-logging angle in `00-map-app-api.md` 6.1; the DB/model angle: both are
generated with `Str::random(40)` / `Str::random(32)` in `Business::booted()`
(`Business.php:83-88`). `Str::random` is cryptographically secure (good), but the secret is the
*entire* auth for every retailer write endpoint and the printable sticker
(`PortalController@sticker` line 227 takes it as a URL segment). `resolve()`
(`BusinessController.php:163`) and `verify` (`RedemptionController.php:74`) both do a plaintext
`where('owner_secret', $secret)` lookup. There is no hashing at rest and no rotation path.
- Fix (risky to do unattended - it is the whole retailer auth model): move to header/bearer or
  Sanctum per the API docblock's own TODO. At minimum stop putting it in URL paths. Do NOT
  silently rotate existing secrets - stickers and `localStorage gl_secret` are already in the
  wild.

---

## P1 - performance & integrity

### P1-1. Missing indexes on every hot filter column
The migrations index `slug`, `qr_token`, `owner_secret`, `google_place_id`, `code`, FKs, and
`city` - but NOT the columns the live queries actually filter and sort on:
- `businesses.status`, `businesses.onboarded`, `businesses.featured`, `businesses.priority`,
  `businesses.plan`. `scopeLive` (`Business.php:66`) = `where status=active AND onboarded=true`
  and `scopeRanked` orders by `priority,featured,rating`. Every public list
  (`SiteController@home/category/business`, `BrowseController@businesses`) and every admin
  count (`PortalController@admin` lines 79-91, six separate `count()` queries) hits these
  unindexed. (`create_businesses_table` line 29 `status` has no index;
  `add_plans_onboarding_and_push` lines 13-17 add `plan/priority/onboarded` with no index.)
- `offers.status` - `scopeActive` and `activeOffers` filter it; no index
  (`create_offers_table` line 23, `add_offer_lifecycle_and_featured`).
- `redemptions.customer_email`, `redemptions.customer_phone`, `redemptions.sms_opt_in`,
  `redemptions.status` - the entire reporting + customer-capture path filters/groups on these.
  `customer_email` is added in `add_customer_capture_to_redemptions` line 13 with no index;
  `CustomerReportController` and `ReportingService::forCustomer` do
  `where('customer_email', $email)` (full table scan).
- Fix (low risk): one additive migration. Composite `['status','onboarded','priority']` on
  businesses, `['business_id','status']` on offers, `index('status')` +
  `index('customer_email')` + `index('customer_phone')` on redemptions. Pure index adds, no
  data change.

### P1-2. ReportingService loads whole tables into PHP and counts there
`ReportingService` is the heaviest code in the app and does almost all aggregation in-memory:
- `platform()` (line 255): `Redemption::with('offer')->get()` - **every redemption row ever**,
  then `->where(...)->count()`, `pluck->unique`, `sum(fn...)` in PHP. Same for `Campaign::all()`.
  Grows unbounded; this is the `/reports` team page.
- `forBusiness()` (line 76): `Redemption::with('offer')->whereIn('offer_id',$offerIds)->get()`
  with no date filter at all - `$days` is only applied afterwards in PHP (`$since` line 81),
  so the query pulls the brand's entire redemption history regardless of the 7/14/30/90 range.
- `topOffers()` (line 132) calls `$business->offers()->get()` a SECOND time (the offer set was
  already available), and inside the map does `$redemptions->where('offer_id',$o->id)` per offer.
- `forCustomer()` (line 205): `with('offer.business')->where('customer_email',$email)->get()`
  then `groupBy(fn($b)=>$b->category?->name)` - `category` is NOT eager-loaded, so this is an
  N+1 across the customer's distinct businesses (one `categories` query per business).
- Fix (moderate risk - reporting math is subtle, has honest-estimate semantics): push the date
  window into the query (`whereBetween('created_at', ...)`), eager-load `offer.business.category`
  in `forCustomer`, reuse the already-fetched offer collection in `topOffers`, and convert the
  platform KPIs to `count()`/`sum()` SQL aggregates. Verify the numbers match before/after.

### P1-3. `BusinessPortalController` re-queries all redemptions 3x per dashboard load
`redemptionsFor()` (line 78) runs `Redemption::whereHas('offer',...)->get()` and is called from
`dashboard()` -> once directly + once inside `customersFor()` (line 86), and
`smsCustomersFor()` calls it again. The dashboard therefore runs the same full redemption
fetch multiple times, and `customersFor`/`smsCustomersFor` group in PHP. `forBusiness` reports
page does its own separate full fetch.
- Fix (low risk): fetch once in `dashboard()` and pass the collection into `customersFor`, or
  memoize on the instance. Same pattern as P1-2.

### P1-4. `BrowseController@businesses` sorts in PHP after `ranked()->get()`
Line 70-72: the query already applies `ranked()` (SQL `ORDER BY priority,featured,rating`), then
the result is re-sorted in PHP by `[$b->priority, $b->activeOffers->count(), rating]`. The
double sort is wasteful and the PHP sort is the one that wins, making the SQL `ranked()` ordering
dead work. `$b->activeOffers->count()` is fine here (eager-loaded line 46) but the whole live
set is materialised before sorting - no pagination/limit on the public browse endpoint.
- Fix (low risk): either sort fully in SQL with a `withCount('activeOffers')` + `orderBy`, or
  drop the redundant `ranked()`. Add a `limit`/pagination - today `/api/businesses` returns the
  entire live catalogue with full `present()` payloads.

### P1-5. `present()` payload ships fabricated data and is large
`BrowseController@present` (line 94) is the public list shape. `full:true` (single-business and
by-token) emits `reviews` that are *synthesised* when none exist (`reviews()` line 141) and
every row carries `fakeDistance()` (line 165). Already flagged for honesty in
`00-map-app-api.md` 8; the perf angle is that `present()` is mapped over the entire unpaginated
live set (P1-4) including `hours`/`photos` JSON. At scale this is a heavy uncached JSON blob on
the busiest endpoint.
- Fix: paginate (P1-4), and gate the sample reviews/distance behind an explicit
  `?demo=1` rather than baking them into the prod shape.

### P1-6. `categoryAndDescendantIds` recurses with a query per node
`SiteController@categoryAndDescendantIds` (line 66) runs
`Category::where('parent_id',$id)->get()` once per category in the tree (recursive). For the
3-level tree that is a handful of queries per category landing page, every request, uncached.
- Fix (low risk): the tree is tiny and static - load all categories once and build the
  descendant set in memory, or cache it (`Cache::remember`). Categories change rarely.

---

## P2 - cleanup & correctness

### P2-1. Two divergent brand-save methods, duplicated verbatim
`MessagingController@saveBrand` (lines 45-67) and `BusinessPortalController@saveBrand`
(lines 171-193) are byte-for-byte the same validation + colour-normalise + logo-store +
`array_filter` update logic, differing only in how they resolve `$business`. Same for the
`smsCustomersFor`/`customersFor` grouping logic duplicated between `BusinessPortalController`
and `ReportingService`.
- Fix (low risk): extract a `BrandIdentity` trait/action and a `CustomerCapture` query object.

### P2-2. `customersFor`/`audienceFor` opt-in semantics are inconsistent
`BusinessPortalController::customersFor` (line 93) derives opt-in via
`$g->max('marketing_opt_in')`. `EmailStudioController::audienceFor` (line 214) filters
`where('marketing_opt_in', true)` per-row then `unique('customer_email')` - so a customer who
opted out on a later redemption is treated differently by the two send paths. Combined with the
two email entry points (dashboard stub vs studio, per `00-map-business.md` 3) the "who gets
emailed" answer depends on which button you press.
- Fix: single source of truth for "opted-in customers of a brand".

### P2-3. Fat admin controller method
`PortalController@admin` (line 74-100) fires ~14 separate aggregate queries inline in the
controller (8 `stats` counts, 3 `planCounts`, plus the lists). Readable but unindexed (P1-1)
and untestable.
- Fix: move to a small `AdminStats` service; collapse the plan counts into one
  `groupBy('plan')->selectRaw('plan,count(*)')`.

### P2-4. `updateListing` validates `category_id` that no UI submits
`BusinessPortalController@updateListing` (line 315) validates+saves `category_id`, but the
dashboard form has no such field (confirmed in `00-map-business.md` 2). Dead validation rule.
- Fix: remove it or add the field.

### P2-5. `Business::uniqueSlug` is a query-in-a-loop on signup
`Business.php:97` loops `where('slug',$slug)->exists()` incrementing a suffix. Fine at current
volume; on a name collision storm it is O(n) queries. Low priority - signup is not hot.

### P2-6. Mass-assignment surface is wide; `priority`/`featured`/`plan` are fillable
`Business` `#[Fillable]` (line 13-19) includes `priority`, `featured`, `plan`, `owner_secret`,
`password`, `onboarded`. Nothing in the audited controllers mass-assigns request input straight
into these for businesses (each plan change goes through `applyPlan`/`adminSetPlan` with a
validated `Rule::in`), so no live exploit, but `owner_secret`/`password` being fillable is a
foot-gun if a future endpoint does `Business::create($request->all())`. (They ARE in
`#[Hidden]`, so not leaked in serialisation - good.)
- Fix (low risk): drop `owner_secret`, `password`, `priority`, `featured` from `$fillable` and
  set them explicitly where needed (they already are).

### P2-7. `uniqueCode()` collision check ignores hashing/index but is fine
`RedemptionService::uniqueCode` (line 69) loops on a 6-digit space (1M) filtered to live
pending codes. `redemptions.code` is indexed (`create_redemptions_table:16`) so the lookup is
cheap; collision probability is negligible at prototype scale. No action - noted as not-a-bug.

### P2-8. `SiteController@home` runs ~6 catalogue scans per homepage hit
`home()` (line 11) issues `live()->count()`, `leaves()->count()`, active-offer count,
`categoriesWithCounts()` (withCount subquery), featured `take(6)`, `cityData` groupBy, and
`mapPoints` (`Business::whereNotNull('lat')->get(['lat','lng',...])` - the full point set, no
limit). All uncached on the marketing homepage.
- Fix (low risk): `Cache::remember(..., 300, ...)` the stats/cityData/mapPoints block; they
  change slowly. Pairs with P1-1 indexes.

### P2-9. `mapPoints` leaks every business's coordinates publicly, including non-onboarded
`home()` line 32: `Business::whereNotNull('lat')->get(['lat','lng','onboarded','city'])` - this
includes *leads* (onboarded=false, the Google-Maps-prospected businesses from
`adminAddProspect`) and ships their coordinates to the public homepage JS. Minor data exposure
of the prospecting pipeline.
- Fix: scope to `live()` (or `onboarded`) before exposing points.

---

## Quick wins (do first, all low risk)
1. Add `throttle` middleware to `routes/api.php` + login routes (P0-1).
2. One additive index migration (P1-1).
3. Cache the homepage stats block (P2-8) and category descendant tree (P1-6).
4. Fetch redemptions once per dashboard/report (P1-3) and eager-load `offer.business.category`
   in `forCustomer` (P1-2 N+1).
