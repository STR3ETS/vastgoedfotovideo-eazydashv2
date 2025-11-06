<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AanvraagStatusLog extends Model
{
    protected $fillable = [
        'aanvraag_website_id',
        'user_id',
        'from_status',
        'to_status',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function aanvraag()
    {
        return $this->belongsTo(AanvraagWebsite::class, 'aanvraag_website_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
