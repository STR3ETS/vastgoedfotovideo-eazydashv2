<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\Response;

class Toast
{
    public static function attach(Response $response, string $message, string $type = 'success', array $extra = []): Response
    {
        $payload = array_merge([
            'message' => $message,
            'type'    => $type,
        ], $extra);

        $existing = $response->headers->get('HX-Trigger');
        $triggers = [];

        if (is_string($existing) && $existing !== '') {
            $decoded = json_decode($existing, true);
            if (is_array($decoded)) $triggers = $decoded;
        }

        $triggers['toast'] = $payload;

        $response->headers->set('HX-Trigger', json_encode($triggers, JSON_UNESCAPED_SLASHES));
        return $response;
    }

    public static function flash(string $message, string $type = 'success', array $extra = []): void
    {
        session()->flash('toast', array_merge([
            'message' => $message,
            'type'    => $type,
        ], $extra));
    }

    public static function isHtmx(): bool
    {
        return request()->headers->get('HX-Request') === 'true';
    }
}
