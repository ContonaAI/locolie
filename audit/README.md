# locolie (GoLocal) audit - index

A six-stream audit of the locolie marketplace (Laravel + Blade + Tailwind v4 + Capacitor).
Read in order, or jump to a surface. The master plan ties it all together.

- **[PLAN.md](PLAN.md)** - master ranked implementation plan: exec summary, full P0/P1/P2
  backlog (62 items), recommended sequence/waves, and decisions needed from Tom. Start here.

## 00 - Surface maps (what exists today)
- **[00-map-site.md](00-map-site.md)** - public marketing/discovery site (#1), customer
  report "Your locolie" (#4), QR window sticker (#5): routes, views, the visitor journey, flags.
- **[00-map-app-api.md](00-map-app-api.md)** - the Capacitor iOS shell + `/app` experience
  (#2) and the full `routes/api.php` (browse/retailer/push/redemption/sync) + security flags.
- **[00-map-business.md](00-map-business.md)** - business self-serve CRM (#3): login,
  dashboard, customers, billing, reports, messaging - feature by feature with gaps.
- **[00-map-portal.md](00-map-portal.md)** - internal team portal (#6) + Messaging Studio
  (#7): the admin CRM, the disabled gate, and wired-vs-stubbed channel status.

## 01 - Benchmarks (what leaders do)
- **[01-bench-marketplaces.md](01-bench-marketplaces.md)** - Airbnb/Booking/Trivago patterns
  (search, cards, map+list, trust, CTA, empty states, mobile nav) mapped to locolie files.
- **[01-bench-services.md](01-bench-services.md)** - Thumbtack/TaskRabbit/Checkatrade/Yelp
  patterns (earned trust badges, vetting honesty, freshness, status tiers) mapped to files.

## 02 - UX audits (where it breaks)
- **[02-ux-consumer.md](02-ux-consumer.md)** - consumer journey (site + /app + report):
  32 findings incl. the severed redeem->report loop (P0-1) and ngrok native shell.
- **[02-ux-business.md](02-ux-business.md)** - business CRM + team portal + Messaging Studio:
  billing bypass, public admin endpoints, the lying quick-email, dead-end onboarding.

## 03 - Design system
- **[03-current-design.md](03-current-design.md)** - the as-built visual language: the dead
  Vite pipeline, two greys, two emeralds, four wordmark encodings, ad-hoc radii/shadows.
- **[03-design-system.md](03-design-system.md)** - the unified, implementable design system:
  full token set, paste-ready `@theme` block, component specs, and a migration plan.

## 04 - Copy
- **[04-copy-audit.md](04-copy-audit.md)** - copy across every surface: GoLocal brand leaks,
  'GL' initials, pricing/empty-state rewrites, dev-speak softening, em-dash removals (~32 fixes).

## 05 - Performance & quality
- **[05-perf-backend.md](05-perf-backend.md)** - controllers/models/services/migrations: no
  rate limiting, oversell race, missing indexes, ReportingService whole-table loads, N+1s.
- **[05-perf-frontend.md](05-perf-frontend.md)** - assets/a11y: CDN Tailwind in prod, Google
  Fonts/Translate on the critical path, unbundled Alpine, missing og/favicon, image CLS.
