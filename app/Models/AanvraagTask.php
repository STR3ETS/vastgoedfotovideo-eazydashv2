<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AanvraagTask extends Model
{
    protected $fillable = [
        'aanvraag_website_id',
        'type',
        'title',
        'status',
        'due_at',
    ];

    public function aanvraag()
    {
        return $this->belongsTo(AanvraagWebsite::class, 'aanvraag_website_id');
    }

    public function questions()
    {
        return $this->hasMany(AanvraagTaskQuestion::class);
    }
}
