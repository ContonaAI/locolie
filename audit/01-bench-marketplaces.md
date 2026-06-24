# Benchmark: Marketplace leaders -> GoLocal (locolie)

Researched Airbnb, Booking.com and Trivago for reusable search/card/map/trust/CTA/onboarding/empty-state/mobile-nav patterns. Each item is framed as "Site does PATTERN for PURPOSE -> GoLocal should adapt it as CONCRETE SUGGESTION (page/route)". File paths and route names below are real and were read; copy in `quotes` is real (either already in our code, or from the cited source).

GoLocal surfaces this touches:
- Consumer SPA: `resources/views/portal/home.blade.php`, served at `/app` and `/` via `PortalController@mobile` / `@home`. Tabs: Home, Map, Saved, plus search/profile/code views.
- Marketing/SEO pages: `resources/views/site/category.blade.php` (`/category/{slug}`, `site.category`) and `resources/views/site/business.blade.php` (`/shop/{slug}`, `site.business`).

Sources are listed at the end.

---

## 1. Search and filter UX

**Airbnb shows a histogram-style price-range slider** so users see where listings cluster before they drag, and **surfaces "Recommended filters"** personalised to prior searches -> GoLocal should adapt the histogram idea to its **Offer filter**, which today is a flat 4-button choice (`fOffer===10/25/50`, "10%+ off / 25%+ off / 50%+ off" in `home.blade.php` ~line 437). Show a tiny bar of how many live offers fall in each discount band so the choice is informed, not blind. Page: filter sheet in `/app` (`filterOpen` sheet, `home.blade.php` lines ~406-458).

**Trivago puts a sort dropdown on the results page** with `'our recommendations'`, `'rating & recommended'`, `'price & recommended'`, `'distance & recommended'` -> GoLocal's category list view (`x-for="b in visible"`, `home.blade.php` ~line 524) has NO sort control at all; results just render in default order. Add a single sort toggle (Recommended / Nearest / Biggest discount / Top rated) above the list. Distance (`dist(b)`) and rating (`b.rating`) are already computed, so this is a client-side `.sort()`. Page: Home tab category view, `/app`.

**Airbnb's filter sheet groups dozens of options under clear headings with chips/sliders** to tame complexity -> GoLocal already does the right thing here (filter sheet has `fgroup` headers "Rating", "Offer", "Sale type" plus Distance, with a count badge `filterCount` on the trigger and a "Clear"/"Apply" footer). Keep this. The one gap vs Airbnb: the applied filters are invisible once the sheet closes. Add removable filter chips under the search bar (e.g. a row of pills "4.5+ rating x", "25%+ off x") so state is visible and one-tap removable. Page: header area below `.searchbar` in `/app`.

**Booking.com / Airbnb both lead with a single prominent search field**; GoLocal's `.search-input` is a tap target reading `Search shops & offers` that opens a dedicated search view with a "Browse categories" fallback before any query (`home.blade.php` ~line 394, 575-583). This is solid. Improvement: the empty search state should show **recent searches and trending offers**, not only category pills, mirroring Airbnb autocomplete. Page: search view (`view==='search'`), `/app`.

---

## 2. Listing / result cards

**Booking.com cards stack a review-score chip, a one-word label, and a deal badge** (`8.4/10`, `Very good`, scarcity flag) so the card is scannable in under a second -> GoLocal cards are close but inconsistent across surfaces. The app home `.hcard` shows `★ rating · category` and an offer badge; the category-list `.row` shows `★ rating · category · X mi · Open`; the marketing card in `category.blade.php` (line 48) shows `★ {rating} · {postcode}` with NO review count and NO open/closed. Standardise one card "trust line": `★ rating (N reviews) · distance · Open/Closed`. The review count exists (`b.reviews_count`) and `isOpen(b.hours)` exists in the app but is absent from the marketing cards. Pages: `home.blade.php` `.hcard`/`.row`, and `site/category.blade.php` line 36-51.

**Airbnb uses contextual card actions** (share vs heart depending on listing type) and **a heart/wishlist on every card** -> GoLocal already has a heart, but only on the open profile (`.hb` toggle, `home.blade.php` ~line 600), not on the cards in the feed. Add the heart to `.hcard`/`.row` so users can save without opening. `toggleFav`/`isFav`/`favs` already exist. Page: Home feed cards, `/app`.

**All three reserve a clear "ad/sponsored" treatment distinct from organic results** -> GoLocal already labels paid placement honestly: `.spon-tag` "Sponsored" on featured hcards, a full `.ad-banner` with `Ad · Google` eyebrow, and `category.blade.php` line 43 puts a `Sponsored` pill on non-free listings. Keep this; it is good practice and matches the leaders. No change needed beyond keeping the label visible on every paid surface.

---

## 3. Map + list layout

**Airbnb runs a sticky split where the map and the result list stay in sync** (pan the map, results refilter; hover a card, the pin lifts) and **map pins show price not a generic dot** so the map itself is a comparison surface -> GoLocal currently separates them into two tabs (Home list vs a Map tab, `tab==='map'`, `home.blade.php` ~line 587) and the map has category pills + a `mapRefresh()` but no synced list. The pins already render a rich `.map-pop` (name, category, offer, a "view" button) and price/offer cards on zoom. Two adaptations: (a) on the Map tab add a bottom sheet list of the same visible businesses so users get map+list without tab-switching; (b) tie the Home category filter to `mapRefresh()` (already partly wired via shared `cCat`). Page: Map tab, `/app`.

**Trivago/Airbnb let the map drive "search this area"** -> GoLocal's map has no "redo search in this area" affordance. Add a floating "Search this area" button that re-queries on pan, reusing `mapRefresh()`. Page: Map tab `#cmap`, `/app`.

---

## 4. Trust signals (reviews, ratings, verification)

**Airbnb withholds an average rating until there are at least 3 reviews** to avoid a misleading "5.0 (1)" -> GoLocal shows `number_format($rating,1)` everywhere regardless of count (`category.blade.php` line 48, `business.blade.php` line 36, app `.row`/profile). Adapt: when `reviews_count < 3`, show "New" or "Few reviews" instead of a star number. Pages: `site/category.blade.php`, `site/business.blade.php`, `home.blade.php` rating spans.

**Booking.com pairs the numeric score with a word label** (`8.4/10` -> `Very good`) for instant read -> GoLocal shows only the star number. Add a one-word label derived from rating (e.g. 4.5+ "Excellent", 4.0+ "Very good") next to the star. Pages: same rating spans as above.

**Airbnb / Booking surface review provenance and verification** -> GoLocal already attributes reviews honestly: `Reviews · via Google` appears in both `business.blade.php` line 71 and the app profile (`home.blade.php` review block), and `business.blade.php` emits LocalBusiness + AggregateRating JSON-LD (lines 8-10) for SEO trust. Strengthen by adding a small "Verified independent" / "Claimed by owner" badge for onboarded businesses (we track `onboarded` in `PortalController@admin`). This is the local-shop analogue of Airbnb's verification and would differentiate GoLocal's "back the indies" positioning. Pages: `site/business.blade.php` header (line 27-44), app profile header.

**Booking.com uses real-time scarcity / social proof** (`Only 1 room left on our site!`, `12 people are looking at this property right now`, `Booked 60 times in the last hour`) to drive action honestly when data is real -> GoLocal already has a truthful, non-dark-pattern version: limited offers render an `otag` "X left" that turns `.hot` when `remaining<=5`, "Sold out" at 0, and seasonal offers show "Ends soon" (app profile offer block, `home.blade.php` ~line 545-552). This is the right restrained adaptation. Extend it to the cards (show "Only N left" on the offer pill) and to `business.blade.php` "Live offers" (line 54-66), which currently shows no remaining-count urgency. Keep it strictly truthful (real `remaining`), not invented.

---

## 5. Booking / contact CTA flow

**Booking/Airbnb keep ONE dominant primary CTA per screen, sticky, with the action verb stating the outcome** ("Reserve", "Request to book") -> GoLocal's redeem flow is strong here: the app profile has a full-width `.cta-btn` reading `Reveal code`, it disables to `Sold out` when `remaining<=0`, and on success routes to a `view==='code'` screen with `You're in.` and `Show this QR at the till for staff to scan - or read out the code.` (`home.blade.php` ~line 555, 609-616). This is a clean, outcome-stating CTA. Keep it.

**The marketing detail page CTA is weaker** -> `site/business.blade.php` line 41's primary action is `Open in the app` (opens `/app?b={slug}` in a new tab). That is an extra hop before the user can act. Adapt: make the live-offer rows themselves the CTA (each offer row deep-links to `/app?b={slug}` and auto-opens that offer), so the page's strongest element (the discount) is the click target, matching how Airbnb makes the whole card actionable. Page: `site/business.blade.php` "Live offers" block (line 54-66).

**Airbnb provides secondary contact/quick-actions without leaving the page** -> GoLocal's app profile already nails this with a `.qa` row of Call / Directions / Share (`home.blade.php` ~line 540) wired to `callBiz()`, `directions()`, `share()`. The marketing `business.blade.php` only offers "Get directions" in the sidebar (line 89). Add Call and Share there too for parity, since phone/share are exactly what local-shop visitors want. Page: `site/business.blade.php` sidebar (line 86-90).

---

## 6. Onboarding

**Airbnb's "Airbnb your home" path is a single clear supply-side entry point** that turns a viewer into a host -> GoLocal already mirrors this with the "Is this your shop? Claim it free" card on every business page (`business.blade.php` line 91-95: `Claim your free listing, post offers that bring in real footfall...`) linking to `/for-business`. Keep and make it appear on category pages too (`category.blade.php` already has a softer version only in the empty state, line 57). Add the claim prompt as a persistent footer on populated category pages, not just empty ones. Page: `site/category.blade.php`.

**Booking/Airbnb capture the consumer with a low-friction email-for-deals ask** -> GoLocal's shopper login already does this: `Your email (for offers near you)` (`home.blade.php` line 347). Good. The one improvement: let users browse the feed before being asked (Airbnb lets you search before sign-up). Confirm the feed is reachable pre-auth in the `solo`/`soloRole==='shopper'` path and gate only redemption behind email. Page: `/app` login vs feed (`PortalController@mobile`).

---

## 7. Empty states

**Airbnb empty states suggest a next action** (broaden dates, clear filters, view nearby) rather than a dead end -> GoLocal's empty states are currently bare strings: `No offers match here.` (line 528), `No results.` (line 583), and `No offers match here.` again in filtered views. The Saved tab does this well: `Tap the heart on any shop to save it.` (line 595) gives an action. Adapt the search/filter empties to match: add a "Clear filters" button (the sheet already has `filterCount` and a clear handler) and a "Browse all nearby" link. The marketing category empty (`category.blade.php` line 55-58) already does this right: `We are adding new local spots every week, so pop back soon.` + a "Be the first to list it, free" CTA. Pages: app Home filtered view, search view (`home.blade.php`).

---

## 8. Mobile navigation

**Airbnb/Booking apps use a fixed bottom tab bar with 3-5 destinations and an icon+label per tab** -> GoLocal already implements this: a glass `.tabbar` fixed at the bottom (`.solo .tabbar` is `position:fixed`, respects `env(safe-area-inset-bottom)`) with icon-over-label `.tab`s that highlight `.on` in the accent colour (`home.blade.php` lines 185-190, 303). Tabs are Home / Map / Saved (+ profile/code/search as overlay views). This matches the leaders well. Two refinements: (a) Booking/Airbnb keep search persistent in a sticky header on scroll, which GoLocal already does (`.app-header` sticky, search bar inside) -- keep it; (b) consider a "Wallet"/"My codes" tab so redeemed offers (the `view==='code'` ticket) have a permanent home rather than being a transient screen, mirroring how Booking surfaces "Trips/Bookings". Page: `.tabbar` in `/app`.

---

## Sources

- Airbnb search/filter/card patterns: https://baymard.com/ux-benchmark/case-studies/airbnb ; https://medium.com/@yashxagarwal/6-ux-examples-airbnb-got-just-right-83b0f4cb8eb8 ; https://medium.com/@meredithameller/ux-exercise-reflecting-on-airbnbs-patterns-and-flows-1d02709291c4
- Booking.com trust/urgency/social-proof: https://swiped.co/file/urgency-from-booking-com/ ; https://octalysisgroup.com/booking-com-conversion-science/ ; https://ralabs.org/blog/booking-ux-best-practices/
- Trivago sort/filter/comparison: https://medium.com/ux-diaries/trivago-redesign-ux-study-546037739173 ; https://support.trivago.com/hc/en-us/sections/360003080094-Searching-on-trivago
