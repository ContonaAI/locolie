<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;

/**
 * Publishes to a Facebook Page via the Graph API. Posts a photo when media is
 * attached, otherwise a text/link feed post. Requires the Page id + a Page
 * access token (stored in the account: meta.page_id + access_token), which only
 * exist once the Facebook app is approved and the Page is connected via OAuth.
 *
 * Docs: https://developers.facebook.com/docs/pages-api/posts
 */
class FacebookPublisher extends BasePublisher
{
    protected string $graphVersion = 'v19.0';

    public function platform(): string
    {
        return 'facebook';
    }

    protected function doPublish(SocialPost $post, SocialAccount $account): PublishResult
    {
        $pageId = $account->meta['page_id'] ?? null;
        if (! $pageId) {
            return PublishResult::notConnected('facebook', 'No Facebook Page selected - reconnect and pick a Page.');
        }

        $base = "https://graph.facebook.com/{$this->graphVersion}";
        $token = $account->access_token; // Page access token

        $media = $this->mediaUrls($post);

        if (! empty($media)) {
            $resp = Http::asForm()->post("{$base}/{$pageId}/photos", [
                'url' => $media[0],
                'caption' => $post->body,
                'access_token' => $token,
            ]);
        } else {
            $resp = Http::asForm()->post("{$base}/{$pageId}/feed", [
                'message' => $post->body,
                'access_token' => $token,
            ]);
        }

        if ($resp->failed()) {
            return PublishResult::failed('facebook', $resp->json('error.message') ?: 'Graph API rejected the post.');
        }

        $id = $resp->json('post_id') ?: $resp->json('id');

        return PublishResult::posted('facebook', (string) $id, 'Posted to the Facebook Page.');
    }
}
