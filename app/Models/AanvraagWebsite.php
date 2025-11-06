<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AanvraagWebsite extends Model
{
    use HasFactory;

    protected $table = 'aanvraag_websites';

    protected $fillable = [
        'choice',
        'url',
        'company',
        'description',
        'goal',
        'example1',
        'example2',
        'contactName',
        'contactEmail',
        'contactPhone',
        'visit_id',
        'status',
    ];

    protected $casts = [
        // eventueel later: 'created_at' => 'datetime', etc.
    ];

    public function tasks()
    {
        return $this->hasMany(\App\Models\AanvraagTask::class, 'aanvraag_website_id');
    }
    public function callLogs()
    {
        return $this->hasMany(\App\Models\AanvraagCallLog::class, 'aanvraag_website_id');
    }
    public function statusLogs()
    {
        return $this->hasMany(\App\Models\AanvraagStatusLog::class);
    }
}
