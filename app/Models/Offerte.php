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
    ];

    protected $casts = [
        'generated' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
