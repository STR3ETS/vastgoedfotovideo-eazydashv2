<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTaskQuestion extends Model
{
    protected $fillable = [
        'project_task_id',
        'question',
        'answer',
        'required',
        'order',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'project_task_id');
    }
}
