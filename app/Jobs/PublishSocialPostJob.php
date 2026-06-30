<?php

namespace App\Jobs;

use App\Models\SocialPost;
use App\Services\Social\SocialPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Publishes a scheduled social post in the background. Dispatched with a delay
 * until scheduled_at when a post is scheduled, or immediately for a "publish
 * now". The actual API calls live in SocialPublisher; this just guards the
 * post state and delegates. Publishing is ready but only goes live once the
 * platform developer apps are approved - until then every adapter returns a
 * clear "not connected" result and the post is left for retry.
 */
class PublishSocialPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(public int $postId) {}

    public function handle(SocialPublisher $publisher): void
    {
        $post = SocialPost::find($this->postId);
        if (! $post || $post->status === 'posted') {
            return; // removed or already published
        }

        $publisher->publish($post);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[social] publish job failed', ['post' => $this->postId, 'error' => $e->getMessage()]);
        SocialPost::where('id', $this->postId)->update(['status' => 'failed', 'error' => $e->getMessage()]);
    }
}
