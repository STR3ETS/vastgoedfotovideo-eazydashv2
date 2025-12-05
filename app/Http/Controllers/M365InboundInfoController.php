<?php

namespace App\Http\Controllers;

use App\Models\AanvraagEmail;
use App\Services\AanvraagEmailMatcher;
use App\Services\MicrosoftGraphClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class M365InboundInfoController extends Controller
{
    public function handle(Request $request, MicrosoftGraphClient $graph, AanvraagEmailMatcher $matcher)
    {
        /**
         * 1) Microsoft Graph validation handshake
         * Graph verwacht dat je exact de validationToken terugstuurt als plain text.
         */
        if ($request->query('validationToken')) {
            return response($request->query('validationToken'), 200)
                ->header('Content-Type', 'text/plain');
        }

        $mailbox = (string) config('services.m365.mailbox', 'info@eazyonline.nl');

        $items = $request->input('value', []);
        if (!is_array($items) || empty($items)) {
            return response()->json(['success' => true]);
        }

        foreach ($items as $n) {
            $resource = $n['resource'] ?? null;
            if (!$resource || !str_contains($resource, '/messages/')) {
                continue;
            }

            // resource voorbeeld:
            // /users/{id}/mailFolders('Inbox')/messages/{messageId}
            $messageId = substr($resource, strrpos($resource, '/messages/') + 10);
            if (!$messageId) {
                continue;
            }

            // 2) Haal volledige mail op uit info@
            $msg = $graph->get("users/{$mailbox}/messages/{$messageId}", [
                '$select' => implode(',', [
                    'id',
                    'subject',
                    'from',
                    'sender',
                    'toRecipients',
                    'ccRecipients',
                    'bccRecipients',
                    'body',
                    'bodyPreview',
                    'receivedDateTime',
                    'internetMessageId',
                    'conversationId',
                ]),
            ]);

            $fromEmail = data_get($msg, 'from.emailAddress.address');
            $fromName  = data_get($msg, 'from.emailAddress.name');

            $to = $this->pluckRecipients($msg['toRecipients'] ?? []);
            $cc = $this->pluckRecipients($msg['ccRecipients'] ?? []);
            $bcc = $this->pluckRecipients($msg['bccRecipients'] ?? []);

            $body = $msg['body'] ?? [];
            $contentType = strtolower((string) ($body['contentType'] ?? ''));
            $content     = $body['content'] ?? null;

            $internetId = $msg['internetMessageId'] ?? null;

            // 3) Voorkom dubbelingen (heel belangrijk)
            if ($internetId) {
                $exists = AanvraagEmail::where('message_id', $internetId)->exists();
                if ($exists) {
                    continue;
                }
            }

            // 4) Bouw payload voor jouw bestaande matcher
            $payload = [
                'mailbox'     => $mailbox,

                'from_email'  => $this->normalizeEmail($fromEmail),
                'from_name'   => $fromName,

                'to'          => $this->normalizeList($to),
                'cc'          => $this->normalizeList($cc),
                'bcc'         => $this->normalizeList($bcc),

                'subject'     => $msg['subject'] ?? null,

                'body_text'   => $contentType === 'text'
                    ? $content
                    : ($msg['bodyPreview'] ?? null),

                'body_html'   => $contentType === 'html'
                    ? $content
                    : null,

                'message_id'  => $internetId,
                'in_reply_to' => null,
                'references'  => [],

                'received_at' => !empty($msg['receivedDateTime'])
                    ? now()->parse($msg['receivedDateTime'])
                    : now(),

                'raw'         => $msg,
            ];

            // 5) Match AanvraagWebsite
            $aanvraag = $matcher->match($payload);

            // 6) Opslaan
            AanvraagEmail::create([
                'aanvraag_id' => $aanvraag?->id,
                'direction'   => 'inbound',
                'mailbox'     => $payload['mailbox'],

                'from_email'  => $payload['from_email'],
                'from_name'   => $payload['from_name'],

                'to'          => $payload['to'],
                'cc'          => $payload['cc'],
                'bcc'         => $payload['bcc'],

                'subject'     => $payload['subject'],
                'body_text'   => $payload['body_text'],
                'body_html'   => $payload['body_html'],

                'message_id'  => $payload['message_id'],
                'in_reply_to' => $payload['in_reply_to'],
                'references'  => $payload['references'],

                'received_at' => $payload['received_at'],
                'raw'         => $payload['raw'],
            ]);
        }

        return response()->json(['success' => true]);
    }

    private function pluckRecipients(array $recipients): array
    {
        $out = [];

        foreach ($recipients as $r) {
            $addr = data_get($r, 'emailAddress.address');
            if ($addr) {
                $out[] = $addr;
            }
        }

        return $out;
    }

    private function normalizeEmail($value): ?string
    {
        if (!$value) return null;

        $value = trim((string) $value);

        // "Naam <mail@...>"
        if (Str::contains($value, '<') && Str::contains($value, '>')) {
            $value = Str::between($value, '<', '>');
        }

        $value = strtolower(trim($value));

        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    private function normalizeList(array $list): array
    {
        $out = [];

        foreach ($list as $v) {
            $email = $this->normalizeEmail($v);
            if ($email) $out[] = $email;
        }

        return array_values(array_unique($out));
    }
}
