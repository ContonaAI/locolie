<?php

namespace App\Services;

use App\Models\Business;
use App\Models\LoyaltyProgress;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRule;
use Illuminate\Support\Str;

/**
 * Customisable loyalty rules engine.
 *
 * A business defines rules ("visit 5 times -> free coffee", "spend £50 ->
 * 10% off"). Each verified redemption (a scan at the till) records a visit for
 * the customer's email and re-evaluates every active rule. When a rule's
 * threshold is crossed a reward is issued; repeat rules then start a new cycle.
 *
 * Customers are keyed by email (captured at redemption) - no login needed.
 */
class LoyaltyService
{
    /**
     * Record a visit (and optional spend) for a customer and award any rewards earned.
     *
     * @return array{progress: ?LoyaltyProgress, earned: LoyaltyReward[]}
     */
    public function recordVisit(Business $business, ?string $email, int $spendPence = 0): array
    {
        if (blank($email) || ! $business->loyaltyLive()) {
            return ['progress' => null, 'earned' => []];
        }

        $email = mb_strtolower(trim($email));

        $progress = LoyaltyProgress::firstOrCreate(
            ['business_id' => $business->id, 'customer_email' => $email],
            ['visits' => 0, 'spend' => 0, 'counters' => []],
        );

        $progress->visits += 1;
        $progress->spend += max(0, $spendPence);
        $progress->last_visit_at = now();

        $earned = $this->evaluate($business, $progress);

        $progress->save();

        return ['progress' => $progress, 'earned' => $earned];
    }

    /**
     * Walk the active rules, advancing each rule's cycle counter and issuing a
     * reward whenever a threshold is reached. Mutates $progress->counters.
     *
     * @return LoyaltyReward[]
     */
    protected function evaluate(Business $business, LoyaltyProgress $progress): array
    {
        $counters = $progress->counters ?? [];
        $earned = [];

        foreach ($business->loyaltyRules()->where('active', true)->get() as $rule) {
            // How far into the current cycle this rule is (resets on repeat rewards).
            $current = (int) ($counters[$rule->id] ?? 0);
            $current += $rule->metric === 'spend' ? $progress->spend - ($counters['_spend_seen'][$rule->id] ?? 0) : 1;

            if ($rule->metric === 'spend') {
                // Track spend already counted toward this rule so we only add the delta.
                $counters['_spend_seen'][$rule->id] = $progress->spend;
            }

            $threshold = max(1, (int) $rule->threshold);

            if ($current >= $threshold) {
                // One-time rules fire once; repeat rules can fire every cycle.
                $alreadyEarnedOnce = LoyaltyReward::where('business_id', $business->id)
                    ->where('customer_email', $progress->customer_email)
                    ->where('rule_id', $rule->id)
                    ->exists();

                if ($rule->repeat || ! $alreadyEarnedOnce) {
                    $earned[] = $this->issueReward($business, $progress->customer_email, $rule);
                    $current = $rule->repeat ? ($current - $threshold) : $threshold; // carry remainder on stamp cards
                }
            }

            $counters[$rule->id] = $current;
        }

        $progress->counters = $counters;

        return $earned;
    }

    protected function issueReward(Business $business, string $email, LoyaltyRule $rule): LoyaltyReward
    {
        return LoyaltyReward::create([
            'business_id' => $business->id,
            'customer_email' => $email,
            'rule_id' => $rule->id,
            'label' => $rule->reward_label,
            'code' => $this->uniqueCode(),
            'status' => 'earned',
            'earned_at' => now(),
        ]);
    }

    /**
     * Public progress snapshot for the consumer app: each rule with how close
     * the customer is, plus any unredeemed rewards waiting to be claimed.
     */
    public function snapshot(Business $business, ?string $email): array
    {
        if (! $business->loyaltyLive()) {
            return ['active' => false, 'rules' => [], 'rewards' => []];
        }

        $email = $email ? mb_strtolower(trim($email)) : null;
        $progress = $email
            ? LoyaltyProgress::where('business_id', $business->id)->where('customer_email', $email)->first()
            : null;

        $rules = $business->loyaltyRules()->where('active', true)->get()->map(function (LoyaltyRule $rule) use ($progress) {
            $current = $progress ? $progress->cycleProgress($rule) : 0;
            $threshold = max(1, (int) $rule->threshold);

            return [
                'id' => $rule->id,
                'name' => $rule->name,
                'metric' => $rule->metric,
                'goal' => $rule->goalLabel(),
                'reward' => $rule->reward_label,
                'current' => $rule->metricLabel(min($current, $threshold)),
                'current_raw' => min($current, $threshold),
                'threshold' => $threshold,
                'threshold_label' => $rule->metricLabel($threshold),
                'remaining' => max(0, $threshold - $current),
                'remaining_label' => $rule->metricLabel(max(0, $threshold - $current)),
                'percent' => (int) round(min(100, $current / $threshold * 100)),
            ];
        })->values()->all();

        $rewards = $email
            ? LoyaltyReward::where('business_id', $business->id)->where('customer_email', $email)
                ->where('status', 'earned')->latest('id')->get()
                ->map(fn ($r) => ['code' => $r->code, 'label' => $r->label, 'earned_at' => $r->earned_at?->toIso8601String()])
                ->values()->all()
            : [];

        return [
            'active' => true,
            'headline' => $business->loyaltyProgram?->headline,
            'blurb' => $business->loyaltyProgram?->blurb,
            'rules' => $rules,
            'rewards' => $rewards,
        ];
    }

    protected function uniqueCode(): string
    {
        do {
            $code = 'LY'.strtoupper(Str::random(6));
        } while (LoyaltyReward::where('code', $code)->exists());

        return $code;
    }
}
