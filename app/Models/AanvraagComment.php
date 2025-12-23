<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AanvraagComment extends Model
{
    protected $fillable = [
        'aanvraag_website_id',
        'user_id',
        'parent_id',
        'body',
    ];

    public function aanvraag(): BelongsTo
    {
        return $this->belongsTo(AanvraagWebsite::class, 'aanvraag_website_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('id');
    }
}
