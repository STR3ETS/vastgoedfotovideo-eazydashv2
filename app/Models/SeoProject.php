<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoProject extends Model
{
    use HasFactory;

    /**
     * Mass assignable velden.
     */
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

        // Nieuwe meta velden
        'status',
        'priority',
        'visibility_index',
        'organic_traffic',
        'primary_goal',
        'goal_notes',
        'notes',

        // Health + audit/sync
        'health_overall',
        'health_technical',
        'health_content',
        'health_authority',
        'last_audit_id',
        'last_synced_at',
    ];

    /**
     * Type casting voor JSON & datum/nummer velden.
     */
    protected $casts = [
        'extra_domains'    => 'array',
        'regions'          => 'array',
        'business_goals'   => 'array',
        'primary_keywords' => 'array',
        'main_pages'       => 'array',

        'visibility_index' => 'decimal:2',
        'organic_traffic'  => 'integer',

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
     * SeoAudit werken we in de volgende stappen verder uit.
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
     * SeoTask komt later als aparte tabel.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(SeoTask::class);
    }

    /**
     * Keyword snapshots in de tijd voor dit project.
     * SeoKeywordSnapshot komt later als aparte tabel.
     */
    public function keywordSnapshots(): HasMany
    {
        return $this->hasMany(SeoKeywordSnapshot::class);
    }
}
