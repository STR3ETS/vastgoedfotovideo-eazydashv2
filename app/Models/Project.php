<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'onboarding_request_id',
        'client_user_id',
        'created_by_user_id',
        'title',
        'status',
        'category',
        'template',
    ];

    public function onboardingRequest(): BelongsTo
    {
        return $this->belongsTo(OnboardingRequest::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_followers')
            ->withTimestamps();
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProjectTask::class)->orderBy('sort_order')->orderBy('id');
    }

    public function financeItems()
    {
        return $this->hasMany(ProjectFinanceItem::class)
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc');
    }

    public function planningItems(): HasMany
    {
        return $this->hasMany(ProjectPlanningItem::class)->orderBy('start_at', 'asc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectComment::class)->latest();
    }

    public function quotes()
    {
        return $this->hasMany(ProjectQuote::class);
    }
    
}
