<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectQuote extends Model
{
  protected $fillable = [
    'project_id',
    'created_by',
    'quote_number',
    'quote_date',
    'expire_date',
    'status',
    'vat_rate',
    'sub_total_cents',
    'vat_cents',
    'total_cents',
    'notes',
  ];

  protected $casts = [
    'quote_date'  => 'date',
    'expire_date' => 'date',
  ];

  public function project(): BelongsTo
  {
    return $this->belongsTo(Project::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(ProjectQuoteItem::class)->orderBy('position');
  }

  public function creator()
  {
    return $this->belongsTo(User::class, 'created_by');
  }
}
