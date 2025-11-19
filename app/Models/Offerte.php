<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Offerte extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'public_uuid',
        'status',
        'generated',
        'number',
        'content_overrides',
    ];

    protected $casts = [
        'generated' => 'array',
        'content_overrides' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
