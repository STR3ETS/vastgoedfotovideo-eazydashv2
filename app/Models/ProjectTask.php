<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'completed_at', // ✅ toevoegen
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
}
