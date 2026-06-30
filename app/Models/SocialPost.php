<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A single entry on the marketing calendar: one piece of copy (plus optional
 * media) targeted at one or more platforms, moving through the lifecycle
 * idea -> draft -> scheduled -> posted (or failed). Publishing is performed by
 * PublishSocialPostJob via the SocialPublisher once the platform apps are live.
 */
#[Fillable([
    'platforms', 'body', 'media', 'status', 'scheduled_at', 'posted_at',
    'external_id', 'error', 'created_by', 'meta',
])]
class SocialPost extends Model
{
    public const STATUSES = ['idea', 'draft', 'scheduled', 'posted', 'failed'];

    protected function casts(): array
    {
        return [
            'platforms' => 'array',
            'media' => 'array',
            'meta' => 'array',
            'scheduled_at' => 'datetime',
            'posted_at' => 'datetime',
        ];
    }

    /** Tailwind classes for a status pill. */
    public static function statusStyle(string $status): string
    {
        return [
            'idea' => 'bg-slate-100 text-slate-600',
            'draft' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
            'scheduled' => 'bg-sky-50 text-sky-700 ring-1 ring-sky-200',
            'posted' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
            'failed' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-200',
        ][$status] ?? 'bg-slate-100 text-slate-600';
    }

    public function isDue(): bool
    {
        return $this->status === 'scheduled'
            && $this->scheduled_at !== null
            && ! $this->scheduled_at->isFuture();
    }
}
