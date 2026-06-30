<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Storage;

/**
 * Shared scaffolding for the per-platform publishers: app-configured / account-
 * connected guards and media-URL resolution. Each concrete publisher only has
 * to implement doPublish() with the real Graph/REST call; publish() wraps it so
 * missing config degrades to a clear "not connected" result and runtime errors
 * are caught into a "failed" result rather than blowing up the job.
 */
abstract class BasePublisher implements Publisher
{
    /** Is the developer app for this platform configured in config/services.php? */
    protected function appConfigured(): bool
    {
        $cfg = config("services.social.{$this->platform()}", []);

        return filled($cfg['client_id'] ?? null) && filled($cfg['client_secret'] ?? null);
    }

    /** Is the account connected with a usable, unexpired token? */
    protected function accountReady(?SocialAccount $account): bool
    {
        return $account && $account->isConnected() && ! $account->isExpired();
    }

    /** Public absolute URLs for any attached media (platforms fetch by URL). */
    protected function mediaUrls(SocialPost $post): array
    {
        return collect($post->media ?? [])
            ->map(fn ($path) => str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path))
            ->values()
            ->all();
    }

    public function publish(SocialPost $post, ?SocialAccount $account): PublishResult
    {
        if (! $this->appConfigured()) {
            return PublishResult::notConnected($this->platform());
        }

        if (! $this->accountReady($account)) {
            $label = SocialAccount::label($this->platform());

            return PublishResult::notConnected(
                $this->platform(),
                $account && $account->isExpired()
                    ? "The {$label} token has expired - reconnect the account to publish."
                    : "Not connected - connect the {$label} account to publish."
            );
        }

        try {
            return $this->doPublish($post, $account);
        } catch (\Throwable $e) {
            return PublishResult::failed($this->platform(), 'Publish error: '.$e->getMessage());
        }
    }

    /** The real API call. Only reached when app + account are ready. */
    abstract protected function doPublish(SocialPost $post, SocialAccount $account): PublishResult;
}
