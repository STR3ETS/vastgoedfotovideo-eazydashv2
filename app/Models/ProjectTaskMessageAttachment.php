<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTaskMessageAttachment extends Model
{
    protected $fillable = [
        'project_task_message_id',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function message(): BelongsTo
    {
        return $this->belongsTo(ProjectTaskMessage::class, 'project_task_message_id');
    }
}
