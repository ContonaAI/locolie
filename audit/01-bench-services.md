# Benchmark: Local-services / review leaders vs GoLocal

Research date 2026-06-24. Sites studied: Thumbtack, TaskRabbit, Checkatrade, Yelp.
Each pattern is grounded in GoLocal's actual files (routes/web.php, resources/views/*) so suggestions land on a real page/route. GoLocal today is offer-and-footfall led (redeem at the till via QR), not a quote/lead marketplace, so where a "lead flow" is suggested it is an additive layer, not a pivot.

## What GoLocal has today (baseline I read)

- Public listing `resources/views/site/business.blade.php` (route `site.business`, `/shop/{slug}`): hero photo, breadcrumb, category chip, a "Sponsored" pill shown only when `plan !== 'free'`, star rating + `reviews_count`, a single CTA "Open in the app", live offers, Google-sourced reviews block ("Reviews · via Google"), a "Visit / Get directions" sidebar and an "Is this your shop? Claim it free" box.
- Category grid `resources/views/site/category.blade.php` (`site.category`): cards with photo, "Sponsored" badge, offer badge, star + postcode, single offer line. No sort/filter controls. Cards link straight to `/app?b={slug}`.
- Business dashboard `resources/views/business/dashboard.blade.php` (`business.dashboard`): KPI tiles (Active offers / Redeemed / Pending / Rating), listing edit (description, phone, website), offers list, a "Your customers" first-party table with CSV export + quick email + Messaging Studio, and a 3-tier plan switcher (free / featured / premium).
- Pricing `resources/views/site/_pricing.blade.php` reads `App\Models\Business::PLANS`; "featured" is hardcoded "Most popular".

The gaps the four leaders expose: no trust/verification layer, no review collection of GoLocal's own (only mirrored Google), no contact/lead capture on the public page, no sort/filter or ranking signal beyond a binary "Sponsored", and onboarding is a bare email/password login with no guided profile completion.

---

## Thumbtack

1. Thumbtack puts a free background-check badge on profiles ("Passing the background check gives you a badge on your profile. Many customers prefer working with verified pros") -> GoLocal should add a verification layer to `site.business.blade.php` next to the category chip: an "Independent verified" badge set when an admin confirms the business is a real local indie (toggle already exists at `admin.onboard`, `/admin/business/{business}/onboard`). Render it as a green shield beside the rating on line ~35, distinct from the paid "Sponsored" pill so trust is not pay-to-win.

2. Thumbtack ranks and tiers pros by a points/rating system (Top Pro: maintain 4.7 stars, earn points per responded lead) and surfaces it as a badge in search -> GoLocal should replace the binary `plan !== 'free'` "Sponsored" ordering with a transparent composite signal on `site.category.blade.php`: sort cards by (verified, then rating, then offer freshness), and reserve a small "Local favourite" badge for businesses above a rating threshold with recent redemptions. This keeps the category grid honest rather than purely paid placement.

3. Thumbtack's lead pricing is "you pay per lead, you set your lead price, your cost is predictable" -> GoLocal should NOT copy pay-per-lead (wrong model for footfall), but should borrow the predictability framing for the plan switcher in `dashboard.blade.php`: show each plan's outcome in concrete units ("featured = top of your category grid + 1 monthly email blast") rather than vague perks, since the current `_pricing.blade.php` blurbs ("Stand out in the feed and search") are abstract.

4. Thumbtack onboarding is guided: set job preferences, add profile photo, intro, business name/year started/team size, then "you can start meeting customers" -> GoLocal's `business.dashboard` should add a profile-completion checklist card (photo, description, phone, website, first offer) with a percentage bar, since the dashboard currently dumps a description/phone/website form with no nudge. Gate the "View in app" share button's prominence on completion to drive quality listings.

## TaskRabbit

5. TaskRabbit's Elite badge is earned, transparent and metro-relative: "Positive Rating of 98%+, highly active, no guideline violations, top 35% of Taskers in their metro with that skill" -> GoLocal should define a "Top in Newcastle NE1" badge that is geo + category scoped (the site is already Newcastle-NE1 framed across `category.blade.php` and `business.blade.php` titles), awarded on rating + redemption volume, shown on both the category card and the listing hero. Earned-and-local reads as more trustworthy than "Sponsored".

6. TaskRabbit lets clients filter by "task date" and opt into same-day jobs; clients "book and pay through the platform rather than off-app" -> GoLocal should add lightweight filter chips to `category.blade.php` (currently zero controls): "Open now", "Has a live offer", "Top rated". "Has a live offer" is already derivable from `$b->activeOffers`, so this is a cheap, high-value addition that turns the static grid into a shopping tool.

7. TaskRabbit shows a self-set hourly rate on every profile so customers price before contacting -> GoLocal's equivalent transparency unit is the offer/saving. The listing page should surface a "typical saving here" or "members saved £X this month" stat in the `business.blade.php` sidebar (near the existing "Visit" card), reusing redemption data the dashboard already counts (`redeemed_count`). This makes the value legible before the app handoff.

## Checkatrade

8. Checkatrade runs "up to 12 checks before a profile goes live" (photo ID, insurance, qualifications) and states it plainly; if a trade lacks public-liability insurance "this will be clearly noted on your profile so homeowners can make informed decisions" -> GoLocal should add a compact "How we vet" trust strip to `business.blade.php` listing one or two real, honest checks it actually performs (e.g. "Independent, locally owned - confirmed by our team" via the `admin.onboard` flow). Do not invent checks GoLocal does not run; honesty about what is and is not verified is the Checkatrade lesson.

9. Checkatrade verifies reviews come from real customers and blocks fake/incentivised ones, and trades who commission fakes can be removed -> GoLocal currently only mirrors Google reviews ("Reviews · via Google" in `business.blade.php` line ~71). It should collect its OWN verified-redemption reviews: after a QR redeem (`qr.redirect`, `/c/{token}`), prompt the shopper in-app to rate, then label those on the listing as "Verified - left after redeeming an offer". A review you can only leave after a real in-store redemption is structurally hard to fake and is GoLocal's defensible trust moat.

10. Checkatrade backs bookings with "The Checkatrade Guarantee" (discretionary, up to GBP 1,000) as a headline reassurance -> GoLocal's analogous low-cost promise is offer integrity: a "Offer honoured or we'll sort it" line near the offer block in `business.blade.php`, backed by the existing customer-support / messaging plumbing. Frames the platform as standing behind the deal, which lifts redemption confidence.

11. Checkatrade lets the verified badge be reused on the trade's own marketing materials -> GoLocal already mints window stickers (`qr.sticker`, `/s/{secret}`). Extend that printable to carry a "Verified independent on locolie" badge/wordmark, so the offline sticker doubles as a trust mark and a funnel back to the app.

## Yelp

12. Yelp's blue shield "Verified License" badge sits next to the business name; "73% of users say they're more likely to choose a business that has a Verified License badge" and buyers saw +10% calls/clicks -> reinforces suggestions 1 and 8: put the verification badge adjacent to the `<h1>` business name on line ~34 of `business.blade.php`, not buried in the sidebar. Placement next to the name is where users look for trust.

13. Yelp's "Request a Quote" shows each business's average response time and highlights the fastest responders in green ("Businesses with a response time of less than a day see 4x more requests") -> GoLocal should add a "Message this shop" action on `business.blade.php` (currently the only CTA is "Open in the app") that routes into the existing per-business messaging (`business.messaging`), and surface a "Usually replies within X" line. This gives indies a reason to keep their inbox warm and gives shoppers a contact path that is not just an app handoff.

14. Yelp sorts reviews by a "Recommended" algorithm weighting recency, and de-ranks stale pages ("If your page hasn't been touched in months ... Yelp may consider your business less relevant") -> GoLocal should weight category-grid ordering toward recency of last offer/redemption, and show a subtle "Updated this week" cue on `category.blade.php` cards. This rewards the active indies the platform wants to keep and quietly penalises dormant listings.

15. Yelp coaches owners to fully complete service area + service offerings to "maximise the number of relevant messages" -> ties to suggestion 4: the `business.dashboard` completion checklist should explicitly list services/categories and area, because a fuller listing both ranks better and converts more contacts.

---

## Visual / brand directions that feel fresh and trustworthy

1. Earned-not-bought trust marks. Yelp's blue shield, Checkatrade's tick, TaskRabbit's Elite badge and Thumbtack's verified badge all read as credentials, not ads. GoLocal currently leans on a single amber "Sponsored" pill, which signals paid. Introduce a clearly different visual language for trust (green shield / tick) vs promotion (amber "Sponsored"), so users can tell verification from advertising at a glance.

2. Response-time and freshness cues as live signals. Yelp's green "fast responder" highlight and "Updated this week" feel alive and human. GoLocal's grid is static; small live cues (green dot "Open now", "Verified review left 2 days ago") make a directory feel current and trustworthy without heavy design cost.

3. Honest, plain-spoken vetting copy. Checkatrade and Thumbtack win trust by stating exactly what they check in plain language (and what they do not). GoLocal's existing voice is already warm and direct ("Skip the chains and back your high street", "Is this your shop? Claim it free") - extend that into a short, honest "how we vet local indies" panel rather than vague trust-washing.

4. Tiering that looks like status, not a paywall. TaskRabbit Elite and Thumbtack Top Pro present paid/earned standing as an aspirational badge with a clear bar to clear. GoLocal's `_pricing.blade.php` "Most popular" pill is generic SaaS. Reframe the featured/premium tiers around a visible local-status badge the business earns/displays, so upgrading feels like joining a recognised local cohort rather than just paying for placement.

Sources: Thumbtack Help (how-thumbtack-works, pay-for-leads, signing-up-as-a-professional, background-checks, profile-guide); Taskrabbit Support (Elite Status Overview, What's Required to Become an Elite Tasker); Checkatrade (checkatrade-how-we-work, checkatrade-approved, review-guidelines-for-consumers, guarantee-terms); Yelp for Business (Verified License badge support, Request a Quote blog, respond-to-a-quote-request).
