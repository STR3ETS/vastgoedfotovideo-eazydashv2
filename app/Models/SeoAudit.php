<?php

namespace App\Models;

use App\Models\Company;
use App\Models\User;
use App\Models\SeoAuditResult;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'domain',
        'type',
        'status',
        'overall_score',
        'meta',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'meta'        => 'array',
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    // Relaties
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(SeoAuditResult::class);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function markRunning(): void
    {
        $this->status     = 'running';
        $this->started_at = now();
        $this->save();
    }

    public function markCompleted(?int $overallScore = null): void
    {
        $this->status       = 'completed';
        $this->overall_score = $overallScore;
        $this->finished_at  = now();
        $this->save();
    }

    public function markFailed(string $reason = null): void
    {
        $meta = $this->meta ?? [];
        if ($reason) {
            $meta['error'] = $reason;
        }

        $this->status      = 'failed';
        $this->meta        = $meta;
        $this->finished_at = now();
        $this->save();
    }

    // Scope om makkelijk per company te filteren
    public function scopeForCompany($query, Company $company)
    {
        return $query->where('company_id', $company->id);
    }
}
