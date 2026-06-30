<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * A connected social platform account (one per platform). The access token is
 * stored with the 'encrypted' cast so the secret is never readable at rest;
 * non-secret identifiers (page id, ig user id, scopes) live in meta. Until the
 * developer app is approved and OAuth completes, connected stays false and the
 * row just records the handle for the calendar.
 */
#[Fillable(['platform', 'handle', 'display_name', 'access_token', 'token_expires_at', 'connected', 'meta'])]
class SocialAccount extends Model
{
    /** Supported platforms, in display order. */
    public const PLATFORMS = ['facebook', 'instagram', 'tiktok', 'linkedin'];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
            'token_expires_at' => 'datetime',
            'connected' => 'boolean',
            'meta' => 'array',
        ];
    }

    /** Human label for a platform key. */
    public static function label(string $platform): string
    {
        return [
            'facebook' => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok' => 'TikTok',
            'linkedin' => 'LinkedIn',
        ][$platform] ?? ucfirst($platform);
    }

    /** Brand colour per platform - used to colour-code the calendar. */
    public static function color(string $platform): string
    {
        return [
            'facebook' => '#1877F2',
            'instagram' => '#E1306C',
            'tiktok' => '#000000',
            'linkedin' => '#0A66C2',
        ][$platform] ?? '#64748b';
    }

    public function isConnected(): bool
    {
        return (bool) $this->connected && filled($this->access_token);
    }

    public function isExpired(): bool
    {
        return $this->token_expires_at !== null && $this->token_expires_at->isPast();
    }
}
