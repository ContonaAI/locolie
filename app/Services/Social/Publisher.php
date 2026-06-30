<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Models\SocialPost;

/** Contract every per-platform publisher implements. */
interface Publisher
{
    /** Platform key: facebook | instagram | tiktok | linkedin. */
    public function platform(): string;

    /**
     * Publish a post to this platform.
     *
     * MUST NOT throw on a missing token / unconfigured app: return
     * PublishResult::notConnected(...) instead so the job can record a clear,
     * actionable status. May throw only on an unexpected runtime error, which
     * the publisher catches and converts to PublishResult::failed(...).
     */
    public function publish(SocialPost $post, ?SocialAccount $account): PublishResult;
}
