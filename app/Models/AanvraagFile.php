<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AanvraagFile extends Model
{
    protected $table = 'aanvraag_files';

    protected $fillable = [
        'aanvraag_website_id',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    protected $appends = ['size_human'];

    public function aanvraag(): BelongsTo
    {
        return $this->belongsTo(AanvraagWebsite::class, 'aanvraag_website_id');
    }

    public function getSizeHumanAttribute(): ?string
    {
        if (!$this->size) return null;

        $bytes = $this->size;
        $units = ['B','kB','MB','GB','TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return sprintf('%.1f %s', $bytes, $units[$i]);
    }
}
