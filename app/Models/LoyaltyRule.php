<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'business_id', 'active', 'sort', 'name', 'metric', 'threshold',
    'repeat', 'reward_type', 'reward_value', 'reward_label',
])]
class LoyaltyRule extends Model
{
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'repeat' => 'boolean',
            'threshold' => 'integer',
            'reward_value' => 'integer',
            'sort' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    /** Human sentence describing what the customer has to do. */
    public function goalLabel(): string
    {
        if ($this->metric === 'spend') {
            return 'Spend £'.number_format($this->threshold / 100, ($this->threshold % 100) ? 2 : 0);
        }

        return $this->threshold.' '.($this->threshold === 1 ? 'visit' : 'visits');
    }

    /** Where a customer's accrued metric sits, formatted for display. */
    public function metricLabel(int $value): string
    {
        return $this->metric === 'spend'
            ? '£'.number_format($value / 100, ($value % 100) ? 2 : 0)
            : (string) $value;
    }
}
