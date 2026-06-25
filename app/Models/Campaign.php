<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'channel', 'status', 'provider', 'template_id', 'subject', 'body', 'sent_count', 'opens', 'clicks', 'opened_by', 'scheduled_at', 'meta'])]
class Campaign extends Model
{
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'opened_by' => 'array',
            'scheduled_at' => 'datetime',
        ];
    }

    /** Unique opens (distinct opener emails), falling back to the raw counter. */
    public function uniqueOpens(): int
    {
        return is_array($this->opened_by) ? count($this->opened_by) : (int) $this->opens;
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class);
    }
}
