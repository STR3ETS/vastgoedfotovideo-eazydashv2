<?php

namespace App\Console\Commands;

use App\Services\MicrosoftGraphClient;
use Illuminate\Console\Command;

class M365CreateInfoSubscription extends Command
{
    protected $signature = 'm365:subscribe-info';
    protected $description = 'Maak Microsoft Graph subscription voor info@ inbox';

    public function handle(MicrosoftGraphClient $graph)
    {
        $mailbox = (string) config('services.m365.mailbox', 'info@eazyonline.nl');

        $baseUrl = rtrim((string) config('app.url'), '/');
        $notificationUrl = $baseUrl . '/api/webhooks/m365/info-messages';

        // Secret in query zodat Graph calls automatisch geverifieerd kunnen worden
        $secret = (string) config('services.m365.webhook_secret');
        if ($secret !== '') {
            $notificationUrl .= '?secret=' . urlencode($secret);
        }

        /**
         * Let op: Graph subscriptions verlopen.
         * We zetten 'm hier bewust op korte veilige duur.
         * Renewen doen we via scheduler.
         */
        $expires = now()->addDays(2)->toIso8601String();

        $payload = [
            'changeType' => 'created',
            'notificationUrl' => $notificationUrl,
            'resource' => "users/{$mailbox}/mailFolders('Inbox')/messages",
            'expirationDateTime' => $expires,
            'clientState' => $secret !== '' ? $secret : 'info-mailbox',
        ];

        $res = $graph->post('subscriptions', $payload);

        $this->info('Subscription gemaakt!');
        $this->line('ID: ' . ($res['id'] ?? 'n/a'));
        $this->line('Expires: ' . ($res['expirationDateTime'] ?? 'n/a'));
        $this->line('Notification URL: ' . $notificationUrl);

        return self::SUCCESS;
    }
}
