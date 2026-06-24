<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Append-only consent audit trail (GDPR accountability). We never update or
 * delete rows here — every consent action writes a new record of what was
 * agreed, when, from where and by which IP.
 */
class ConsentLog extends Model
{
    protected $table = 'consent_log';

    public $timestamps = false;

    protected $fillable = [
        'email', 'phone', 'action', 'topic', 'channel', 'source',
        'document_version', 'ip_address', 'user_agent', 'meta', 'created_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    public static function record(string $action, ?string $email = null, array $attrs = []): self
    {
        return self::create([
            'email' => $email ? strtolower(trim($email)) : null,
            'phone' => $attrs['phone'] ?? null,
            'action' => $action,
            'topic' => $attrs['topic'] ?? null,
            'channel' => $attrs['channel'] ?? null,
            'source' => $attrs['source'] ?? null,
            'document_version' => $attrs['document_version'] ?? null,
            'ip_address' => $attrs['ip_address'] ?? request()?->ip(),
            'user_agent' => $attrs['user_agent'] ?? substr((string) request()?->userAgent(), 0, 512),
            'meta' => $attrs['meta'] ?? null,
            'created_at' => now(),
        ]);
    }
}
