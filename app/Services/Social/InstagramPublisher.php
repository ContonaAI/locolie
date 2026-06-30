<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;

/**
 * Publishes to an Instagram Business/Creator account via the Instagram Graph
 * API two-step flow: create a media container, then publish it. Requires an IG
 * user id (meta.ig_user_id) linked to a Facebook Page + a token, plus at least
 * one image - IG feed posts cannot be text-only.
 *
 * Docs: https://developers.facebook.com/docs/instagram-api/guides/content-publishing
 */
class InstagramPublisher extends BasePublisher
{
    protected string $graphVersion = 'v19.0';

    public function platform(): string
    {
        return 'instagram';
    }

    protected function doPublish(SocialPost $post, SocialAccount $account): PublishResult
    {
        $igUserId = $account->meta['ig_user_id'] ?? null;
        if (! $igUserId) {
            return PublishResult::notConnected('instagram', 'No Instagram account linked - reconnect and link a Business account.');
        }

        $media = $this->mediaUrls($post);
        if (empty($media)) {
            return PublishResult::failed('instagram', 'Instagram requires an image - attach media before publishing.');
        }

        $base = "https://graph.facebook.com/{$this->graphVersion}";
        $token = $account->access_token;

        // Step 1: create the media container.
        $container = Http::asForm()->post("{$base}/{$igUserId}/media", [
            'image_url' => $media[0],
            'caption' => $post->body,
            'access_token' => $token,
        ]);

        if ($container->failed() || ! ($creationId = $container->json('id'))) {
            return PublishResult::failed('instagram', $container->json('error.message') ?: 'Could not create the media container.');
        }

        // Step 2: publish the container.
        $publish = Http::asForm()->post("{$base}/{$igUserId}/media_publish", [
            'creation_id' => $creationId,
            'access_token' => $token,
        ]);

        if ($publish->failed() || ! ($id = $publish->json('id'))) {
            return PublishResult::failed('instagram', $publish->json('error.message') ?: 'Could not publish the media container.');
        }

        return PublishResult::posted('instagram', (string) $id, 'Posted to Instagram.');
    }
}
