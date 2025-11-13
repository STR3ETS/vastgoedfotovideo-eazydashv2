<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectPreviewView extends Model
{
    protected $fillable = [
        'project_id', 'ip', 'city', 'region', 'country', 'country_code', 'user_agent',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
