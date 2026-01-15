{{-- resources/views/hub/projects/partials/logbook.blade.php --}}

@php
  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";

  $oobAttr = !empty($oob) ? 'hx-swap-oob="true"' : '';
  $logs = ($project->logs ?? collect());

  // 4 kolommen: Actie / Omschrijving / Datum & tijd / Gebruiker
  $logCols = "grid-cols-[220px_minmax(0,1fr)_220px_220px]";

  // helpers: alles 1 regel (nooit enters)
  $oneLine = function ($v) {
    $s = (string) ($v ?? '');
    $s = str_replace(["\r\n", "\n", "\r", "\t"], ' ', $s);
    $s = preg_replace('/\s+/', ' ', $s);
    return trim((string) $s);
  };

  $fmtMoney = function ($cents) {
    $c = (int) ($cents ?? 0);
    return '€' . number_format($c / 100, 2, ',', '.');
  };

  $shortIds = function ($ids) {
    if (!is_array($ids)) return '';
    $ids = array_values(array_map('intval', $ids));
    if (!count($ids)) return '';
    $head = array_slice($ids, 0, 8);
    $txt = implode(',', $head);
    if (count($ids) > 8) $txt .= '…';
    return $txt;
  };

  $humanStatus = function ($s) {
    $s = (string) ($s ?? '');
    return match ($s) {
      'pending'   => 'In afwachting',
      'active'    => 'Actief',
      'done'      => 'Afgerond',
      'archived'  => 'Gearchiveerd',
      'cancelled' => 'Geannuleerd',
      'draft'     => 'Concept',
      'sent'      => 'Verzonden',
      'paid'      => 'Betaald',
      'accepted'  => 'Geaccepteerd',
      default     => $s !== '' ? ucfirst($s) : '—',
    };
  };
@endphp

<div id="project-logbook" {!! $oobAttr !!} class="col-span-2">
  <div class="{{ $sectionWrap }}">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Logboek</p>
      </div>
    </div>

    {{-- Header row --}}
    <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
      <div class="grid {{ $logCols }} items-center gap-6">
        <p class="text-[#191D38] font-bold text-xs opacity-50">Actie</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Omschrijving</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Datum & tijd</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Gebruiker</p>
      </div>
    </div>

    {{-- Body --}}
    <div class="{{ $sectionBody }} rounded-b-2xl">
      <div class="px-6 py-2 divide-y divide-[#191D38]/10">
        @forelse($logs as $log)
          @php
            // meta normaliseren
            $meta = $log->meta ?? [];
            if (is_string($meta)) $meta = json_decode($meta, true) ?: [];
            if (!is_array($meta)) $meta = [];

            $type = (string) ($log->type ?? '');

            // veel voorkomende velden
            $count = data_get($meta, 'count');
            $ids   = data_get($meta, 'ids');
            $idsTxt = $shortIds(is_array($ids) ? $ids : []);

            // status velden (jij hebt soms status, soms level)
            $newStatus = data_get($meta, 'status');
            if ($newStatus === null) $newStatus = data_get($meta, 'new_status');
            if ($newStatus === null) $newStatus = data_get($meta, 'level'); // bij jou in task.status_updated

            $oldStatus = data_get($meta, 'old_status');

            // finance velden
            $desc      = data_get($meta, 'description');
            $qty       = data_get($meta, 'quantity');
            $unitCents = data_get($meta, 'unit_price_cents');
            $totalCents= data_get($meta, 'total_cents');

            // quote/invoice velden
            $quoteId   = data_get($meta, 'quote_id');
            $invoiceId = data_get($meta, 'invoice_id');

            $subTotal  = data_get($meta, 'sub_total_cents', data_get($meta, 'sub_total'));
            $vatCents  = data_get($meta, 'vat_cents');
            $total     = data_get($meta, 'total_cents');

            // Actie + Omschrijving per type
            $action = '';
            $omschrijving = '';

            switch ($type) {

              case 'finance_item.created':
                $action = 'Financiele regel toegevoegd';
                $omschrijving =
                  ($desc ? 'Regel: '.$desc : 'Nieuw financieel item')
                  . ($qty ? ' | Aantal: '.(int)$qty : '')
                  . ($unitCents !== null ? ' | Prijs: '.$fmtMoney($unitCents) : '')
                  . ($totalCents !== null ? ' | Totaal: '.$fmtMoney($totalCents) : '');
              break;

              case 'finance_item.updated':
                $action = 'Financiele regel bewerkt';
                // als je old/new meegeeft werkt dit top; anders fallback op wat er wél is
                $old = (array) data_get($meta, 'old', []);
                $new = (array) data_get($meta, 'new', []);
                if (!empty($old) || !empty($new)) {
                  $b = $old['description'] ?? null;
                  $a = $new['description'] ?? null;
                  $omschrijving = 'Gewijzigd'
                    . ($b !== null || $a !== null ? ' | Omschrijving: '.($b ?? '—').' → '.($a ?? '—') : '');
                } else {
                  $omschrijving =
                    ($desc ? 'Regel: '.$desc : 'Financieel item aangepast')
                    . ($qty ? ' | Aantal: '.(int)$qty : '')
                    . ($unitCents !== null ? ' | Prijs: '.$fmtMoney($unitCents) : '')
                    . ($totalCents !== null ? ' | Totaal: '.$fmtMoney($totalCents) : '');
                }
              break;

              case 'finance_item.deleted':
                $action = 'Financieel regel verwijderd';
                $omschrijving = ($desc ? 'Regel: '.$desc : 'Financieel item verwijderd');
              break;

              case 'finance_item.bulk_deleted':
                $action = 'Financiele regels verwijderd';
                $omschrijving = 'Aantal: '.(int)($count ?? 0) . ($idsTxt ? ' | IDs: '.$idsTxt : '');
              break;

              case 'quote.created':
                $action = 'Offerte aangemaakt';
                $omschrijving =
                  ($quoteId ? 'Offerte ID: '.(int)$quoteId : 'Nieuwe offerte')
                  . ($subTotal !== null ? ' | Subtotaal: '.$fmtMoney($subTotal) : '')
                  . ($vatCents !== null ? ' | BTW: '.$fmtMoney($vatCents) : '')
                  . ($total !== null ? ' | Totaal: '.$fmtMoney($total) : '');
              break;

              case 'quote.status_updated':
                $action = 'Offerte status bewerkt';
                if ($oldStatus !== null && $newStatus !== null) {
                  $omschrijving = 'Van '.$humanStatus($oldStatus).' naar '.$humanStatus($newStatus);
                } elseif ($newStatus !== null) {
                  $omschrijving = 'Naar '.$humanStatus($newStatus);
                } else {
                  $omschrijving = 'Status bijgewerkt';
                }
                if ($count !== null) $omschrijving .= ' | Aantal: '.(int)$count;
                if ($idsTxt) $omschrijving .= ' | IDs: '.$idsTxt;
              break;

              case 'invoice.created':
                $action = 'Factuur aangemaakt';
                $omschrijving =
                  ($invoiceId ? 'Factuur ID: '.(int)$invoiceId : 'Nieuwe factuur')
                  . ($newStatus !== null ? ' | Status: '.$humanStatus($newStatus) : '')
                  . ($subTotal !== null ? ' | Subtotaal: '.$fmtMoney($subTotal) : '')
                  . ($vatCents !== null ? ' | BTW: '.$fmtMoney($vatCents) : '')
                  . ($total !== null ? ' | Totaal: '.$fmtMoney($total) : '');
              break;

              case 'invoice.status_updated':
                $action = 'Factuur status bewerkt';
                if ($oldStatus !== null && $newStatus !== null) {
                  $omschrijving = 'Van '.$humanStatus($oldStatus).' naar '.$humanStatus($newStatus);
                } elseif ($newStatus !== null) {
                  $omschrijving = 'Naar '.$humanStatus($newStatus);
                } else {
                  $omschrijving = 'Status bijgewerkt';
                }
                if ($count !== null) $omschrijving .= ' | Aantal: '.(int)$count;
                if ($idsTxt) $omschrijving .= ' | IDs: '.$idsTxt;
              break;

              case 'task.status_updated':
                $action = 'Taak status bewerkt';
                if ($oldStatus !== null && $newStatus !== null) {
                  $omschrijving = 'Van '.$humanStatus($oldStatus).' naar '.$humanStatus($newStatus);
                } elseif ($newStatus !== null) {
                  $omschrijving = 'Naar '.$humanStatus($newStatus);
                } else {
                  $omschrijving = 'Status bijgewerkt';
                }
                if ($count !== null) $omschrijving .= ' | Aantal: '.(int)$count;
                if ($idsTxt) $omschrijving .= ' | IDs: '.$idsTxt;
              break;

              default:
                // fallback: gebruik bestaande message, maar toch 1-regelig
                $action = $log->message ? $oneLine($log->message) : ($type !== '' ? $type : 'Log');
                $omschrijving = $idsTxt ? ('IDs: '.$idsTxt) : '—';
              break;
            }

            $action = $oneLine($action !== '' ? $action : ($log->message ?? 'Log'));
            $omschrijving = $oneLine($omschrijving !== '' ? $omschrijving : '—');
            $userName = $oneLine($log->user?->name ?? 'Systeem');
          @endphp

          <div class="py-3 grid {{ $logCols }} items-center gap-6">
            {{-- Actie --}}
            <div class="min-w-0">
              <p class="text-[#191D38] font-semibold text-sm">
                {{ $action }}
              </p>
            </div>

            {{-- Omschrijving --}}
            <div class="min-w-0">
              <p class="text-[#191D38] text-sm">
                {{ $omschrijving }}
              </p>
            </div>

            {{-- Datum --}}
            <div class="text-[#191D38] text-sm">
              {{ optional($log->created_at)->format('d-m-Y H:i') ?? '—' }}
            </div>

            {{-- Gebruiker --}}
            <div class="min-w-0">
              <p class="text-[#009AC3] text-sm">
                {{ $userName }}
              </p>
            </div>
          </div>
        @empty
          <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
            Nog geen logboek-items.
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>