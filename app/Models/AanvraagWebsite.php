<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AanvraagWebsite extends Model
{
    use HasFactory;

    protected $table = 'aanvraag_websites';

    protected $fillable = [
        'owner_id',
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
        'intake_at',
        'intake_duration',
        'intake_done',
        'intake_completed_at',
        'ai_summary',
    ];

    protected $casts = [
        'intake_at'           => 'datetime',
        'intake_done'         => 'boolean',
        'intake_completed_at' => 'datetime',
    ];

    /**
     * ⚠️ BELANGRIJK:
     * We maken tasks nu expliciet in de AanvraagController.
     * Dus geen auto-create via model events meer,
     * anders krijg je dubbele/rare task_id's.
     */

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

    public function files()
    {
        return $this->hasMany(\App\Models\AanvraagFile::class, 'aanvraag_website_id');
    }

    public function project()
    {
        return $this->hasOne(\App\Models\Project::class, 'aanvraag_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function emails()
    {
        return $this->hasMany(\App\Models\AanvraagEmail::class, 'aanvraag_id')
            ->latest('received_at')
            ->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(\App\Models\AanvraagComment::class, 'aanvraag_website_id');
    }
}