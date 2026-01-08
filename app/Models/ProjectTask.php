<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class ProjectTask extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'due_date',
        'location',
        'assigned_user_id',
        'status',
        'sort_order',
        'completed_at',
        'description',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProjectTask $task) {
            if (!$task->isDirty('status')) {
                return;
            }

            $new = strtolower((string) ($task->status ?? ''));
            $old = strtolower((string) ($task->getOriginal('status') ?? ''));

            // status -> done = timestamp zetten
            if ($new === 'done' && $task->completed_at === null) {
                $task->completed_at = now();
            }

            // van done af = timestamp leegmaken
            if ($old === 'done' && $new !== 'done') {
                $task->completed_at = null;
            }
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProjectTaskLog::class, 'project_task_id')->latest();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ProjectTaskMessage::class, 'project_task_id')->latest();
    }

    public function attachments(): HasManyThrough
    {
        return $this->hasManyThrough(
            ProjectTaskMessageAttachment::class,
            ProjectTaskMessage::class,
            'project_task_id',          // FK op messages tabel
            'project_task_message_id',  // FK op attachments tabel
            'id',                       // local key op tasks
            'id'                        // local key op messages
        );
    }

    public function subtasks()
    {
        return $this->hasMany(ProjectTaskSubtask::class, 'project_task_id')->orderBy('sort_order')->orderBy('id');
    }
}
