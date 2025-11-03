<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon; // <-- toevoegen

class Company extends Model
{
    protected $fillable = [
        'name',
        'country_code',
        'website',
        'email',
        'phone',
        'street',
        'house_number',
        'postal_code',
        'city',
        'kvk_number',
        'vat_number',
        'trade_name',
        'legal_form',
        // optioneel als je deze via mass assignment wilt kunnen zetten
        'trial_days','trial_starts_at','trial_ends_at',
    ];

    protected $casts = [
        'trial_starts_at' => 'datetime',
        'trial_ends_at'   => 'datetime',
        'trial_days'      => 'integer',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function startTrial(?int $days = null): void
    {
        $days = $days ?? ($this->trial_days ?: 30);

        if ($this->isOnTrial()) {
            return;
        }

        $this->trial_days      = $days;
        $this->trial_starts_at = Carbon::now();
        $this->trial_ends_at   = Carbon::now()->copy()->addDays($days);
        $this->save();
    }

    public function isOnTrial(): bool
    {
        return !is_null($this->trial_ends_at) && now()->lt($this->trial_ends_at);
    }

    public function trialRemainingDays(): int
    {
        if (!$this->trial_ends_at) return 0;
        $diff = now()->diffInDays($this->trial_ends_at, false);
        return $diff > 0 ? $diff : 0;
    }

    public function getOnTrialAttribute(): bool
    {
        return $this->isOnTrial();
    }
}
