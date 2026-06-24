# locolie Website - UX + SEO Spec & Audit

The marketing site (`resources/views/site/*`). Reviewed as a website/UX specialist **and** an SEO specialist. Status: ✅ done · ◻️ backlog.

## Global / layout (`site/layout.blade.php`)
**UX**
- ✅ Standalone **floating glass nav** (detached pill, blurs on scroll), icons on every link, small spinning **team-portal cog** → `/portal`.
- ✅ Mobile = floating glass menu card; all CTAs duplicated.
- ✅ Scroll-reveal + parallax + count-up engine (IntersectionObserver), with **no-JS and reduced-motion fallbacks** (content never hidden without JS).
- ✅ `section[id]{scroll-margin-top:6rem}` so anchor jumps clear the fixed nav.
- ✅ `:focus-visible` outlines for keyboard a11y.
**SEO**
- ✅ Per-page `<title>` + unique `meta description`; canonical; Open Graph + Twitter card; theme-color; favicon.
- ✅ JSON-LD **Organization** (name, area served NE1, founders).
- ✅ `/robots.txt` (+ disallow private areas) and `/sitemap.xml`.
- ◻️ Add a real `og:image` (1200×630 social card) - needs a designed asset.

## Performance (site-wide) - **the bandwidth fix**
- ✅ Business images recompressed **25MB → 2.1MB** (≤360px, JPEG q60; biggest 42KB). `PlacesService` auto-compresses new photos.
- ✅ Marketing pages embed **zero iframes** - the app demo is a hand-built **static mock** (no network, no app/API/image load). Homepage payload ≈ **105KB** (was multiple MB).
- ✅ In-app: offscreen category rows/list rows use `content-visibility:auto` so their images don't load until scrolled near.
- ◻️ Longer term: precompiled Tailwind CSS instead of Play CDN (CDN is off-tunnel so doesn't hit bandwidth, but a build would cut client JS).

## Section-by-section (home)
1. **Hero** - H1 "Your high street, reimagined", dual CTAs, animated gradient-mesh + parallax, static app mock with looping "redeemed" toast. *UX:* one clear primary action. *SEO:* single H1, keyword-rich subcopy.
2. **Two-sided value band** - Shoppers vs **Businesses** (data capture · email · push · **SMS** · analytics chips). Sells both audiences immediately.
3. **Problem** - "1 in 7 empty / £0 budget / 0% customers owned" - frames the need.
4. **Why locolie** - 6 glass USP cards (map, redeemable offers, own-your-customers, search, push/email/SMS, free-to-start).
5. **See it live** - static mock + **"Open the live app ↗"** (full-screen, new tab - where the real app works perfectly). No fragile iframe.
6. **Own your customers** - the wedge: first-party data + email/push/SMS marketing, with a mock "Your customers" card.
7. **Comparison** - "With locolie vs going it alone" tick table.
8. **How it works** - 3 shopper steps.
9. **Stats band** - count-up (businesses, categories, NE1).
10. **Pricing** - Free / Featured £19 / Premium £49 (reads `Business::PLANS`).
11. **Founders** - Tom · Joe · Roddy, equal.
12. **Download** - App Store / Google Play (coming soon) + web app.
13. **Final CTA**.

## For-business (`site/for-business.blade.php`)
- ✅ Hero, value props, dark **"own your customers"** band, big pricing, onboarding steps, FAQ.
- ✅ **FAQPage JSON-LD** for rich snippets; GDPR/data-ownership FAQ.

## Accessibility
- ✅ Landmarks (`<nav aria-label>`, `<main>`, `<footer>`), aria-labels on icon-only links, focus-visible, decorative SVGs `aria-hidden`, reduced-motion honoured.
- ◻️ Full contrast audit on muted text over tinted backgrounds.

## Backlog (next)
- og:image asset · precompiled CSS · contrast audit · per-business SEO landing pages (`/business/{slug}`) for local search · blog/content for organic.
