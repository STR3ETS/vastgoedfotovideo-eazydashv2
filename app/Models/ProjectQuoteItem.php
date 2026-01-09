<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectQuoteItem extends Model
{
  protected $fillable = [
    'project_quote_id',
    'position',
    'description',
    'quantity',
    'unit_price_cents',
    'line_total_cents',
  ];

  public function quote(): BelongsTo
  {
    return $this->belongsTo(ProjectQuote::class, 'project_quote_id');
  }
}
