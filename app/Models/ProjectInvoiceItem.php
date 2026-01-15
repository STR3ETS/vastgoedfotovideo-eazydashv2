<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvoiceItem extends Model
{
    protected $table = 'project_invoice_items';

    protected $guarded = [];

    protected $casts = [
        'quantity'         => 'integer',
        'unit_price_cents' => 'integer',
        'line_total_cents' => 'integer',
        'position'         => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(ProjectInvoice::class, 'project_invoice_id');
    }
}
