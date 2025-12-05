<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class MicrosoftGraphClient
{
    protected function tenantId(): string
    {
        return (string) config('services.m365.tenant_id');
    }

    protected function clientId(): string
    {
        return (string) config('services.m365.client_id');
    }

    protected function clientSecret(): string
    {
        return (string) config('services.m365.client_secret');
    }

    /**
     * Haal een application token op (client_credentials).
     */
    public function token(): string
    {
        return Cache::remember('m365_graph_token', 50 * 60, function () {
            $tenant = $this->tenantId();

            $res = Http::asForm()->post(
                "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token",
                [
                    'client_id'     => $this->clientId(),
                    'client_secret' => $this->clientSecret(),
                    'grant_type'    => 'client_credentials',
                    'scope'         => 'https://graph.microsoft.com/.default',
                ]
            );

            if (!$res->ok()) {
                throw new \RuntimeException('M365 token ophalen mislukt: ' . $res->body());
            }

            $token = $res->json('access_token');

            if (!$token) {
                throw new \RuntimeException('M365 token ontbreekt in response.');
            }

            return $token;
        });
    }

    public function get(string $url, array $query = []): array
    {
        $url = ltrim($url, '/');

        $res = Http::withToken($this->token())
            ->acceptJson()
            ->get('https://graph.microsoft.com/v1.0/' . $url, $query);

        if (!$res->ok()) {
            throw new \RuntimeException("Graph GET {$url} mislukt: " . $res->body());
        }

        return (array) $res->json();
    }

    public function post(string $url, array $payload = []): array
    {
        $url = ltrim($url, '/');

        $res = Http::withToken($this->token())
            ->acceptJson()
            ->post('https://graph.microsoft.com/v1.0/' . $url, $payload);

        if (!$res->ok()) {
            throw new \RuntimeException("Graph POST {$url} mislukt: " . $res->body());
        }

        return (array) $res->json();
    }
}
