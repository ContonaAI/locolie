<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'customer_email', 'visits', 'spend', 'counters', 'last_visit_at'])]
class LoyaltyProgress extends Model
{
    protected $table = 'loyalty_progress';

    protected function casts(): array
    {
        return [
            'counters' => 'array',
            'visits' => 'integer',
            'spend' => 'integer',
            'last_visit_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** Accrued value for a rule's metric (visits or lifetime spend). */
    public function metricValue(LoyaltyRule $rule): int
    {
        return $rule->metric === 'spend' ? (int) $this->spend : (int) $this->visits;
    }

    /** Progress within the current cycle of a rule (resets after a reward on repeat rules). */
    public function cycleProgress(LoyaltyRule $rule): int
    {
        $counters = $this->counters ?? [];

        return (int) ($counters[$rule->id] ?? $this->metricValue($rule));
    }
}
