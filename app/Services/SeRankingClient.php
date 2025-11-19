<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SeRankingClient
{
    protected string $baseUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('seranking.base_url'), '/');
        $this->apiKey  = (string) config('seranking.api_key', '');
    }

    protected function http()
    {
        return Http::withHeaders([
            // Belangrijk: SE Ranking gebruikt "Token", niet "Bearer"
            'Authorization' => 'Token ' . $this->apiKey,
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
        ])->baseUrl($this->baseUrl);
    }

    protected function request(string $method, string $path, array $params = []): array
    {
        $method = strtolower($method);
        $client = $this->http();

        // TEMP LOGGING
        logger()->warning('SERanking request', [
            'method' => strtoupper($method),
            'url'    => $this->baseUrl . $path,
            'params' => $params,
            'apiKey_starts_with' => substr($this->apiKey, 0, 6),
        ]);

        if ($method === 'get') {
            $res = $client->get($path, $params);
        } else {
            $res = $client->{$method}($path, $params);
        }

        if ($res->failed()) {
            logger()->warning('SERanking API error', [
                'method' => strtoupper($method),
                'url'    => $this->baseUrl . $path,
                'status' => $res->status(),
                'body'   => $res->body(),
                'json'   => $res->json(),
                'params' => $params,
                'headers'=> $res->headers(),
            ]);
        }

        $res->throw();

        return $res->json();
    }


    /**
     * Start een standaard Website Audit.
     * Zie: https://api.seranking.com/v1/site-audit/audits/standard
     */
    public function createStandardAudit(string $domain, array $settings = [], ?string $title = null): array
    {
        $payload = [
            'domain' => $domain,
        ];

        if ($title !== null) {
            $payload['title'] = $title;
        }

        if (!empty($settings)) {
            $payload['settings'] = $settings;
        }

        return $this->request('post', '/v1/site-audit/audits/standard', $payload);
    }

    /**
     * Compat wrapper, zodat bestaande code kan blijven werken.
     */
    public function startWebsiteAudit(string $domain, array $options = []): array
    {
        $title    = $options['title']    ?? $domain;
        $settings = $options['settings'] ?? [];

        return $this->createStandardAudit($domain, $settings, $title);
    }

    /**
     * Check status van een audit.
     * GET /v1/site-audit/audits/status?audit_id=...
     */
    public function getAuditStatus(int $auditId): array
    {
        return $this->request('get', '/v1/site-audit/audits/status', [
            'audit_id' => $auditId,
        ]);
    }

    /**
     * Haal het volledige rapport op.
     * GET /v1/site-audit/audits/report?audit_id=...
     */
    public function getAuditReport(int $auditId): array
    {
        return $this->request('get', '/v1/site-audit/audits/report', [
            'audit_id' => $auditId,
        ]);
    }

    /**
     * Compat wrapper met je oude naam.
     */
    public function getWebsiteAudit(string $remoteAuditId): array
    {
        return $this->getAuditReport((int) $remoteAuditId);
    }
}
