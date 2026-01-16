<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPlanningItem extends Model
{
    protected $fillable = [
        'project_id',
        'notes',
        'start_at',
        'end_at',
        'location',
        'location_lat',
        'location_lng',
        'location_geocoded_at',
        'assignee_user_id',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'location_geocoded_at' => 'datetime',
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_user_id');
    }
}
