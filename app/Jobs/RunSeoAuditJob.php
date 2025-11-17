<?php

namespace App\Jobs;

use App\Models\SeoAudit;
use App\Services\SeRankingClient;
use App\Services\SeoAuditInsightsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class RunSeoAuditJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximale looptijd van de job (in seconden).
     */
    public int $timeout = 600; // 10 minuten

    /**
     * Aantal pogingen voordat de job definitief faalt.
     */
    public int $tries = 3;

    /**
     * De audit die uitgevoerd moet worden.
     */
    public function __construct(public SeoAudit $audit)
    {
        // Eventueel aparte queue, nu gewoon default
        $this->onQueue('default');
    }

    /**
     * Voert de audit uit via SERanking en slaat alle resultaten + insights op.
     */
    public function handle(SeRankingClient $client, SeoAuditInsightsService $insights): void
    {
        // Audit altijd vers ophalen (zodat status/meta up-to-date zijn)
        $audit = $this->audit->fresh();

        if (! $audit) {
            return;
        }

        // Als de audit al klaar of gefaald is, niet opnieuw uitvoeren
        if (! $audit->isPending() && ! $audit->isRunning()) {
            return;
        }

        // Markeer als "running"
        $audit->markRunning();

        // Basis meta ophalen
        $meta = $audit->meta ?? [];

        /**
         * 1) Audit settings bepalen
         * ------------------------
         * - Defaults uit config('seranking.default_audit_settings')
         * - Per-audit overrides uit $audit->meta['settings']
         */
        $defaultSettings = config('seranking.default_audit_settings', []);
        if (! is_array($defaultSettings)) {
            $defaultSettings = [];
        }

        $auditSettings = $meta['settings'] ?? [];
        if (! is_array($auditSettings)) {
            $auditSettings = [];
        }

        // Default + audit specifieke settings combineren
        $settings = array_merge($defaultSettings, $auditSettings);

        // Gebruikte settings terugschrijven in meta
        $meta['settings'] = $settings;
        $audit->meta      = $meta;
        $audit->save();

        /**
         * 2) Audit starten bij SERanking
         * ------------------------------
         */
        $title = $audit->company->name ?? $audit->domain;

        $response = $client->createStandardAudit(
            $audit->domain,
            $settings,
            $title
        );

        $externalId = (int) ($response['id'] ?? 0);

        if (! $externalId) {
            // Geen geldige audit ID teruggekregen van SERanking
            $meta['seranking']['start_error'] = $response;
            $audit->meta = $meta;
            $audit->markFailed('SERanking gaf geen geldige audit ID terug.');
            return;
        }

        // SERanking audit ID in meta opslaan
        $meta['seranking']['external_audit_id'] = $externalId;
        $audit->meta = $meta;
        $audit->save();

        /**
         * 3) Status pollen tot audit klaar is
         * -----------------------------------
         */
        $statusData  = null;
        $maxAttempts = 15; // 15 * 10 seconden ≈ 2,5 minuut
        $finished    = false;

        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep(10);

            $statusData = $client->getAuditStatus($externalId);
            $status     = $statusData['status'] ?? null;

            if (in_array($status, ['finished', 'done', 'completed'], true)) {
                $finished = true;
                break;
            }

            if (in_array($status, ['cancelled', 'expired', 'failed'], true)) {
                // SERanking heeft de audit zelf afgebroken
                $meta['seranking']['status'] = $statusData;
                $audit->meta = $meta;
                $audit->markFailed('Audit afgebroken door SERanking (status: ' . $status . ').');
                return;
            }
        }

        if (! $finished) {
            // Niet binnen de tijd afgerond
            $meta['seranking']['status'] = $statusData;
            $audit->meta = $meta;
            $audit->markFailed('Audit niet binnen de tijd afgerond.');
            return;
        }

        /**
         * 4) Volledig rapport ophalen
         * ---------------------------
         */
        $report = $client->getAuditReport($externalId);

        // Rapport en status in meta opslaan
        $meta['seranking']['status'] = $statusData;
        $meta['seranking']['report'] = $report ?? null;

        /**
         * 5) Insights + issue records opslaan
         * -----------------------------------
         */
        if (is_array($report)) {
            // Slaat:
            // - genormaliseerde issues op in seo_audit_results
            // - summary & issues in $audit->meta['insights']
            $insights->storeResultsFromReport($audit, $report);
        }

        /**
         * 6) Score bepalen en audit afronden
         * ----------------------------------
         */
        $score = null;

        if (isset($report['score_percent'])) {
            $score = (int) $report['score_percent'];
        } elseif (isset($report['score'])) {
            $score = (int) $report['score'];
        }

        $audit->meta = $meta;
        $audit->markCompleted($score);
    }

    /**
     * Wordt aangeroepen als de job crasht na alle pogingen.
     */
    public function failed(Throwable $exception): void
    {
        $audit = $this->audit->fresh();

        if (! $audit) {
            return;
        }

        $reason = 'RunSeoAuditJob: ' . $exception->getMessage();

        $meta = $audit->meta ?? [];
        $meta['seranking']['job_error'] = $reason;

        $audit->meta = $meta;
        $audit->markFailed($reason);
    }
}
