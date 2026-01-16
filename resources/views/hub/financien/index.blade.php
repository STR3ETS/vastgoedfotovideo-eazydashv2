{{-- resources/views/hub/financien/index.blade.php --}}
@extends('hub.layouts.app')

@section('content')
@php
  // ============================================================
  // Styling helpers
  // ============================================================
  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";

  $fmtCents = fn($cents) => '€ ' . number_format(((int)$cents) / 100, 2, ',', '.');

  // ============================================================
  // Data (verwacht vanuit controller)
  // ============================================================
  $quotes   = collect($quotes   ?? $projectQuotes   ?? $offertes ?? [])->filter(fn($x) => is_object($x))->values();
  $invoices = collect($invoices ?? $projectInvoices ?? $facturen ?? [])->filter(fn($x) => is_object($x))->values();

  // ============================================================
  // Status maps
  // ============================================================
  $quoteStatusMap = [
    'draft'     => ['label' => 'Concept',              'class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
    'sent'      => ['label' => 'Verstuurd',            'class' => 'text-[#009AC3] bg-[#009AC3]/20'],
    'accepted'  => ['label' => 'Getekend',             'class' => 'text-[#87A878] bg-[#87A878]/20'],
    'cancelled' => ['label' => 'Geannuleerd',          'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
  ];

  $invoiceStatusMap = [
    'draft'     => ['label' => 'Concept',              'class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
    'sent'      => ['label' => 'Verstuurd',            'class' => 'text-[#009AC3] bg-[#009AC3]/20'],
    'paid'      => ['label' => 'Betaald',              'class' => 'text-[#87A878] bg-[#87A878]/20'],
    'cancelled' => ['label' => 'Geannuleerd',          'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
  ];

  // ============================================================
  // Summaries voor pills + charts
  // ============================================================
  $quoteSummary = [
    'draft'     => ['count' => 0, 'sum_cents' => 0],
    'sent'      => ['count' => 0, 'sum_cents' => 0],
    'accepted'  => ['count' => 0, 'sum_cents' => 0],
    'cancelled' => ['count' => 0, 'sum_cents' => 0],
    'total'     => ['count' => 0, 'sum_cents' => 0],
  ];

  $invoiceSummary = [
    'draft'     => ['count' => 0, 'sum_cents' => 0],
    'sent'      => ['count' => 0, 'sum_cents' => 0],
    'paid'      => ['count' => 0, 'sum_cents' => 0],
    'cancelled' => ['count' => 0, 'sum_cents' => 0],
    'total'     => ['count' => 0, 'sum_cents' => 0],
  ];

  $quotePerMonth   = [];
  $invoicePerMonth = [];

  foreach ($quotes as $q) {
    $status = strtolower((string)($q->status ?? 'draft'));
    if (!isset($quoteSummary[$status])) $status = 'draft';

    $sub = (int)($q->sub_total_cents ?? 0);
    $vat = (int)($q->vat_cents ?? 0);
    $tot = (int)($q->total_cents ?? ($sub + $vat));

    $quoteSummary[$status]['count']++;
    $quoteSummary[$status]['sum_cents'] += $tot;
    $quoteSummary['total']['count']++;
    $quoteSummary['total']['sum_cents'] += $tot;

    $d = \Carbon\Carbon::parse($q->quote_date ?? $q->created_at ?? now());
    $k = $d->format('Y-m');

    if (!isset($quotePerMonth[$k])) {
      $quotePerMonth[$k] = [
        'label'          => $d->format('M Y'),
        'total_count'    => 0,
        'accepted_count' => 0,
        'sum_cents'      => 0,
      ];
    }

    $quotePerMonth[$k]['total_count']++;
    if ($status === 'accepted') $quotePerMonth[$k]['accepted_count']++;
    $quotePerMonth[$k]['sum_cents'] += $tot;
  }
  ksort($quotePerMonth);

  foreach ($invoices as $inv) {
    $status = strtolower((string)($inv->status ?? 'draft'));
    if (!isset($invoiceSummary[$status])) $status = 'draft';

    $sub = (int)($inv->sub_total_cents ?? 0);
    $vat = (int)($inv->vat_cents ?? 0);
    $tot = (int)($inv->total_cents ?? ($sub + $vat));

    $invoiceSummary[$status]['count']++;
    $invoiceSummary[$status]['sum_cents'] += $tot;
    $invoiceSummary['total']['count']++;
    $invoiceSummary['total']['sum_cents'] += $tot;

    $d = \Carbon\Carbon::parse($inv->invoice_date ?? $inv->created_at ?? now());
    $k = $d->format('Y-m');

    if (!isset($invoicePerMonth[$k])) {
      $invoicePerMonth[$k] = [
        'label'     => $d->format('M Y'),
        'sum_cents' => 0,
      ];
    }

    $invoicePerMonth[$k]['sum_cents'] += $tot;
  }
  ksort($invoicePerMonth);

  $monthKeys = collect(array_unique(array_merge(array_keys($quotePerMonth), array_keys($invoicePerMonth))))
    ->sort()
    ->values();

  $incomeLabels   = $monthKeys->map(fn($k) => \Carbon\Carbon::createFromFormat('Y-m', $k)->format('M Y'))->values()->all();
  $incomeInvoices = $monthKeys->map(fn($k) => (int)($invoicePerMonth[$k]['sum_cents'] ?? 0))->values()->all();
  $incomeQuotes   = $monthKeys->map(fn($k) => (int)($quotePerMonth[$k]['sum_cents'] ?? 0))->values()->all();

  $conversionSeries = $monthKeys->map(function($k) use ($quotePerMonth) {
    $t = (int)($quotePerMonth[$k]['total_count'] ?? 0);
    $a = (int)($quotePerMonth[$k]['accepted_count'] ?? 0);
    return $t ? round(($a / $t) * 100, 2) : 0;
  })->values()->all();

  $financePayload = [
    'quoteSummary'   => $quoteSummary,
    'invoiceSummary' => $invoiceSummary,
    'income' => [
      'labels'   => $incomeLabels,
      'invoices' => $incomeInvoices,
      'quotes'   => $incomeQuotes,
    ],
    'conversion' => [
      'labels' => $incomeLabels,
      'data'   => $conversionSeries,
    ],
  ];

  $financePayloadJson = json_encode($financePayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

  // Columns (behoud extra info, maar voeg Ex/BTW/Incl toe + gebruik dezelfde naming)
  $qCols = "grid-cols-[160px_minmax(0,1fr)_minmax(0,0.9fr)_140px_140px_140px_120px_140px_160px_140px_90px]";
  // Nummer | Bedrijf | Contact | Datum | Vervaldatum | Ex | BTW | Incl | Status | Ondertekend op | Acties

  $iCols = "grid-cols-[160px_minmax(0,1fr)_140px_140px_140px_120px_140px_160px_90px]";
  // Nummer | Bedrijf | Datum | Vervaldatum | Ex | BTW | Incl | Status | Acties
@endphp

<div class="col-span-5 bg-white p-8 border border-gray-200 rounded-2xl space-y-6" x-data="financeIndex()">

  {{-- =========================
       OFFERTES CARD
     ========================= --}}
  <div class="{{ $sectionWrap }}">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Offertes</p>
      </div>

      <input
        type="text"
        x-model="searchQuotes"
        placeholder="Zoek op offerte, bedrijf of contactpersoon"
        class="h-9 w-[420px] rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
      >
    </div>

    <div class="{{ $sectionBody }} rounded-b-2xl p-6 space-y-4">

      {{-- Pills --}}
      <div class="flex flex-wrap items-center justify-between p-2 rounded-full bg-white ring-1 ring-[#191D38]/10">
        <div class="flex-1 grid grid-cols-4 gap-2">
          <span class="py-2 px-3 rounded-full text-xs font-semibold bg-[#DF9A57]/20 text-[#DF9A57] text-center">
            {{ $quoteSummary['draft']['count'] }} ≈ {{ $fmtCents($quoteSummary['draft']['sum_cents']) }}
          </span>
          <span class="py-2 px-3 rounded-full text-xs font-semibold bg-[#009AC3]/20 text-[#009AC3] text-center">
            {{ $quoteSummary['sent']['count'] }} ≈ {{ $fmtCents($quoteSummary['sent']['sum_cents']) }}
          </span>
          <span class="py-2 px-3 rounded-full text-xs font-semibold bg-[#87A878]/20 text-[#87A878] text-center">
            {{ $quoteSummary['accepted']['count'] }} ≈ {{ $fmtCents($quoteSummary['accepted']['sum_cents']) }}
          </span>
          <span class="py-2 px-3 rounded-full text-xs font-semibold bg-[#DF2935]/20 text-[#DF2935] text-center">
            {{ $quoteSummary['cancelled']['count'] }} ≈ {{ $fmtCents($quoteSummary['cancelled']['sum_cents']) }}
          </span>
        </div>

        <span class="py-2 px-3 rounded-full text-xs font-extrabold text-[#191D38] pl-6 whitespace-nowrap">
          {{ $quoteSummary['total']['count'] }} ≈ {{ $fmtCents($quoteSummary['total']['sum_cents']) }}
        </span>
      </div>

      {{-- Table (inner block) --}}
      <div class="{{ $sectionWrap }} overflow-visible">
        <div class="px-6 py-4 bg-[#191D38]/10 rounded-t-2xl border border-[#191D38]/10 border-b-0">
          <div class="grid {{ $qCols }} gap-4 items-center">
            <p class="text-[#191D38] font-bold text-xs opacity-50">Nummer</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Bedrijfsnaam</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Contactpersoon</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Datum</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Vervaldatum</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Ex. BTW</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">BTW</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Incl. BTW</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Ondertekend op</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
          </div>
        </div>

        <div class="bg-[#191D38]/5 rounded-b-2xl divide-y divide-[#191D38]/10 px-6 py-2 border border-[#191D38]/10 border-t-0">
          @forelse($quotes as $q)
            @php
              $statusKey = strtolower((string)($q->status ?? 'draft'));
              if (!isset($quoteStatusMap[$statusKey])) $statusKey = 'draft';
              $pill = $quoteStatusMap[$statusKey];

              $sub = (int) ($q->sub_total_cents ?? 0);
              $vat = (int) ($q->vat_cents ?? 0);
              $tot = (int) ($q->total_cents ?? ($sub + $vat));

              $quoteNr = $q->quote_number ?? $q->number ?? ('OF-' . str_pad((int)($q->id ?? 0), 6, '0', STR_PAD_LEFT));

              $companyName = $q->company_name
                ?? optional($q->project)->company_name
                ?? optional($q->project)->company
                ?? 'Onbekend';

              $contactName = $q->contact_name
                ?? optional($q->project)->contact_name
                ?? '—';

              $qDate = $q->quote_date ? \Carbon\Carbon::parse($q->quote_date)->format('d-m-Y') : '—';
              $exp   = $q->expire_date ? \Carbon\Carbon::parse($q->expire_date)->format('d-m-Y') : '—';

              $signedAt  = $q->accepted_at ?? $q->signed_at ?? null;
              $signedTxt = $signedAt ? \Carbon\Carbon::parse($signedAt)->format('d-m-Y') : null;

              $searchHaystack = strtolower($quoteNr.' '.$companyName.' '.$contactName);
            @endphp

            <div
              class="grid {{ $qCols }} gap-4 items-center py-3"
              x-show="(quoteStatus === 'all' || quoteStatus === '{{ $statusKey }}') && (!searchQuotes || '{{ $searchHaystack }}'.includes(searchQuotes.toLowerCase()))"
              x-cloak
            >
              <p class="text-[#191D38] font-semibold text-sm">{{ $quoteNr }}</p>
              <p class="text-[#191D38] font-bold text-sm truncate">{{ $companyName }}</p>
              <p class="text-[#191D38] text-sm truncate">{{ $contactName }}</p>
              <p class="text-[#191D38] text-sm">{{ $qDate }}</p>
              <p class="text-[#191D38] text-sm">{{ $exp }}</p>
              <p class="text-[#191D38] text-sm">{{ $fmtCents($sub) }}</p>
              <p class="text-[#191D38] text-sm">{{ $fmtCents($vat) }}</p>
              <p class="text-[#009AC3] text-sm font-semibold">{{ $fmtCents($tot) }}</p>


              <div>
                <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-semibold {{ $pill['class'] }}">
                  {{ $pill['label'] }}
                </span>
              </div>

              <div>
                @if($signedTxt)
                  <p class="text-[#191D38] text-sm">{{ $signedTxt }}</p>
                @else
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold text-[#191D38]/40 bg-[#191D38]/10">
                    Nog niet ondertekend
                  </span>
                @endif
              </div>

              <div class="flex items-center justify-end">
                <button type="button"
                  class="w-8 h-8 bg-white ring-1 ring-[#191D38]/10 hover:bg-[#191D38]/5 rounded-full flex items-center justify-center transition duration-200"
                  title="Openen"
                >
                  <i class="fa-solid fa-up-right-from-square text-[#191D38] text-xs"></i>
                </button>
              </div>
            </div>
          @empty
            <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
              Nog geen offertes.
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- =========================
       FACTUREN CARD
     ========================= --}}
  <div class="{{ $sectionWrap }} bg-white border border-gray-200">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Facturen</p>
      </div>

      <input
        type="text"
        x-model="searchInvoices"
        placeholder="Zoek op factuur of bedrijf"
        class="h-9 w-[420px] rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
      >
    </div>

    <div class="{{ $sectionBody }} rounded-b-2xl p-6 space-y-4">
      {{-- Pills --}}
      <div class="flex flex-wrap items-center justify-between p-2 rounded-full bg-white ring-1 ring-[#191D38]/10">
        <div class="flex-1 grid grid-cols-4 gap-2">
          <span class="py-2 px-3 rounded-full text-xs font-semibold bg-[#DF9A57]/20 text-[#DF9A57] text-center">
            {{ $invoiceSummary['draft']['count'] }} ≈ {{ $fmtCents($invoiceSummary['draft']['sum_cents']) }}
          </span>
          <span class="py-2 px-3 rounded-full text-xs font-semibold bg-[#009AC3]/20 text-[#009AC3] text-center">
            {{ $invoiceSummary['sent']['count'] }} ≈ {{ $fmtCents($invoiceSummary['sent']['sum_cents']) }}
          </span>
          <span class="py-2 px-3 rounded-full text-xs font-semibold bg-[#87A878]/20 text-[#87A878] text-center">
            {{ $invoiceSummary['paid']['count'] }} ≈ {{ $fmtCents($invoiceSummary['paid']['sum_cents']) }}
          </span>
          <span class="py-2 px-3 rounded-full text-xs font-semibold bg-[#DF2935]/20 text-[#DF2935] text-center">
            {{ $invoiceSummary['cancelled']['count'] }} ≈ {{ $fmtCents($invoiceSummary['cancelled']['sum_cents']) }}
          </span>
        </div>

        <span class="py-2 px-3 rounded-full text-xs font-extrabold text-[#191D38] pl-6 whitespace-nowrap">
          {{ $invoiceSummary['total']['count'] }} ≈ {{ $fmtCents($invoiceSummary['total']['sum_cents']) }}
        </span>
      </div>

      {{-- Table --}}
      <div class="{{ $sectionWrap }} overflow-visible">
        <div class="px-6 py-4 bg-[#191D38]/10 rounded-t-2xl border border-[#191D38]/10 border-b-0">
          <div class="grid {{ $iCols }} gap-4 items-center">
            <p class="text-[#191D38] font-bold text-xs opacity-50">Nummer</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Bedrijfsnaam</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Datum</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Vervaldatum</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Ex. BTW</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">BTW</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Incl. BTW</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
          </div>
        </div>

        <div class="bg-[#191D38]/5 rounded-b-2xl divide-y divide-[#191D38]/10 px-6 py-2 border border-[#191D38]/10 border-t-0">
          @forelse($invoices as $inv)
            @php
              $statusKey = strtolower((string)($inv->status ?? 'draft'));
              if (!isset($invoiceStatusMap[$statusKey])) $statusKey = 'draft';
              $pill = $invoiceStatusMap[$statusKey];

              $sub = (int) ($inv->sub_total_cents ?? 0);
              $vat = (int) ($inv->vat_cents ?? 0);
              $tot = (int) ($inv->total_cents ?? ($sub + $vat));

              $invNr = $inv->invoice_number ?? $inv->number ?? ('F-' . str_pad((int)($inv->id ?? 0), 6, '0', STR_PAD_LEFT));

              $companyName = $inv->company_name
                ?? optional($inv->project)->company_name
                ?? optional($inv->project)->company
                ?? 'Onbekend';

              $iDate = $inv->invoice_date ? \Carbon\Carbon::parse($inv->invoice_date)->format('d-m-Y') : '—';
              $due   = $inv->due_date ? \Carbon\Carbon::parse($inv->due_date)->format('d-m-Y') : '—';

              $searchHaystack = strtolower($invNr.' '.$companyName);
            @endphp

            <div
              class="grid {{ $iCols }} gap-4 items-center py-3"
              x-show="(invoiceStatus === 'all' || invoiceStatus === '{{ $statusKey }}') && (!searchInvoices || '{{ $searchHaystack }}'.includes(searchInvoices.toLowerCase()))"
              x-cloak
            >
              <p class="text-[#191D38] font-semibold text-sm">{{ $invNr }}</p>
              <p class="text-[#191D38] font-bold text-sm truncate">{{ $companyName }}</p>
              <p class="text-[#191D38] text-sm">{{ $iDate }}</p>
              <p class="text-[#191D38] text-sm">{{ $due }}</p>
              <p class="text-[#191D38] text-sm">{{ $fmtCents($sub) }}</p>
              <p class="text-[#191D38] text-sm">{{ $fmtCents($vat) }}</p>
              <p class="text-[#009AC3] text-sm font-semibold">{{ $fmtCents($tot) }}</p>

              <div>
                <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-xs font-semibold {{ $pill['class'] }}">
                  {{ $pill['label'] }}
                </span>
              </div>

              <div class="flex items-center justify-end">
                <button type="button"
                  class="w-8 h-8 bg-white ring-1 ring-[#191D38]/10 hover:bg-[#191D38]/5 rounded-full flex items-center justify-center transition duration-200"
                  title="Openen"
                >
                  <i class="fa-solid fa-up-right-from-square text-[#191D38] text-xs"></i>
                </button>
              </div>
            </div>
          @empty
            <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
              Nog geen facturen.
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>

  {{-- =========================
       STATISTIEKEN CARD
     ========================= --}}
  <div class="{{ $sectionWrap }} bg-white border border-gray-200">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Statistieken</p>
      </div>
    </div>

    <div class="{{ $sectionBody }} rounded-b-2xl p-6" x-show="openStat" x-transition>
      <div class="grid grid-cols-3 gap-4">
        <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-6">
          <p class="text-[#191D38] font-black text-sm mb-3">Offertes per status</p>
          <div class="h-[170px]">
            <canvas id="chartQuotesStatus"></canvas>
          </div>
        </div>

        <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-6">
          <p class="text-[#191D38] font-black text-sm mb-3">Totaal offertes per status</p>
          <div class="h-[170px]">
            <canvas id="chartQuotesAmount"></canvas>
          </div>
        </div>

        <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-6">
          <p class="text-[#191D38] font-black text-sm mb-3">Omzet per maand</p>
          <div class="h-[170px]">
            <canvas id="chartIncome"></canvas>
          </div>
        </div>

        <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-6">
          <p class="text-[#191D38] font-black text-sm mb-3">Facturen per status</p>
          <div class="h-[170px]">
            <canvas id="chartInvoicesStatus"></canvas>
          </div>
        </div>

        <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-6">
          <p class="text-[#191D38] font-black text-sm mb-3">Totaal facturen per status</p>
          <div class="h-[170px]">
            <canvas id="chartInvoicesAmount"></canvas>
          </div>
        </div>

        <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-6">
          <p class="text-[#191D38] font-black text-sm mb-3">Conversie per maand (in %)</p>
          <div class="h-[170px]">
            <canvas id="chartConversion"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

{{-- payload --}}
<script>
  window.__financePayload = {!! $financePayloadJson !!};
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
  function financeIndex(){
    return {
      openStat: true,

      quoteStatus: 'all',
      invoiceStatus: 'all',

      searchQuotes: '',
      searchInvoices: '',

      init(){
        this.$nextTick(() => this.initCharts());
      },

      euroFromCents(c){
        const n = Number(c) || 0;
        return '€ ' + (n / 100).toLocaleString('nl-NL', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
      },

      initCharts(){
        if(!window.Chart) return;

        const payload = window.__financePayload || {};
        const qs = payload.quoteSummary || {};
        const is = payload.invoiceSummary || {};

        const qOrder  = ['draft','sent','accepted','cancelled'];
        const qLabels = ['Concept','Verstuurd','Getekend','Geannuleerd'];
        const qCounts = qOrder.map(k => (qs[k]?.count ?? 0));
        const qSums   = qOrder.map(k => (qs[k]?.sum_cents ?? 0));

        const iOrder  = ['draft','sent','paid','cancelled'];
        const iLabels = ['Concept','Verstuurd','Betaald','Geannuleerd'];
        const iCounts = iOrder.map(k => (is[k]?.count ?? 0));
        const iSums   = iOrder.map(k => (is[k]?.sum_cents ?? 0));

        const income = payload.income || { labels: [], invoices: [], quotes: [] };
        const conv   = payload.conversion || { labels: [], data: [] };

        const mkDoughnut = (el, labels, data, colors) => {
          if(!el) return;
          new Chart(el, {
            type: 'doughnut',
            data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0, borderRadius: 18 }] },
            options: { responsive:true, maintainAspectRatio:false, cutout:'70%', plugins:{ legend:{ display:false } } }
          });
        };

        const mkBar = (el, labels, data, colors) => {
          if(!el) return;
          new Chart(el, {
            type: 'bar',
            data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0, borderRadius: 10 }] },
            options: {
              responsive:true,
              maintainAspectRatio:false,
              plugins:{
                legend:{ display:false },
                tooltip:{ callbacks:{ label:(ctx)=> this.euroFromCents(ctx.raw || 0) } }
              },
              scales:{
                x:{ grid:{ display:false }, ticks:{ font:{ size:10 } } },
                y:{ beginAtZero:true, grid:{ color:'#191D3810' }, ticks:{ callback:(v)=> this.euroFromCents(v) } }
              }
            }
          });
        };

        const mkLine = (el, labels, seriesA, seriesB) => {
          if(!el) return;
          new Chart(el, {
            type: 'line',
            data: {
              labels,
              datasets: [
                { label:'Facturen', data: seriesA, tension:.35, borderWidth:2, pointRadius:2, borderColor:'#009AC3' },
                { label:'Offertes', data: seriesB, tension:.35, borderWidth:2, pointRadius:2, borderColor:'#DF9A57' },
              ]
            },
            options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:true } }, scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true } } }
          });
        };

        const mkPercentLine = (el, labels, data) => {
          if(!el) return;
          new Chart(el, {
            type: 'line',
            data: { labels, datasets: [{ data, tension:.35, borderWidth:2, pointRadius:2, borderColor:'#87A878' }] },
            options:{
              responsive:true,
              maintainAspectRatio:false,
              plugins:{
                legend:{ display:false },
                tooltip:{ callbacks:{ label:(ctx)=> (Number(ctx.raw)||0).toFixed(1)+' %' } }
              },
              scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true, max:100, ticks:{ callback:(v)=> v+' %' } } }
            }
          });
        };

        mkDoughnut(document.getElementById('chartQuotesStatus'), qLabels, qCounts, ['#DF9A57','#009AC3','#87A878','#DF2935']);
        mkBar(document.getElementById('chartQuotesAmount'), qLabels, qSums, ['#DF9A57','#009AC3','#87A878','#DF2935']);

        mkDoughnut(document.getElementById('chartInvoicesStatus'), iLabels, iCounts, ['#DF9A57','#009AC3','#87A878','#DF2935']);
        mkBar(document.getElementById('chartInvoicesAmount'), iLabels, iSums, ['#DF9A57','#009AC3','#87A878','#DF2935']);

        mkLine(document.getElementById('chartIncome'), income.labels || [], income.invoices || [], income.quotes || []);
        mkPercentLine(document.getElementById('chartConversion'), conv.labels || [], conv.data || []);
      }
    }
  }
</script>
@endsection
