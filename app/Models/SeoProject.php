<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'domain',
        'extra_domains',
        'regions',
        'business_goals',
        'primary_keywords',
        'main_pages',
        'seranking_project_id',
        'health_overall',
        'health_technical',
        'health_content',
        'health_authority',
        'last_audit_id',
        'last_synced_at',
    ];

    protected $casts = [
        'extra_domains'    => 'array',
        'regions'          => 'array',
        'business_goals'   => 'array',
        'primary_keywords' => 'array',
        'main_pages'       => 'array',
        'last_synced_at'   => 'datetime',
    ];

    /**
     * De company waarvoor dit SEO project wordt uitgevoerd.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Alle audits die bij dit project horen.
     * SeoAudit heb je al of maken we in een volgende stap strak af.
     */
    public function audits(): HasMany
    {
        return $this->hasMany(SeoAudit::class);
    }

    /**
     * De laatst gekoppelde audit.
     */
    public function lastAudit(): BelongsTo
    {
        return $this->belongsTo(SeoAudit::class, 'last_audit_id');
    }

    /**
     * Alle SEO taken voor dit project.
     * SeoTask maken we later met een aparte migratie.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(SeoTask::class);
    }

    /**
     * Keyword snapshots in de tijd voor dit project.
     * SeoKeywordSnapshot maken we ook later.
     */
    public function keywordSnapshots(): HasMany
    {
        return $this->hasMany(SeoKeywordSnapshot::class);
    }
}
