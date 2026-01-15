<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectInvoice extends Model
{
    protected $table = 'project_invoices';

    protected $guarded = [];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date'     => 'date',
        'vat_rate'     => 'integer',
        'sub_total_cents' => 'integer',
        'vat_cents'       => 'integer',
        'total_cents'     => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProjectInvoiceItem::class, 'project_invoice_id')->orderBy('position');
    }
}
