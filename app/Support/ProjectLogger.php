<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectLog;
use App\Models\User;

class ProjectLogger
{
    public static function add(Project $project, ?User $user, string $type, string $message, array $meta = []): void
    {
        ProjectLog::create([
            'project_id' => (int) $project->id,
            'user_id'    => $user?->id,
            'type'       => $type,
            'message'    => $message,
            'meta'       => $meta ?: null,
        ]);
    }
}
