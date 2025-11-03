<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TeamInvite extends Model
{
    protected $fillable = [
        'company_id','email','token','invited_by_user_id','expires_at','accepted_at',
    ];

    protected $casts = [
        'expires_at'  => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function inviter(): BelongsTo { return $this->belongsTo(User::class, 'invited_by_user_id'); }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    public function isAccepted(): bool
    {
        return !is_null($this->accepted_at);
    }

    public static function issue(int $companyId, int $inviterId, string $email, ?int $ttlDays = 7): self
    {
        $normalized = strtolower(trim($email));

        // Vervang evt. bestaande invite voor dezelfde mail binnen dit bedrijf
        static::where('company_id', $companyId)->where('email', $normalized)->delete();

        return static::create([
            'company_id'         => $companyId,
            'email'              => $normalized,
            'token'              => Str::random(64),
            'invited_by_user_id' => $inviterId,
            'expires_at'         => now()->addDays($ttlDays ?? 7),
        ]);
    }
}
