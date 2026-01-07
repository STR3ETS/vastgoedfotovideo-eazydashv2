<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFinanceItem extends Model
{
    protected $fillable = [
        'project_id','description','unit_price_cents','quantity','unit','total_cents'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public static function booted(): void
    {
        static::saving(function (self $item) {
            $item->total_cents = ((int)$item->unit_price_cents) * ((int)$item->quantity);
        });
    }
}
