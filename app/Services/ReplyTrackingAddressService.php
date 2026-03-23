<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CampaignRecipient;

class ReplyTrackingAddressService
{
    public function isEnabled(): bool
    {
        return (bool) config('mailzen.reply_tracking.enabled', false);
    }

    protected function trackingDomainForCampaign(Campaign $campaign): ?string
    {
        $campaign->loadMissing('replyServer');

        $domain = trim((string) ($campaign->replyServer?->reply_domain ?? ''));
        if ($domain !== '') {
            return $domain;
        }

        $domain = trim((string) (config('mailzen.reply_tracking.reply_domain') ?? ''));

        return $domain !== '' ? $domain : null;
    }

    public function trackingAddressForRecipient(CampaignRecipient $recipient): ?string
    {
        if (!$this->isEnabled()) {
            return null;
        }

        $recipient->loadMissing('campaign.replyServer');

        $campaign = $recipient->campaign;
        if (!$campaign) {
            return null;
        }

        $domain = $this->trackingDomainForCampaign($campaign);
        if (!is_string($domain) || trim($domain) === '') {
            return null;
        }

        $uuid = (string) ($recipient->uuid ?? '');
        $uuid = trim($uuid);

        if ($uuid === '') {
            return null;
        }

        return 'reply+' . $uuid . '@' . $domain;
    }

    public function effectiveReplyTo(Campaign $campaign, ?CampaignRecipient $recipient = null, ?string $fallbackFromEmail = null): string
    {
        $fallbackFromEmail = (string) ($fallbackFromEmail ?? '');
        $campaignReplyTo = (string) ($campaign->reply_to ?? '');

        if ($recipient) {
            $tracking = $this->trackingAddressForRecipient($recipient);
            if (is_string($tracking) && $tracking !== '') {
                return $tracking;
            }
        }

        if ($campaignReplyTo !== '') {
            return $campaignReplyTo;
        }

        // Check if reply server is configured
        $campaign->loadMissing('replyServer');
        $replyServer = $campaign->replyServer;
        
        // If reply server is not configured, return empty string
        if (!$replyServer || !$replyServer->isActive()) {
            return '';
        }

        // Use reply server username as reply-to email if available
        $replyServerEmail = trim((string) ($replyServer->username ?? ''));
        if ($replyServerEmail !== '') {
            return $replyServerEmail;
        }

        // If reply server is configured but no username, use from email
        return $fallbackFromEmail;
    }
}
