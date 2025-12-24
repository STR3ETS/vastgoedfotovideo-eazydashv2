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

    public int $timeout = 600;
    public int $tries = 3;

    public function __construct(public SeoAudit $audit)
    {
        $this->onQueue('default');
    }

    public function handle(SeRankingClient $client, SeoAuditInsightsService $insights): void
    {
        $audit = $this->audit->fresh();

        if (! $audit) {
            return;
        }

        // Als al klaar of failed, niet opnieuw.
        if (! $audit->isPending() && ! $audit->isRunning()) {
            return;
        }

        $audit->markRunning();

        $meta = $audit->meta ?? [];

        // 1) Settings bepalen
        $defaultSettings = config('seranking.default_audit_settings', []);
        if (!is_array($defaultSettings)) {
            $defaultSettings = [];
        }

        $auditSettings = $meta['settings'] ?? [];
        if (!is_array($auditSettings)) {
            $auditSettings = [];
        }

        $settings = array_merge($defaultSettings, $auditSettings);

        $meta['settings'] = $settings;
        $audit->meta = $meta;
        $audit->save();

        // 2) Audit starten
        $title = $audit->company?->name ?? ($audit->domain ?? 'Website audit');

        $response = $client->createStandardAudit(
            (string) $audit->domain,
            $settings,
            $title
        );

        $externalId = (int) ($response['id'] ?? 0);

        if (! $externalId) {
            $meta['seranking']['start_error'] = $response;
            $audit->meta = $meta;
            $audit->save();

            $audit->markFailed('SERanking gaf geen geldige audit ID terug.');
            return;
        }

        $meta['seranking']['external_audit_id'] = $externalId;
        $audit->remote_audit_id = $externalId;
        $audit->meta = $meta;
        $audit->save();

        // 3) Status pollen
        $statusData  = null;
        $maxAttempts = 15;
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
                $meta = $audit->fresh()?->meta ?? $meta;
                $meta['seranking']['status'] = $statusData;

                $audit->meta = $meta;
                $audit->save();

                $audit->markFailed('Audit afgebroken door SERanking (status: ' . $status . ').');
                return;
            }
        }

        if (! $finished) {
            $meta = $audit->fresh()?->meta ?? $meta;
            $meta['seranking']['status'] = $statusData;

            $audit->meta = $meta;
            $audit->save();

            $audit->markFailed('Audit niet binnen de tijd afgerond.');
            return;
        }

        // 4) Rapport ophalen
        $report = $client->getAuditReport($externalId);

        $meta = $audit->fresh()?->meta ?? $meta;
        $meta['seranking']['status'] = $statusData;
        $meta['seranking']['report'] = $report ?? null;

        $audit->raw_data = is_array($report) ? $report : null;
        $audit->meta = $meta;
        $audit->save();

        // 5) Issues + insights opslaan (maakt ook seo_audit_results records)
        if (is_array($report)) {
            $insights->storeResultsFromReport($audit, $report);
        }

        // 6) Score bepalen en afronden
        $score = null;

        if (is_array($report) && isset($report['score_percent'])) {
            $score = (int) $report['score_percent'];
        } elseif (is_array($report) && isset($report['score'])) {
            $score = (int) $report['score'];
        }

        $audit->markCompleted($score);
    }

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
        $audit->save();

        $audit->markFailed($reason);
    }
}
