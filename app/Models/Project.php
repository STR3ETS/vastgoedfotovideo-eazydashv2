<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\AanvraagWebsite;
use App\Models\ProjectPreviewView;
use App\Models\ProjectTask;
use App\Models\ProjectCallLog;
use App\Models\Offerte;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'aanvraag_id',
        'status',
        'company',
        'contact_name',
        'contact_email',
        'contact_phone',
        'preview_url',
        'preview_token',
        'preview_first_viewed_at',
        'preview_expires_at',
    ];

    protected $casts = [
        'preview_first_viewed_at' => 'datetime',
        'preview_expires_at'      => 'datetime',
        'preview_approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Project $project) {
            if (empty($project->preview_token)) {
                $project->preview_token = static::generatePreviewToken();
            }
        });
    }

    public static function generatePreviewToken(): string
    {
        do {
            $token = Str::random(20);
        } while (static::where('preview_token', $token)->exists());

        return $token;
    }

    public function aanvraag()
    {
        return $this->belongsTo(AanvraagWebsite::class, 'aanvraag_id');
    }

    public function previewViews()
    {
        return $this->hasMany(ProjectPreviewView::class);
    }

    public function previewFeedbacks()
    {
        return $this->hasMany(ProjectPreviewFeedback::class);
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class);
    }

    public function callLogs()
    {
        return $this->hasMany(ProjectCallLog::class);
    }

    public function offerte()
    {
        return $this->hasOne(Offerte::class);
    }
}
