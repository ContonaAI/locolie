<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Campaign;
use App\Models\Offer;
use App\Models\Redemption;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * The reporting brain. Turns raw redemptions, offers and campaigns into the
 * numbers each audience actually cares about:
 *
 *  - forBusiness(): a retailer's own performance - customers, redemptions,
 *    estimated revenue influenced, marketing reach and channel results.
 *  - forCustomer(): a shopper's "Your locolie" - money saved, places visited,
 *    local businesses supported.
 *  - platform(): the team's bird's-eye view across the whole marketplace.
 *
 * Money is always an *estimate* derived from the offer badge (e.g. "20% OFF",
 * "£5 OFF", "2-FOR-1") against a typical transaction value, and is labelled as
 * such everywhere it surfaces. No payment data is assumed.
 */
class ReportingService
{
    /** Typical spend per visit, used to estimate the value of a % discount. */
    public const AVG_TRANSACTION = 28.0;

    /** Channel display metadata, reused across every report. */
    public const CHANNELS = [
        'email' => ['label' => 'Email', 'color' => '#0284c7'],
        'sms' => ['label' => 'SMS', 'color' => '#7c3aed'],
        'push' => ['label' => 'Push', 'color' => '#d97706'],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    //  Money estimation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Estimate the saving a single redemption delivered to the shopper, parsed
     * from the offer badge. Deliberately conservative and clearly an estimate.
     */
    public function estimateSaving(?Offer $offer): float
    {
        $badge = Str::upper((string) ($offer->badge ?? ''));

        if (preg_match('/(\d+(?:\.\d+)?)\s*%/', $badge, $m)) {
            return round(self::AVG_TRANSACTION * ((float) $m[1] / 100), 2);
        }
        if (preg_match('/£\s*(\d+(?:\.\d+)?)/', $badge, $m)) {
            return (float) $m[1];
        }
        if (Str::contains($badge, ['2-FOR-1', '2 FOR 1', 'BOGOF', 'BUY ONE'])) {
            return round(self::AVG_TRANSACTION / 2, 2);
        }
        if (Str::contains($badge, 'FREE')) {
            // A free item: coffee/quote/sample - a modest typical value.
            return Str::contains($badge, ['WEEK', 'MONTH', 'QUOTE']) ? 25.0 : 4.5;
        }

        // Unknown / "other": a small assumed saving so totals stay honest.
        return 6.0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Retailer report
    // ─────────────────────────────────────────────────────────────────────────

    public function forBusiness(Business $business, int $days = 30): array
    {
        $offerIds = $business->offers()->pluck('id');
        $redemptions = Redemption::with('offer')
            ->whereIn('offer_id', $offerIds)
            ->get();

        $redeemed = $redemptions->where('status', 'redeemed');
        $since = Carbon::now()->subDays($days - 1)->startOfDay();

        // Distinct customers + marketing reachability.
        $byEmail = $redemptions->filter(fn ($r) => $r->customer_email)->groupBy('customer_email');
        $emailOptIn = $byEmail->filter(fn ($g) => (bool) $g->max('marketing_opt_in'))->count();
        $smsReach = $redemptions->filter(fn ($r) => $r->customer_phone && $r->sms_opt_in)
            ->pluck('customer_phone')->unique()->count();

        $savings = $redeemed->sum(fn ($r) => $this->estimateSaving($r->offer));

        // Repeat customers (more than one redemption on record).
        $repeat = $byEmail->filter(fn ($g) => $g->count() > 1)->count();

        $campaigns = Campaign::where('business_id', $business->id)->get();

        return [
            'business' => $business,
            'range_days' => $days,
            'generated_at' => Carbon::now(),

            'kpis' => [
                'customers' => $byEmail->count(),
                'new_customers' => $byEmail->filter(fn ($g) => $g->min('created_at') >= $since)->count(),
                'redemptions' => $redeemed->count(),
                'pending' => $redemptions->where('status', 'pending')->count(),
                'revenue_influenced' => round($redeemed->count() * self::AVG_TRANSACTION, 2),
                'savings_delivered' => round($savings, 2),
                'repeat_customers' => $repeat,
                'repeat_rate' => $byEmail->count() ? round($repeat / $byEmail->count() * 100) : 0,
            ],

            'reach' => [
                'email_optin' => $emailOptIn,
                'email_rate' => $byEmail->count() ? round($emailOptIn / $byEmail->count() * 100) : 0,
                'sms' => $smsReach,
                'total_customers' => $byEmail->count(),
            ],

            'redemptions_series' => $this->dailySeries($redeemed, 'redeemed_at', $days),
            'customers_series' => $this->newCustomersSeries($byEmail, $days),

            'top_offers' => $this->topOffers($business, $redemptions),
            'channels' => $this->channelStats($campaigns),
            'visit_pattern' => $this->visitPattern($redeemed),
            'recent_customers' => $this->recentCustomers($byEmail),
        ];
    }

    /** Top offers ranked by redemptions, with redemption rate + est. value. */
    protected function topOffers(Business $business, Collection $redemptions): array
    {
        return $business->offers()->get()->map(function (Offer $o) use ($redemptions) {
            $rs = $redemptions->where('offer_id', $o->id);
            $done = $rs->where('status', 'redeemed')->count();

            return [
                'title' => $o->title,
                'badge' => $o->badge,
                'status' => $o->status,
                'redemptions' => $done,
                'issued' => $rs->count(),
                'rate' => $rs->count() ? round($done / $rs->count() * 100) : 0,
                'value' => round($done * $this->estimateSaving($o), 2),
            ];
        })->sortByDesc('redemptions')->take(8)->values()->all();
    }

    /** Per-channel message stats from the campaign log. */
    protected function channelStats(Collection $campaigns): array
    {
        $out = [];
        foreach (self::CHANNELS as $key => $meta) {
            $rows = $campaigns->where('channel', $key);
            $sent = (int) $rows->sum('sent_count');
            // Modelled engagement benchmarks (clearly indicative, not measured yet).
            $openRate = ['email' => 0.42, 'sms' => 0.94, 'push' => 0.61][$key];
            $clickRate = ['email' => 0.11, 'sms' => 0.19, 'push' => 0.14][$key];
            $out[$key] = [
                'label' => $meta['label'],
                'color' => $meta['color'],
                'campaigns' => $rows->count(),
                'sent' => $sent,
                'est_opens' => (int) round($sent * $openRate),
                'est_clicks' => (int) round($sent * $clickRate),
                'open_rate' => (int) round($openRate * 100),
                'click_rate' => (int) round($clickRate * 100),
            ];
        }

        return $out;
    }

    /** Redemptions bucketed by day-of-week, to show the busy days. */
    protected function visitPattern(Collection $redeemed): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $counts = array_fill_keys($days, 0);
        foreach ($redeemed as $r) {
            $when = $r->redeemed_at ?? $r->created_at;
            if ($when) {
                $counts[$days[$when->dayOfWeekIso - 1]]++;
            }
        }

        return $counts;
    }

    protected function recentCustomers(Collection $byEmail): array
    {
        return $byEmail->map(fn ($g) => [
            'name' => $g->first()->customer_name ?: 'Customer',
            'email' => $g->first()->customer_email,
            'visits' => $g->count(),
            'opt_in' => (bool) $g->max('marketing_opt_in'),
            'last' => $g->max('redeemed_at') ?? $g->max('created_at'),
        ])->sortByDesc('last')->take(8)->values()->all();
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Customer report ("Your locolie")
    // ─────────────────────────────────────────────────────────────────────────

    public function forCustomer(string $email): array
    {
        $redemptions = Redemption::with('offer.business')
            ->where('customer_email', $email)
            ->get();
        $redeemed = $redemptions->where('status', 'redeemed');

        $businesses = $redeemed->map(fn ($r) => $r->offer?->business)->filter()->unique('id');
        $savings = $redeemed->sum(fn ($r) => $this->estimateSaving($r->offer));

        $byCategory = $businesses->groupBy(fn ($b) => $b->category?->name ?? 'Other')
            ->map->count()->sortDesc();

        $name = $redemptions->first()->customer_name ?? null;

        return [
            'email' => $email,
            'name' => $name,
            'found' => $redemptions->isNotEmpty(),
            'generated_at' => Carbon::now(),
            'kpis' => [
                'saved' => round($savings, 2),
                'redemptions' => $redeemed->count(),
                'businesses' => $businesses->count(),
                'favourite_category' => $byCategory->keys()->first(),
            ],
            'places' => $businesses->take(12)->map(fn ($b) => [
                'name' => $b->name,
                'slug' => $b->slug,
                'category' => $b->category?->name,
                'visits' => $redeemed->filter(fn ($r) => $r->offer?->business_id === $b->id)->count(),
                'logo' => $b->logoUrl(),
                'color' => $b->brandColor(),
                'initials' => $b->brandInitials(),
            ])->values()->all(),
            'timeline' => $redeemed->sortByDesc('redeemed_at')->take(10)->map(fn ($r) => [
                'business' => $r->offer?->business?->name,
                'offer' => $r->offer?->title,
                'badge' => $r->offer?->badge,
                'saved' => $this->estimateSaving($r->offer),
                'when' => $r->redeemed_at ?? $r->created_at,
            ])->values()->all(),
            'categories' => $byCategory->take(5)->toArray(),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Platform report (team)
    // ─────────────────────────────────────────────────────────────────────────

    public function platform(int $days = 30): array
    {
        $redemptions = Redemption::with('offer')->get();
        $redeemed = $redemptions->where('status', 'redeemed');
        $campaigns = Campaign::all();

        return [
            'range_days' => $days,
            'generated_at' => Carbon::now(),
            'kpis' => [
                'businesses' => Business::where('onboarded', true)->count(),
                'paid' => Business::whereIn('plan', ['featured', 'premium'])->count(),
                'redemptions' => $redeemed->count(),
                'customers' => $redemptions->pluck('customer_email')->filter()->unique()->count(),
                'savings_delivered' => round($redeemed->sum(fn ($r) => $this->estimateSaving($r->offer)), 2),
                'messages_sent' => (int) $campaigns->sum('sent_count'),
            ],
            'redemptions_series' => $this->dailySeries($redeemed, 'redeemed_at', $days),
            'channels' => $this->channelStats($campaigns),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    //  Series helpers - return label/value pairs ready for an inline SVG chart
    // ─────────────────────────────────────────────────────────────────────────

    /** Count rows per day over the trailing window using $dateField. */
    protected function dailySeries(Collection $rows, string $dateField, int $days): array
    {
        $buckets = $this->emptyDayBuckets($days);
        foreach ($rows as $r) {
            $d = $r->{$dateField} ?? $r->created_at;
            if (! $d) {
                continue;
            }
            $key = Carbon::parse($d)->toDateString();
            if (isset($buckets[$key])) {
                $buckets[$key]++;
            }
        }

        return $this->labelledSeries($buckets);
    }

    /** New-customer count per day from grouped-by-email collection. */
    protected function newCustomersSeries(Collection $byEmail, int $days): array
    {
        $buckets = $this->emptyDayBuckets($days);
        foreach ($byEmail as $group) {
            $first = $group->min('created_at');
            $key = $first ? Carbon::parse($first)->toDateString() : null;
            if ($key && isset($buckets[$key])) {
                $buckets[$key]++;
            }
        }

        return $this->labelledSeries($buckets);
    }

    /** @return array<string,int> date => 0, oldest first. */
    protected function emptyDayBuckets(int $days): array
    {
        $buckets = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $buckets[Carbon::now()->subDays($i)->toDateString()] = 0;
        }

        return $buckets;
    }

    /** Shape buckets into [['label'=>'12 Jun','value'=>3,'date'=>...], ...]. */
    protected function labelledSeries(array $buckets): array
    {
        $out = [];
        foreach ($buckets as $date => $value) {
            $out[] = [
                'date' => $date,
                'label' => Carbon::parse($date)->format('j M'),
                'value' => $value,
            ];
        }

        return $out;
    }
}
