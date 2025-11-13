<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offerte extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'public_uuid',
        'status',
        'title',
        'reference',
        'valid_until',
        'total_ex_vat',
        'total_incl_vat',
        'body',
        'meta',
    ];

    protected $casts = [
        'meta'        => 'array',
        'valid_until' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
