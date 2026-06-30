<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Models\SocialPost;
use Illuminate\Support\Facades\Http;

/**
 * Publishes a text share to LinkedIn via the UGC Posts API. Posts as the
 * connected author URN (meta.author_urn, e.g. an organization or person URN).
 * Requires an approved LinkedIn app with the w_member_social / w_organization_social
 * scope + a member access token. Image upload is a separate register-then-upload
 * dance; this scaffold publishes the copy and notes media as a follow-up.
 *
 * Docs: https://learn.microsoft.com/linkedin/marketing/integrations/community-management/shares/ugc-post-api
 */
class LinkedinPublisher extends BasePublisher
{
    public function platform(): string
    {
        return 'linkedin';
    }

    protected function doPublish(SocialPost $post, SocialAccount $account): PublishResult
    {
        $authorUrn = $account->meta['author_urn'] ?? null;
        if (! $authorUrn) {
            return PublishResult::notConnected('linkedin', 'No LinkedIn author selected - reconnect and pick a Page or profile.');
        }

        $resp = Http::withToken($account->access_token)
            ->withHeaders(['X-Restli-Protocol-Version' => '2.0.0'])
            ->asJson()
            ->post('https://api.linkedin.com/v2/ugcPosts', [
                'author' => $authorUrn,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => ['text' => $post->body],
                        'shareMediaCategory' => 'NONE',
                    ],
                ],
                'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'],
            ]);

        if ($resp->failed()) {
            return PublishResult::failed('linkedin', $resp->json('message') ?: 'LinkedIn rejected the post.');
        }

        $id = $resp->header('x-restli-id') ?: $resp->json('id');

        return PublishResult::posted('linkedin', (string) $id, 'Posted to LinkedIn.');
    }
}
