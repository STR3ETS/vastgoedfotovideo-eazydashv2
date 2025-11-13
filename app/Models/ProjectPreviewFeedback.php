<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPreviewFeedback extends Model
{
    use HasFactory;

    protected $table = 'project_preview_feedbacks';

    protected $fillable = [
        'project_id',
        'feedback',
    ];

    protected $casts = [
        'is_done' => 'boolean',
        'done_at' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
