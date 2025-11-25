<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'clock_in_at',
        'clock_out_at',
        'worked_seconds',
    ];

    protected $casts = [
        'clock_in_at'  => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDurationSeconds(): int
    {
        if ($this->clock_out_at) {
            return $this->worked_seconds ?? $this->clock_out_at->diffInSeconds($this->clock_in_at);
        }

        return now()->diffInSeconds($this->clock_in_at);
    }
}
