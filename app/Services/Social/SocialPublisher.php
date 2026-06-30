<?php

namespace App\Services\Social;

use App\Models\SocialAccount;
use App\Models\SocialPost;

/**
 * Registry + facade over the per-platform publishers. Resolves an adapter by
 * platform key and publishes a SocialPost across every platform it targets,
 * recording the per-platform outcome and rolling the post up to a single
 * status (posted | failed) for the calendar. Never throws on a missing app or
 * token - those degrade to a "not connected" result per platform.
 */
class SocialPublisher
{
    /** @var array<string,Publisher> */
    protected array $publishers = [];

    public function __construct(
        FacebookPublisher $facebook,
        InstagramPublisher $instagram,
        TikTokPublisher $tiktok,
        LinkedinPublisher $linkedin,
    ) {
        foreach ([$facebook, $instagram, $tiktok, $linkedin] as $p) {
            $this->publishers[$p->platform()] = $p;
        }
    }

    public function publisher(string $platform): Publisher
    {
        return $this->publishers[$platform]
            ?? throw new \InvalidArgumentException("Unknown social platform [$platform]");
    }

    /**
     * Publish a post to all of its target platforms and persist the outcome.
     *
     * @return array<string,PublishResult> keyed by platform
     */
    public function publish(SocialPost $post): array
    {
        $accounts = SocialAccount::whereIn('platform', $post->platforms ?? [])->get()->keyBy('platform');

        /** @var array<string,PublishResult> $results */
        $results = [];
        foreach ($post->platforms ?? [] as $platform) {
            if (! isset($this->publishers[$platform])) {
                continue;
            }
            $results[$platform] = $this->publisher($platform)->publish($post, $accounts->get($platform));
        }

        $this->record($post, $results);

        return $results;
    }

    /** Roll the per-platform results up onto the post row. */
    protected function record(SocialPost $post, array $results): void
    {
        $anyPosted = collect($results)->contains(fn (PublishResult $r) => $r->isPosted());
        $anyFailed = collect($results)->contains(fn (PublishResult $r) => $r->status === 'failed');

        // Posted if at least one platform went live; failed if something errored
        // and nothing went live; otherwise it stays as-is (e.g. all not_connected
        // leaves it scheduled/draft so it can be retried once apps are live).
        $status = $anyPosted ? 'posted' : ($anyFailed ? 'failed' : $post->status);

        $externalId = collect($results)->first(fn (PublishResult $r) => $r->isPosted())?->externalId;
        $error = collect($results)
            ->filter(fn (PublishResult $r) => $r->status !== 'posted')
            ->map(fn (PublishResult $r) => SocialAccount::label($r->platform).': '.$r->note)
            ->implode(' | ');

        $post->update([
            'status' => $status,
            'posted_at' => $anyPosted ? now() : $post->posted_at,
            'external_id' => $externalId ?: $post->external_id,
            'error' => $error ?: null,
            'meta' => array_merge($post->meta ?? [], [
                'results' => collect($results)->map(fn (PublishResult $r) => [
                    'status' => $r->status,
                    'external_id' => $r->externalId,
                    'note' => $r->note,
                ])->all(),
            ]),
        ]);
    }
}
