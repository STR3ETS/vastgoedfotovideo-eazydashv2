<?php

namespace App\Console\Commands;

use App\Services\MicrosoftGraphClient;
use Illuminate\Console\Command;

class M365CreateInfoSubscription extends Command
{
    protected $signature = 'm365:subscribe-info';
    protected $description = 'Maak Microsoft Graph subscription voor info@ inbox';

    public function handle(\App\Services\MicrosoftGraphClient $graph)
    {
        try {
            $mailbox = config('services.m365.mailbox', 'info@eazyonline.nl');

            $notificationUrl = rtrim(config('app.url'), '/') . '/api/webhooks/m365/info-messages';

            $resource = "users/{$mailbox}/mailFolders('Inbox')/messages";

            $payload = [
                'changeType' => 'created',
                'notificationUrl' => $notificationUrl,
                'resource' => $resource,
                'expirationDateTime' => now()->addMinutes(55)->toIso8601String(),
                'clientState' => config('services.m365.webhook_secret'),
            ];

            $this->info('[M365] Creating subscription...');
            $this->line('[M365] Mailbox: ' . $mailbox);
            $this->line('[M365] Resource: ' . $resource);
            $this->line('[M365] Notification: ' . $notificationUrl);

            $res = $graph->post('subscriptions', $payload);

            $this->info('[M365] ✅ Subscription created!');
            $this->line('ID: ' . ($res['id'] ?? '(no id returned)'));
            $this->line('Expires: ' . ($res['expirationDateTime'] ?? '(no expiration returned)'));

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('[M365] ❌ Failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
