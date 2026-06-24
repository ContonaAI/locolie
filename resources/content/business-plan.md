# locolie - Business Plan

*Working title - name to be finalised. Full name exploration in **Appendix C**.*

**Version:** 0.1 (Draft)
**Date:** June 2026
**Status:** Pre-MVP, bootstrap

---

## 1. Executive Summary

locolie is a free-to-use iOS app that helps consumers discover offers and discounts from independent local businesses, and helps those businesses reach new customers without the cost or complexity of running their own marketing. The platform launches UK-wide on iOS, seeded in 2-3 target towns to prove density before scaling.

The model is two-sided and frictionless: free for consumers, free for businesses to list. Revenue is generated post-traction via sponsored placements, paid email campaigns to the consumer base, and in-app advertising through the Google Display Network.

The opportunity is real. The UK has ~500,000 independent customer-facing businesses, most of whom rely on word-of-mouth and have no effective digital acquisition channel. The existing competitor landscape - chiefly Local Pocket, plus a handful of small US/EU apps - is fragmented, slow-moving, and locally constrained. None has emerged as the dominant UK player.

The product can be built in 3 months on a sub-£5K budget using AI-assisted development (Claude Code), low-code tooling where appropriate, and a phased rollout starting in Wokingham/Reading. The plan below details product scope, go-to-market, competitive positioning, financials, and the realistic risks.

---

## 2. The Problem

**For consumers**, finding good local offers is broken. Google Maps shows businesses but not their current deals. Loyalty cards live in wallets and get lost. Email newsletters from individual shops are unsubscribed. Groupon-style sites are dominated by chains and gimmicky offers. The result: people don't know what's happening on their own high street, and default to chains or online.

**For independent businesses**, customer acquisition is the single hardest problem. Facebook Ads require expertise most don't have. Local print advertising is dying. Google Ads is expensive for the volumes a single café or salon needs. Building their own audience via email or social takes years. Most indies have no functioning marketing channel at all beyond a Google Business listing.

The category - "help people find local deals" - has been attempted many times, but no UK player has cracked the combination of consumer reach, business self-serve onboarding, and a redemption mechanic that gives merchants real data.

---

## 3. The Solution

locolie is an iOS app and accompanying business web portal that lets:

**Consumers** browse a map and category-filtered list of independent local businesses near them, see live offers and discounts, save favourites, get push notifications about new deals nearby, and redeem offers in-store via a simple unique-code mechanic.

**Businesses** sign up via a self-serve web portal, claim or create their listing, add photos and opening hours, publish offers (with start/end dates, redemption limits, and discount terms), and see analytics on profile views, offer claims, and redemptions.

**The killer feature - first-party customer data for independents.** Every time a shopper redeems an offer, locolie captures them as the *business's own customer*: name, email, and visit frequency, with marketing opt-in. This is the single biggest unlock for an independent. Chains run loyalty empires (Tesco Clubcard, Greggs, Pret, Costa) precisely *because* they own their customer data and can market back to it. Independents never could - they rent reach from Facebook, Instagram and Deliveroo, paying every time to reach customers they already served. locolie hands them a **customer list they own and can export**, plus the channels (email + push) to bring those customers back. That moves locolie from "another offers app" to **the independent's CRM and marketing engine** - a far stickier, higher-value position, and the foundation of our monetisation (see §8).

The product is deliberately minimal at launch. The wedge is **execution quality** *and* this data advantage: a faster app, cleaner UX, broader coverage, a redemption mechanic that works for both sides - and the customer relationships only we put back in the independent's hands.

---

## 4. Market Opportunity

**UK market size:**
- ~5.5M small businesses in the UK
- ~1.5-2M are micro/independent customer-facing businesses (retail, hospitality, personal services, trades)
- ~500K are clearly addressable: indie retail, hospitality, salons, gyms, cafés, mechanics, plumbers, electricians
- Smartphone-using UK adults: ~50M

**Reachable for v1 (3 launch towns - Wokingham, Reading, +1):**
- ~2,000-3,000 indie businesses
- ~400,000 adults

**Why now:**
- High-street footfall recovery post-pandemic still uneven; indies actively seeking digital channels
- Cost-of-living pressure makes consumers more deal-conscious
- Native app dev costs collapsed thanks to AI-assisted tooling - what cost £80K in 2022 can now be built for under £5K in dev costs
- No clear UK winner in the category despite multiple attempts

---

## 5. Product

### 5.1 Consumer iOS app - MVP feature set

**Core**
- Map view: pins for businesses with active offers, filterable by category and distance
- List view: businesses sorted by proximity, with offer preview
- Business profile: photos, description, address, opening hours, contact, current offers, directions (deep-link to Apple/Google Maps)
- Offer detail: discount terms, expiry, redemption instructions
- Search: by business name, category, or offer keyword
- User profile: saved favourites, redemption history, notification preferences
- Push notifications: new offers within X km of home location, favourites posting new offers
- Onboarding: location permission, category preferences (food/retail/services/trades), home postcode

**Redemption mechanic (recommended)**
- User taps "Redeem" → app reveals a unique 6-digit code + the offer details
- Code is valid for 10 minutes
- Staff types the code into the business portal (or the business app, if built later) to mark redeemed
- Merchant sees redemption logged in real-time analytics
- This pattern gives **proof of redemption** without requiring QR scanners, while letting the business verify validity before honouring the discount

This is the gap Local Pocket leaves open. A merchant who can't verify whether an offer was actually claimed via the app won't trust the platform.

### 5.2 Business web portal - MVP feature set

- Sign up (email + verification)
- Business profile setup: name, category, address (geocoded), photos, opening hours, contact details
- Offer creation: title, description, discount type (% off / £ off / BOGO / free item), expiry date, redemption limit (total or per user)
- Redemption verification: enter user's 6-digit code, see if valid, mark redeemed
- Analytics dashboard: profile views, offer views, redemption count, repeat redemption rate
- Account settings: billing (for future paid tier), users, notification prefs

### 5.3 Out of scope for MVP (Phase 2)
- Android app
- Loyalty cards / stamp cards
- In-app messaging consumer ↔ business
- Booking / appointments
- Native business app (for now, portal is web)
- Multi-location chains
- Payment integration / in-app purchase of offers

---

## 6. Competitive Landscape

### 6.1 Direct competitors

**Local Pocket** (localpocket.com / LOCALPOCKET LIMITED)
- iOS only, free, council-partnered (Waltham Forest)
- 3.0 stars, single rating, listed as "beta under development"
- Free for businesses, no clear monetisation
- Geographically locked to a single London borough by design
- Slow product velocity; minor updates only
- **Verdict:** real but stuck. Easy to leapfrog on geographic ambition and product polish.

**Savzy, Nearwala, Local Deals, Dashible, Geod Services**
- US- and India-focused mostly. Similar concept, none with UK presence.
- Reinforces the thesis: no incumbent owns the UK indie-discount space.

### 6.2 Adjacent / horizontal competitors

- **Groupon, Wowcher** - dominated by chains, salons-on-deal, and gimmicky packages. Not a true indie play.
- **Nextdoor** - community-focused but not a deals app; weak business tooling.
- **Google Maps + Google Business Profile** - the real competitor. Free, used by every consumer, has reviews and basic info. **But** no offers/discounts mechanic, no push notifications about deals, no "what's nearby and on offer right now."
- **Toogoodtogo, Olio** - adjacent (food waste, sharing) but proves consumers will use hyperlocal apps.

### 6.3 Why locolie wins

1. **UK-wide ambition from day one**, no council dependency.
2. **Redemption verification** that gives businesses real data - most competitors stop at "show staff your screen."
3. **Self-serve business onboarding** - businesses sign themselves up in 5 minutes, no sales call.
4. **Sharper product** - fewer features, executed properly, vs. competitors who launched and stagnated.
5. **AI-built, AI-iterated** - the cost structure means locolie can outpace competitors who rely on outsourced dev.

---

## 7. Go-to-Market

### 7.1 Phase 1: Density (Months 1-3)
- Launch app live in App Store (UK-wide)
- Active recruitment of 50-100 businesses in 2 seed towns (Wokingham + Reading)
- Personal outreach: door-to-door, indie business associations, local Chamber of Commerce, BIDs (Business Improvement Districts)
- Free onboarding, hand-hold first 20 businesses through portal
- Seed consumer side via local Facebook groups, Reddit (r/wokingham, r/reading), local press outreach, leaflets at participating businesses

### 7.2 Phase 2: Validate (Months 4-6)
- Hit 200+ active businesses across seed towns
- 2,000+ registered consumers
- 500+ monthly redemptions
- Begin community partnerships: local councils (free pilot in exchange for marketing support), local press, influencers
- Add 2 more towns

### 7.3 Phase 3: Expand (Months 7-12)
- Open self-serve sign-up nationally
- Targeted Facebook/Instagram ads in mid-density towns
- PR push: "free marketing for indie businesses"
- Begin monetisation: featured listings, sponsored emails, in-app display ads via AdMob

### 7.4 Consumer acquisition channels (in order of cost)
1. **Word-of-mouth from participating businesses** - every signed business gets a window sticker, in-store flyer, social post template. Free.
2. **Local Facebook groups and subreddits** - organic posts about new offers. Free.
3. **Local press / hyperlocal media** - free pitch angle: "supporting indie businesses."
4. **Influencer partnerships** - local lifestyle/food creators, paid in offer credit.
5. **Paid Meta ads** - only after density proves the unit economics.

### 7.5 Business acquisition channels
1. **Direct outreach** in seed towns - the founding team, 100+ doors knocked (Joe leading merchant sales, Roddy on partnerships)
2. **BIDs and local Chambers of Commerce** - partnership pitch
3. **Trade associations** - FSB, Federation of Independent Retailers
4. **Existing business inbound** - driven by consumer activity in the area

---

## 8. Business Model & Monetisation

### 8.1 Phase 1 (Months 1-6): Pure growth, no revenue
- Free for both sides
- Focus: prove density and engagement

### 8.2 Phase 2 (Months 6-12): Soft monetisation
- **Featured listings** for businesses (£15-25/month) - top placement in category, push notification slot, multi-offer capability
- **Sponsored emails** to the consumer base - pay-per-send (£X per 1,000 recipients) once list >10K openers
- **In-app display ads via AdMob / Google Display Network** - banner + interstitial, only after >25K DAU

### 8.3 Phase 3 (Year 2+): Diversified revenue
- Subscription tiers for businesses (basic / pro / premium) with analytics, multi-location, API access
- White-label for councils (recurring revenue from local authorities wanting their own branded version)
- Affiliate revenue on bookable categories (salons, restaurants) via integrations

### 8.4 The monetisation surface (driven by the customer-data engine)
Because locolie owns the redemption event *and* puts customer data back in the independent's hands, we have an unusually broad set of levers. The plan is to ladder them in, not switch them all on at once:

| # | Revenue stream | Who pays | Notes |
|---|----------------|----------|-------|
| 1 | **Subscription tiers** - Free / **Featured £19/mo** / **Premium £49/mo** | Business | Core ARPU. Featured = priority placement + featured rail + monthly email feature; Premium adds push-to-shoppers, unlimited customer email/marketing, and full analytics. *(Built in the prototype.)* |
| 2 | **CRM & marketing add-ons** | Business | The customer-data feature is the upsell engine: paid email/push **campaign credits**, automated "win-back" and birthday flows, segmentation. This is the highest-margin, stickiest revenue - independents will pay for customers, not features. |
| 3 | **Data export / advanced analytics** | Business | Export customer lists, footfall & repeat-rate dashboards, offer ROI. Gated to Premium / metered. |
| 4 | **Featured placement & in-app sponsorship** | Business | Sponsored category slots, boosted map pins, homepage features - pay-for-reach on top of subscription. |
| 5 | **Sponsored push & email to shoppers** | Business / brands | Once the consumer list is engaged, paid sends (per-1,000) and category takeovers. |
| 6 | **Display ads (AdMob / GDN)** | Ad networks | Banner/interstitial inventory once DAU supports it (mocked in the app today). |
| 7 | **Affiliate / bookings** | Partners | Rev-share on bookable categories (salons, restaurants, classes) via integrations. |
| 8 | **White-label for councils & BIDs** | Local authorities / Business Improvement Districts | Recurring licence for a branded "shop local" app + the CRM toolkit for their high street. |
| 9 | **Transaction / redemption fee (optional, later)** | Business | A small per-redemption fee on attributed sales - only once value is proven; kept off at launch to drive adoption. |

The throughline: **streams 1-3 monetise the customer relationship we uniquely create**, which is more defensible and higher-margin than ads (6) or fees (9). Ads and affiliate are upside, not the core thesis.

### 8.5 Honest read on the model
The "free both sides + ads later" approach is high-risk because it requires significant scale before revenue meaningfully exists. AdMob CPMs in the UK sit around £2-5; sponsored emails need 10K+ engaged openers to charge competitively. A more defensible path is to introduce **business freemium** by Month 3-6 - keep basic listing free, charge £15-25/month for featured/analytics/multi-offer. This proves willingness-to-pay early, generates revenue alongside growth, and doesn't require massive scale.

**Recommendation:** plan for free-both-sides at launch, with business freemium activating by Month 6 at the latest. Revisit before launch.

---

## 9. Technology & Build

### 9.1 Stack (proposed)

**Consumer iOS app**
- SwiftUI native build, **or**
- FlutterFlow (cross-platform, exports to App Store, ~£60/month, faster build) - recommended for MVP

**Business portal (web)**
- Next.js + React, hosted on Vercel
- Tailwind for styling

**Backend / database**
- Supabase (Postgres + Auth + Storage + Realtime) - generous free tier, scales to paid £25/month
- Edge functions for redemption code generation/validation

**Maps**
- Google Maps SDK for iOS (free, unlimited) for base map display
- Google Places API for business search/autocomplete during signup (cost-controlled)
- Geocoding API for postcode-to-coords (cached aggressively to control cost)

**Notifications**
- OneSignal (free tier up to 10K users) or Firebase Cloud Messaging (free)

**Email**
- Resend or Postmark (~£15/month at MVP scale)

**Analytics**
- Mixpanel free tier or PostHog open-source (self-hosted on existing infra)

**Monitoring**
- Sentry free tier

### 9.2 Build approach
The build is AI-assisted from end to end using Claude (Code + chat). Architecture decisions, schema design, Swift/React code, database migrations, and the redemption logic are all developed iteratively. Tom is the primary builder and product owner, with Joe and Roddy driving merchant sales and operations in parallel so the product launches into a market that's already being warmed up. No external developers required for MVP.

> **Architecture decision (current):** the original Supabase + Next.js plan has been consolidated into a **single Laravel backend**. Laravel serves both the JSON API (consumed by the iOS app) and the web business/admin portal - one codebase, one source of truth for data, with the consumer app as a decoupled front-end. This keeps the door open to reuse the same API for iOS and, later, Android.

### 9.3 Timeline (3 months)
- **Month 1**: Backend schema, auth, business portal MVP (signup → profile → offer creation), redemption code logic
- **Month 2**: iOS app - map, list, business profile, offer detail, redemption flow, push notifications
- **Month 3**: Polish, internal testing with seed businesses, App Store submission (allow 2-3 weeks for review), soft launch in Wokingham

---

## 10. Costs & Financials

### 10.1 Year 1 cost projection (bootstrap)

| Item | One-off | Annual / Recurring |
|---|---|---|
| Apple Developer Program | - | £80 |
| Domain (.com + .co.uk) | £30 | £25/year |
| Companies House registration | £50 | - |
| Hosting & infra (Supabase + Vercel free tiers, Cloudflare R2) | - | £0-300 |
| Google Maps API (with cost controls) | - | £100-1,000 |
| FlutterFlow (if used) | - | £600 |
| Email (Resend) | - | £180 |
| Brand / logo (DIY) | £0-200 | - |
| Marketing (leaflets, stickers, low-budget Meta ads) | - | £500-1,500 |
| Legal (T&Cs, privacy policy templates) | £100-300 | - |
| Contingency | - | £300 |
| **Total** | **£200-600** | **£1,800-4,200** |

**Realistic year 1 spend: £2,000-4,800.** Fits inside the <£5K bootstrap.

### 10.2 Year 1 revenue projection
- **Conservative (free both sides, no monetisation activated)**: £0
- **Realistic (business freemium from Month 6, 100 paying businesses by Month 12 @ £20/mo avg)**: ~£10-15K
- **Optimistic (Month 6 freemium + display ads from Month 9 + first sponsored email)**: ~£20-30K

### 10.3 Year 2 revenue projection (with business freemium fully active and 4 cities live)
- 500 paying businesses × £20/mo = £120K MRR ARR contribution
- Plus ad/email revenue: £30-60K
- Total: £150-180K annual revenue
- Operating costs at this scale: £40-60K (hosting, Maps, tools, contractor design help)
- Net: break-even to modestly profitable, depending on growth investment

---

## 11. Team

locolie is founded by three equal co-founders. Equity is split **evenly three ways - 33.3% each** - reflecting an equal commitment to building the business. (A standard 4-year vesting schedule with a 1-year cliff is recommended for all three to protect the cap table if circumstances change.)

| Co-founder | Equity | Focus (suggested - confirm between founders) |
|---|---|---|
| **Tom** | 33.3% | Product, AI-assisted build, GTM strategy |
| **Joe** | 33.3% | Business development & sales - direct merchant outreach in seed towns |
| **Roddy** | 33.3% | Operations & partnerships - onboarding, BIDs/Chambers, community growth |

The role split above is a starting suggestion based on a typical early-stage division of labour; the founders should confirm who owns what. What matters at this stage is that all three are aligned, equally invested, and cover the three things this business actually needs: **building it, selling it to merchants, and running it on the ground.**

- **Design/UX**: contracted as needed (~£500-1,500 one-off for v1 UI), or AI-assisted in-house.
- **Future hires (Year 2)**: full-time iOS/Android developer, plus a business development hire for territorial expansion.

The intentional choice for Year 1 is no full-time employees beyond the founders. AI-assisted development, no-code where appropriate, and contracted specialists keep burn close to zero - three founders sharing the load also means the "single-founder bandwidth" risk that kills most side-projects is materially reduced.

---

## 12. Risks & Mitigations

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Two-sided cold start fails - too few businesses for consumers, too few consumers for businesses | High | High | Geographic focus: seed Wokingham + Reading first. Don't claim national coverage in marketing until density is real. |
| Free-both-sides model doesn't generate revenue fast enough | High | Medium | Activate business freemium by Month 6. Don't wait for AdMob scale. |
| iOS-only limits addressable market by ~50% (Android share UK ~50%) | Medium | High | Plan Android in Month 4-6 once iOS is stable. FlutterFlow makes this cheaper. |
| Google Maps API costs balloon | Medium | Medium | Cache geocoding aggressively, debounce autocomplete, use Maps SDK (free) for display, monitor weekly. |
| Apple App Store rejection | Low | Medium | Follow guidelines from start, plan 2-3 weeks for review. |
| Local Pocket or new competitor accelerates | Medium | Medium | Velocity advantage from AI-built stack. Plan monthly product releases. |
| Founder bandwidth (all three have other commitments) | Medium | High | Three equal co-founders share the load across build, sales and operations. Set a realistic shared cadence and be honest about pace until traction proves it out. |
| Consumer acquisition cost too high | Medium | High | Lean heavily on free channels (Reddit, FB groups, local press, in-store stickers) before paid. |
| GDPR / data protection compliance | Medium | High | Use standard T&Cs and privacy policy templates from a UK service (Genie AI, Rocket Lawyer ~£100). Minimal data collection by design. |

---

## 13. Next Steps (next 30 days)

1. **Lock in the name** - check .com/.co.uk availability and UK IPO trademark for Highstreet, Vicinity, locolie, +2 alternates. Shortlist 3, pick one.
2. **Companies House** - register the limited company.
3. **Apple Developer enrollment** - start the DUNS number process (can take 2-3 weeks).
4. **Domain + Google Workspace** - get the address sorted.
5. **Decide stack** - SwiftUI native vs FlutterFlow. Build a small prototype of each in week 1.
6. **Install Local Pocket** - sign up as a business and as a consumer, document every screen, every flow, every weakness. The single best feature spec for v1.
7. **Talk to 10 indie business owners in Wokingham** - show them the concept on paper. What would they pay? What would make them sign up? What's the redemption mechanic they'd actually use?
8. **Draft the redemption logic** - code generation, validation rules, expiry. The trickiest backend piece.
9. **Build the business portal first** - easier than iOS, lets you onboard pilot businesses while the consumer app is being built.

---

## Appendix A - Feature parity & improvement matrix vs Local Pocket

| Feature | Local Pocket | locolie v1 | Improvement |
|---|---|---|---|
| Geographic scope | Single borough | UK-wide, seed 2-3 towns | National ambition from day one |
| Business onboarding | Unclear, council-mediated | Self-serve web portal | 5-minute signup, no sales call |
| Offer creation | Yes | Yes | Add expiry, redemption limits, multi-offer per business |
| Map view | Yes | Yes | Live offer pins with category filter |
| Search | Limited | By name, category, offer keyword | Better discoverability |
| Push notifications | Unclear | Nearby offers + favourites | Active retention loop |
| Redemption verification | Unknown - likely "show staff" | 6-digit unique code | **Real data for businesses** |
| Business analytics | Not visible | Profile views, offer views, redemptions | Foundation for paid tier |
| Council partnership | Yes - locked in | Optional, opportunistic | Don't depend on it |
| Update cadence | Slow / "beta" | Monthly releases | Velocity advantage |
| Monetisation path | None visible | Freemium businesses + ads | Clear road to revenue |

---

## Appendix B - Open questions to resolve before MVP build

1. Final company and product name
2. Visual identity / brand direction (clean modern vs playful community vs UK heritage)
3. SwiftUI native vs FlutterFlow - week-1 prototype decision
4. Redemption code length, expiry, and security (does it need to be cryptographically signed, or is a 6-digit + timestamp + lookup sufficient?)
5. What happens when a business goes bust or stops honouring offers? Reporting flow + auto-flagging
6. T&Cs / liability - who's responsible if a merchant refuses to honour a valid offer?
7. VAT treatment of any paid tier (UK VAT registration threshold £90K)
8. When does business freemium activate? Month 6 vs at launch?
9. Android timeline - Month 4 or Month 6?
10. First 10 categories to feature (food, drink, salons, retail, mechanics, plumbers, electricians, fitness, beauty, services?)

---

## Appendix C - Name exploration

The name should signal the core idea: **discovering what's good, nearby, right now** - local, independent, on your doorstep. It needs to be short, easy to say, available as a `.com`/`.co.uk`, trademark-clear, and ideally App-Store-distinct from *Local Pocket*. Below are candidates grouped by the feeling they lean into. (Availability not yet checked - shortlist first, then run domain + UK IPO trademark searches.)

### Theme: Local / proximity
| Name | Why it works | Watch-outs |
|---|---|---|
| **locolie** *(current)* | Clear, action-led, says exactly what it does | Slightly generic; check trademark crowding |
| **Vicinity** | Premium, modern, "everything in your vicinity" | Longer to type |
| **Nearby / Neara** | Instantly understood; "Neara" is a brandable twist | Plain "Nearby" likely taken |
| **Hereabouts** | Warm, characterful, very British | Long |
| **Patch** | "What's on in your patch" - short, ownable, friendly | Common word; needs strong logo |

### Theme: High street / community
| Name | Why it works | Watch-outs |
|---|---|---|
| **Highstreet / HighSt** | Directly evokes indie shops; strong PR angle ("save the high street") | Generic noun, trademark risk |
| **TownLoop** | "Stay in the loop with your town" - retention built into the name | Two concepts stuck together |
| **LocalLoop** | Same loop idea, clearer locality | A little soft |
| **Doorstep** | "Right on your doorstep" - hyperlocal, homely | Used in grocery/delivery contexts |
| **Cornershop** | Nostalgic, instantly British, indie-coded | Evokes convenience stores specifically |

### Theme: Discovery / browse (more brandable, less literal)
| Name | Why it works | Watch-outs |
|---|---|---|
| **Mooch** | "Have a mooch" - perfectly British for casual browsing; fun, memorable, ownable | Informal; may read young |
| **Yonder** | Discovery-led, premium, calm | Less obviously "local deals" |
| **Pop** | "Pop in" / "Pop down the road"; tiny, punchy, app-friendly | Very short = hard to trademark |
| **Tucked** | "Tucked-away local gems" - curated, premium feel | Needs explanation |

### Recommendation
Carry **3 into testing**: a clear one (**locolie** or **Vicinity**), a community one (**Patch** or **TownLoop**), and a brandable wildcard (**Mooch**). Check `.com`/`.co.uk` + UK IPO trademark for all three, mock each up as a logo (see the Brand page), and gut-check with a few Wokingham merchants before locking it in.

---

*End of plan. This is a v0.1 working document, not a finished investor deck. Iterate from here.*
