<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTaskSubtask extends Model
{
    protected $fillable = [
        'project_task_id',
        'name',
        'status',
        'assigned_user_id',
        'due_date',
        'completed_at',
        'sort_order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProjectTaskSubtask $subtask) {
            if (!$subtask->isDirty('status')) return;

            $new = strtolower((string) ($subtask->status ?? ''));
            $old = strtolower((string) ($subtask->getOriginal('status') ?? ''));

            if ($new === 'done' && $subtask->completed_at === null) {
                $subtask->completed_at = now();
            }

            if ($old === 'done' && $new !== 'done') {
                $subtask->completed_at = null;
            }
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
