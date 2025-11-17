<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoAuditResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'seo_audit_id',
        'raw_issue_id',
        'raw_name',
        'severity',          // bv. critical / warning / info
        'pages_affected',    // aantal pagina's waar dit issue speelt
        'sample_urls',       // json array met url voorbeelden
        'code',              // interne code uit rapport, bv. images_4xx
        'label',             // nette naam voor in UI
        'category',          // Techniek / Content / Links / UX / Overig
        'impact',            // hoog / middel / laag
        'effort',            // hoog / middel / laag
        'owner',             // developer / content / marketing / seo / designer
        'priority',          // quick_win / must_fix / normal / low
        'data',              // overige ruwe data (flattened issue)
    ];

    protected $casts = [
        'sample_urls' => 'array',
        'data'        => 'array',
    ];

    public function audit()
    {
        return $this->belongsTo(SeoAudit::class, 'seo_audit_id');
    }
}
