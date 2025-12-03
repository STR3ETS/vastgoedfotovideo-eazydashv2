<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoAudit extends Model
{
    use HasFactory;

    /**
     * We houden dit expres open ($guarded = []) zodat we flexibel blijven
     * terwijl we de definitieve kolommen + migratie nog uitwerken.
     *
     * In de migratie (volgende stap) kun je denken aan kolommen als:
     * - seo_project_id
     * - source (bv. 'seranking', 'mcp', 'manual')
     * - status (bv. 'pending', 'running', 'completed', 'failed')
     * - score_overall, score_technical, score_content, score_authority
     * - started_at, finished_at
     * - raw_summary (korte samenvatting voor UI)
     * - raw_data (volledige ruwe payload)
     */
    protected $guarded = [];

    protected $casts = [
        'started_at'   => 'datetime',
        'finished_at'  => 'datetime',
        'raw_summary'  => 'array',
        'raw_data'     => 'array',
        'score_overall'    => 'integer',
        'score_technical'  => 'integer',
        'score_content'    => 'integer',
        'score_authority'  => 'integer',
    ];

    /**
     * Het SEO project waar deze audit bij hoort.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(SeoProject::class, 'seo_project_id');
    }

    /**
     * Alle gevonden issues / resultaten binnen deze audit.
     * Dit sluit aan op jouw bestaande SeoAuditResult model.
     */
    public function results(): HasMany
    {
        return $this->hasMany(SeoAuditResult::class, 'seo_audit_id');
    }

    /**
     * Eventuele SEO taken die voortkomen uit deze audit.
     * (SeoTask maken we later, net als bij SeoProject.)
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(SeoTask::class, 'seo_audit_id');
    }

    /**
     * Helper: is deze audit al klaar?
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Helper: is deze audit mislukt?
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Helper: is deze audit nu nog bezig?
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }
}
