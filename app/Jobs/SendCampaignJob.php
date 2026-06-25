<?php

namespace App\Jobs;

use App\Models\Business;
use App\Models\Campaign;
use App\Services\Messaging\MessagingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Delivers a campaign in the background so large or scheduled sends never block
 * the request. The audience is resolved and consent-filtered before the job is
 * queued; this just performs delivery and updates the campaign log.
 */
class SendCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function __construct(
        public int $campaignId,
        public string $channelKey,
        public array $message,
        public array $recipients,
        public ?int $brandId = null,
    ) {}

    public function handle(MessagingService $messaging): void
    {
        $campaign = Campaign::find($this->campaignId);
        if (! $campaign || $campaign->status === 'sent') {
            return; // already handled or removed
        }

        $campaign->update(['status' => 'sending']);
        $brand = $this->brandId ? Business::find($this->brandId) : null;

        $messaging->deliver($campaign, $this->channelKey, $this->message, $this->recipients, $brand);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('[campaign] job failed', ['campaign' => $this->campaignId, 'error' => $e->getMessage()]);
        Campaign::where('id', $this->campaignId)->update(['status' => 'failed']);
    }
}
