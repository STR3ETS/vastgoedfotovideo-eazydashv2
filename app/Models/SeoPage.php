<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'seo_project_id',
        'seo_keyword_id',
        'title',
        'slug',
        'planned_url',
        'live_url',
        'goal',
        'status',
        'content_blocks',
        'meta',
        'published_at',
    ];

    protected $casts = [
        'content_blocks' => 'array',
        'meta' => 'array',
        'published_at' => 'datetime',
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
