<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;

/**
 * Publishes to TikTok via the Content Posting API (PULL_FROM_URL). TikTok is
 * video-first: a publishable post needs a video URL. Requires an approved
 * TikTok developer app with the video.publish scope + a user access token.
 *
 * Docs: https://developers.tiktok.com/doc/content-posting-api-reference-direct-post
 */
class TikTokPublisher extends BasePublisher
{
    public function platform(): string
    {
        return 'tiktok';
    }

    protected function doPublish(SocialPost $post, SocialAccount $account): PublishResult
    {
        $media = $this->mediaUrls($post);
        $video = collect($media)->first(fn ($u) => (bool) preg_match('/\.(mp4|mov|webm)(\?|$)/i', $u));

        if (! $video) {
            return PublishResult::failed('tiktok', 'TikTok requires a video - attach an .mp4/.mov before publishing.');
        }

        $resp = Http::withToken($account->access_token)
            ->asJson()
            ->post('https://open.tiktokapis.com/v2/post/publish/video/init/', [
                'post_info' => [
                    'title' => mb_substr($post->body, 0, 150),
                    'privacy_level' => 'SELF_ONLY', // safest default until review approves PUBLIC
                ],
                'source_info' => [
                    'source' => 'PULL_FROM_URL',
                    'video_url' => $video,
                ],
            ]);

        if ($resp->failed() || $resp->json('error.code', 'ok') !== 'ok') {
            return PublishResult::failed('tiktok', $resp->json('error.message') ?: 'TikTok rejected the publish request.');
        }

        $publishId = $resp->json('data.publish_id');

        return PublishResult::posted('tiktok', (string) $publishId, 'Submitted to TikTok (processing).');
    }
}
