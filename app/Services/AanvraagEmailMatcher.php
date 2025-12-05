<?php

namespace App\Services;

use App\Models\AanvraagEmail;
use App\Models\AanvraagWebsite;
use Illuminate\Support\Str;

class AanvraagEmailMatcher
{
    public function match(array $p): ?AanvraagWebsite
    {
        $from = strtolower($p['from_email'] ?? '');
        $to   = $p['to'] ?? [];

        $inReplyTo  = $p['in_reply_to'] ?? null;
        $references = $p['references'] ?? [];

        // 1) Future-proof: aanvraag+UUID@...
        $token = $this->extractTokenFromTo($to);
        if ($token) {
            $hit = AanvraagWebsite::where('uuid', $token)->first();
            if ($hit) return $hit;
        }

        // 2) Thread
        if ($inReplyTo) {
            $prev = AanvraagEmail::where('message_id', $inReplyTo)->first();
            if ($prev?->aanvraag_id) return $prev->aanvraag;
        }

        if (!empty($references)) {
            $prev = AanvraagEmail::whereIn('message_id', $references)->first();
            if ($prev?->aanvraag_id) return $prev->aanvraag;
        }

        // 3) Exact match op jullie veld
        if ($from) {
            $hit = AanvraagWebsite::where('contactEmail', $from)->latest()->first();
            if ($hit) return $hit;
        }

        // 4) Domein match met safety
        $fromDomain = $this->domain($from);
        if ($fromDomain) {
            $candidates = AanvraagWebsite::query()
                ->whereNotNull('contactEmail')
                ->get()
                ->filter(fn ($a) => $this->domain(strtolower($a->contactEmail)) === $fromDomain)
                ->values();

            if ($candidates->count() === 1) {
                return $candidates->first();
            }
        }

        return null;
    }

    private function extractTokenFromTo(array $toList): ?string
    {
        foreach ($toList as $addr) {
            $addr = strtolower(trim($addr));

            if (Str::contains($addr, 'aanvraag+')) {
                $local = Str::before($addr, '@');
                $token = Str::after($local, 'aanvraag+');

                if (preg_match('/^[0-9a-f\-]{16,64}$/i', $token)) {
                    return $token;
                }
            }
        }
        return null;
    }

    private function domain(?string $email): ?string
    {
        if (!$email || !str_contains($email, '@')) return null;
        return strtolower(trim(substr(strrchr($email, "@"), 1)));
    }
}