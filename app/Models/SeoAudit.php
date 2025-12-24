<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoAudit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',

        'raw_summary' => 'array',
        'raw_data'    => 'array',
        'meta'        => 'array',

        'score_overall'   => 'integer',
        'score_technical' => 'integer',
        'score_content'   => 'integer',
        'score_authority' => 'integer',

        'remote_audit_id' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(SeoProject::class, 'seo_project_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(SeoAuditResult::class, 'seo_audit_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(SeoTask::class, 'seo_audit_id');
    }

    /**
     * Compat: sommige services/job gebruiken overall_score, domain en company.
     * We mappen dat naar jouw echte structuur.
     */
    public function getOverallScoreAttribute(): ?int
    {
        return $this->score_overall;
    }

    public function getDomainAttribute(): ?string
    {
        return $this->project?->domain;
    }

    public function getCompanyAttribute()
    {
        return $this->project?->company;
    }

    public function isPending(): bool
    {
        return ($this->status ?? 'pending') === 'pending';
    }

    public function isCompleted(): bool
    {
        return ($this->status ?? '') === 'completed';
    }

    public function isFailed(): bool
    {
        return ($this->status ?? '') === 'failed';
    }

    public function isRunning(): bool
    {
        return ($this->status ?? '') === 'running';
    }

    public function markRunning(): void
    {
        $this->status = 'running';
        $this->started_at = $this->started_at ?? now();
        $this->save();
    }

    public function markFailed(?string $reason = null): void
    {
        $this->status = 'failed';
        $this->finished_at = now();

        $meta = $this->meta ?? [];
        if ($reason) {
            $meta['error'] = $reason;
        }
        $this->meta = $meta;

        $this->save();
    }

    public function markCompleted(?int $overallScore = null): void
    {
        $this->status = 'completed';
        $this->finished_at = now();

        if (!is_null($overallScore)) {
            $this->score_overall = (int) $overallScore;
        }

        $this->save();
    }
}
