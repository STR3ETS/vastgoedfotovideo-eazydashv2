<?php

namespace App\Http\Controllers;

use App\Models\AanvraagEmail;
use App\Services\AanvraagEmailMatcher;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InboundAanvraagEmailController extends Controller
{
    /**
     * ✅ Nieuwe primaire mailbox endpoint
     */
    public function info(Request $request, AanvraagEmailMatcher $matcher)
    {
        return $this->handleInbound($request, $matcher, 'info@eazyonline.nl');
    }

    /**
     * (Optioneel) Oude endpoint behouden
     * Handig als je al routes of provider settings had staan.
     * Je kunt dit later verwijderen.
     */
    public function raphael(Request $request, AanvraagEmailMatcher $matcher)
    {
        return $this->handleInbound($request, $matcher, 'raphael@eazyonline.nl');
    }

    /**
     * ---------------------------------------------------
     * Core handler
     * ---------------------------------------------------
     */
    private function handleInbound(Request $request, AanvraagEmailMatcher $matcher, string $mailbox)
    {
        /**
         * Provider-agnostisch inbound endpoint.
         * Verwacht dat een mail-provider (Mailgun/SendGrid/Postmark) een POST doet
         * met email-gegevens. Outlook zelf post dit niet rechtstreeks.
         */

        // ----------------------------
        // 1) RAW INPUTS (flexibel)
        // ----------------------------

        $fromEmail = $request->input('from_email')
            ?? $request->input('from')
            ?? $request->input('sender')
            ?? $request->input('From')
            ?? $request->input('Sender');

        $fromName = $request->input('from_name')
            ?? $request->input('sender_name')
            ?? $request->input('SenderName')
            ?? null;

        $toRaw = $request->input('to')
            ?? $request->input('recipient')
            ?? $request->input('recipients')
            ?? $request->input('To')
            ?? $request->input('to_email')
            ?? [];

        $ccRaw = $request->input('cc')
            ?? $request->input('Cc')
            ?? $request->input('cc_email')
            ?? [];

        $bccRaw = $request->input('bcc')
            ?? $request->input('Bcc')
            ?? $request->input('bcc_email')
            ?? [];

        $subject = $request->input('subject')
            ?? $request->input('Subject')
            ?? null;

        // body keys verschillen per provider
        $text = $request->input('text')
            ?? $request->input('stripped-text')
            ?? $request->input('body_plain')
            ?? $request->input('body-plain')
            ?? null;

        $html = $request->input('html')
            ?? $request->input('stripped-html')
            ?? $request->input('body_html')
            ?? $request->input('body-html')
            ?? null;

        $messageId = $request->input('message_id')
            ?? $request->input('Message-Id')
            ?? $request->input('message-id')
            ?? $request->input('messageId')
            ?? null;

        $inReplyTo = $request->input('in_reply_to')
            ?? $request->input('In-Reply-To')
            ?? null;

        $references = $request->input('references')
            ?? $request->input('References')
            ?? [];

        // ----------------------------
        // 2) NORMALIZED PAYLOAD
        // ----------------------------

        $payload = [
            'mailbox'     => $mailbox,

            'from_email'  => $this->normalizeEmail($fromEmail),
            'from_name'   => $fromName,

            'to'          => $this->normalizeAddressList($toRaw),
            'cc'          => $this->normalizeAddressList($ccRaw),
            'bcc'         => $this->normalizeAddressList($bccRaw),

            'subject'     => $subject,
            'body_text'   => $text,
            'body_html'   => $html,

            'message_id'  => $messageId ? trim((string) $messageId) : null,
            'in_reply_to' => $inReplyTo ? trim((string) $inReplyTo) : null,
            'references'  => is_array($references) ? $references : $this->splitRefs($references),

            'received_at' => now(),
            'raw'         => $request->all(),
        ];

        // ----------------------------
        // 3) MATCH AANVRAAG
        // ----------------------------

        $aanvraag = $matcher->match($payload);

        // ----------------------------
        // 4) SAVE EMAIL
        // ----------------------------

        $email = AanvraagEmail::create([
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

        return response()->json([
            'success'     => true,
            'aanvraag_id' => $email->aanvraag_id,
            'mailbox'     => $payload['mailbox'],
        ]);
    }

    // ---------------------------------------------------
    // Helpers
    // ---------------------------------------------------

    private function normalizeEmail($value): ?string
    {
        if (!$value) return null;

        // value kan ook array/object zijn van provider
        if (is_array($value)) {
            $value = $value['email'] ?? $value['address'] ?? null;
            if (!$value) return null;
        }

        $value = trim((string) $value);

        // Als "Naam <mail@...>"
        if (Str::contains($value, '<') && Str::contains($value, '>')) {
            $value = Str::between($value, '<', '>');
        }

        $value = strtolower(trim($value));

        return filter_var($value, FILTER_VALIDATE_EMAIL) ? $value : null;
    }

    private function normalizeAddressList($raw): array
    {
        if (!$raw) return [];

        // 1) string: "a@x.nl, b@y.nl"
        if (is_string($raw)) {
            $parts = preg_split('/,/', $raw);
        }
        // 2) array: kan strings of objects bevatten
        elseif (is_array($raw)) {
            $parts = $raw;
        }
        else {
            $parts = [];
        }

        $out = [];

        foreach ($parts as $item) {
            $email = $this->extractEmailFromAddressItem($item);
            if ($email) $out[] = $email;
        }

        return array_values(array_unique($out));
    }

    private function extractEmailFromAddressItem($item): ?string
    {
        /**
         * Ondersteunt:
         * - "Naam <mail@...>"
         * - "mail@..."
         * - ['email' => 'mail@...']
         * - ['address' => 'mail@...']
         * - ['name' => 'X', 'email' => 'mail@...']
         */
        if (is_array($item)) {
            $candidate = $item['email'] ?? $item['address'] ?? null;
            return $this->normalizeEmail($candidate);
        }

        return $this->normalizeEmail($item);
    }

    private function splitRefs($value): array
    {
        if (!$value) return [];
        if (is_array($value)) return $value;

        $str = trim((string) $value);
        if ($str === '') return [];

        // References kan space-separated message-ids bevatten
        return array_values(array_filter(preg_split('/\s+/', $str)));
    }
}