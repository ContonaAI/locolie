<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'channel', 'status', 'provider', 'template_id', 'subject', 'body', 'sent_count', 'scheduled_at', 'meta'])]
class Campaign extends Model
{
    protected function casts(): array
    {
        return [
            'meta' => 'array',
            'scheduled_at' => 'datetime',
        ];
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
