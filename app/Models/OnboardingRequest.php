<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',

        'address',
        'postcode',
        'city',
        'surface_home',
        'surface_outbuildings',
        'surface_plot',

        'contact_first_name',
        'contact_last_name',
        'contact_email',
        'contact_phone',
        'contact_updates',

        'agency_first_name',
        'agency_last_name',
        'agency_email',
        'agency_phone',

        'package',
        'extras',

        'shoot_date',
        'shoot_slot',

        'confirm_truth',
        'confirm_terms',

        'status',
    ];

    protected $casts = [
        'extras'          => 'array',
        'contact_updates' => 'boolean',
        'confirm_truth'   => 'boolean',
        'confirm_terms'   => 'boolean',
        'shoot_date'      => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
