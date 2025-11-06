<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AanvraagCallLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'aanvraag_website_id',
        'user_id',
        'outcome',
        'note',
        'called_at',
    ];

    protected $casts = [
        'called_at' => 'datetime',
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