<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoKeywordSnapshot extends Model
{
    use HasFactory;

    protected $fillable = [
        'seo_project_id',
        'seo_keyword_id',
        'snapshot_date',
        'position',
        'url',
        'device',
        'search_engine',
        'serp_features',
        'raw',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'position' => 'integer',
        'serp_features' => 'array',
        'raw' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(SeoProject::class, 'seo_project_id');
    }

    public function keyword(): BelongsTo
    {
        return $this->belongsTo(SeoKeyword::class, 'seo_keyword_id');
    }
}
