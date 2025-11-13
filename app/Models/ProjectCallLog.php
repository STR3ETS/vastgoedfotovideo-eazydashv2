<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCallLog extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'called_at',
        'outcome',
        'note',
    ];

    protected $casts = [
        'called_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
