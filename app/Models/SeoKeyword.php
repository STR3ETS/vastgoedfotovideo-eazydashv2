<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeoKeyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'seo_project_id',
        'keyword',
        'is_selected',
        'is_primary',
        'search_volume',
        'difficulty',
        'cpc',
        'competition',
        'intent',
        'reason',
        'seranking_keyword_id',
        'target_url',
        'meta',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
        'is_primary'  => 'boolean',
        'search_volume' => 'integer',
        'difficulty' => 'integer',
        'cpc' => 'decimal:2',
        'competition' => 'decimal:2',
        'meta' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(SeoProject::class, 'seo_project_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(SeoKeywordSnapshot::class, 'seo_keyword_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(SeoPage::class, 'seo_keyword_id');
    }
}
