<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AanvraagEmail extends Model
{
    protected $fillable = [
        'aanvraag_id',
        'direction',
        'mailbox',
        'from_email',
        'from_name',
        'to',
        'cc',
        'bcc',
        'subject',
        'body_text',
        'body_html',
        'message_id',
        'in_reply_to',
        'references',
        'received_at',
        'raw',
    ];

    protected $casts = [
        'to'          => 'array',
        'cc'          => 'array',
        'bcc'         => 'array',
        'references'  => 'array',
        'received_at' => 'datetime',
        'raw'         => 'array',
    ];

    // ✅ Koppelt naar jouw echte Aanvraag-model
    public function aanvraag()
    {
        return $this->belongsTo(\App\Models\AanvraagWebsite::class, 'aanvraag_id');
    }
}
