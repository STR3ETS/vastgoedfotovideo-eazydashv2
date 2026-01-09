<!doctype html>
<html lang="nl">
<head>
  <meta charset="utf-8">
  <title>Offerte {{ $quote->quote_number }}</title>

  <style>
    @page { margin: 28px 34px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }

    .row { width:100%; }
    .col { vertical-align: top; }
    .right { text-align:right; }
    .muted { color:#6b7280; }
    .strong { font-weight:700; }
    .spacer-1 { height: 10px; }
    .spacer-2 { height: 18px; }
    .spacer-3 { height: 28px; }

    /* top blocks */
    .company-block, .client-block { line-height: 1.45; }
    .company-block .line, .client-block .line { margin: 0; padding: 0; }

    /* title */
    .title { font-size: 28px; font-weight: 800; margin: 0 0 10px; color: #111827; }

    /* meta table */
    .meta { width: 100%; border-collapse: collapse; }
    .meta td { padding: 2px 0; }
    .meta .label { width: 140px; color:#111827; }
    .meta .value { font-weight:700; color:#111827; }

    /* items table */
    .items { width:100%; border-collapse: collapse; margin-top: 18px; }
    .items thead th {
      font-size: 11px;
      font-weight: 700;
      color: #111827;
      text-align: left;
      padding: 8px 0;
      border-bottom: 1px solid #111827;
    }
    .items tbody td {
      padding: 7px 0;
      border-bottom: 0;
    }
    .items .c-desc { width: 55%; }
    .items .c-unit { width: 15%; }
    .items .c-qty  { width: 10%; }
    .items .c-total{ width: 20%; }

    .items .right { text-align:right; }
    .items tr { page-break-inside: avoid; }

    /* totals block (rechts) */
    .totals-wrap { width: 100%; margin-top: 20px; }
    .totals {
      width: 320px;
      margin-left: auto;
      border-collapse: collapse;
    }
    .totals td { padding: 6px 0; }
    .totals .label { color:#111827; }
    .totals .value { text-align:right; font-weight:700; }
    .totals .divider td { padding: 10px 0 8px; border-top: 1px solid #111827; }
    .totals .grand .label { font-size: 14px; font-weight: 800; }
    .totals .grand .value { font-size: 14px; font-weight: 800; }

    /* small footer (optional) */
    .footer { margin-top: 28px; font-size: 10px; color:#6b7280; }
  </style>
</head>

<body>
@php
  $fmt = fn($cents) => '€ ' . number_format(((int)$cents)/100, 2, ',', '.');

  // ✅ logo base64 (DomPDF-safe)
  $logoPath = public_path('assets/vastgoedfotovideo/logo-full.png');
  $logoSrc = null;
  if (file_exists($logoPath)) {
    $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
  }

  // ✅ Bedrijfsgegevens (pas deze mappings aan naar jouw settings/model)
  $companyName  = 'Vastgoed Foto Video';
  $companyStreet = 'De Smalle Zijde 5-10';
  $companyZip   = '3903 LL';
  $companyCity  = 'Veenendaal';
  $companyPhone = '0318 891 586';
  $companyEmail = 'info@vastgoedfotovideo.nl';
  $companyKvk   = '51978458';
  $companyVat   = $companyVat   ?? ($company['vat'] ?? null);
  $companyIban  = $companyIban  ?? ($company['iban'] ?? null);

  // ✅ Relatie (pas deze mappings aan naar jouw project/klant velden)
  $clientName   = $clientName   ?? ($project->client_name ?? $project->customer_name ?? '—');
  $clientStreet = $clientStreet ?? ($project->client_street ?? $project->customer_street ?? null);
  $clientZip    = $clientZip    ?? ($project->client_zip ?? $project->customer_zip ?? null);
  $clientCity   = $clientCity   ?? ($project->client_city ?? $project->customer_city ?? null);

  $quoteDate = \Carbon\Carbon::parse($quote->quote_date)->format('d-m-Y');
  $expireDate = $quote->expire_date ? \Carbon\Carbon::parse($quote->expire_date)->format('d-m-Y') : '—';
@endphp

{{-- TOP: logo links, bedrijfsinfo rechts --}}
<table class="row">
  <tr>
    <td class="col" style="width:55%">
      @if($logoSrc)
        <img src="{{ $logoSrc }}" style="height:60px; max-width: 320px;">
      @else
        <div class="muted">[logo ontbreekt]</div>
      @endif
    </td>
    <td class="col right" style="width:45%">
      <div class="company-block">
        <p class="line strong">{{ $companyName }}</p>

        @if($companyStreet)<p class="line">{{ $companyStreet }}</p>@endif
        @if($companyZip || $companyCity)<p class="line">{{ trim(($companyZip ?? '').' '.($companyCity ?? '')) }}</p>@endif

        <div class="spacer-1"></div>

        @if($companyPhone)<p class="line">{{ $companyPhone }}</p>@endif
        @if($companyEmail)<p class="line">{{ $companyEmail }}</p>@endif

        <div class="spacer-1"></div>

        @if($companyKvk)<p class="line">KvK: {{ $companyKvk }}</p>@endif
        @if($companyVat)<p class="line">{{ $companyVat }}</p>@endif

        <div class="spacer-1"></div>

        @if($companyIban)<p class="line">{{ $companyIban }}</p>@endif
      </div>
    </td>
  </tr>
</table>

<div class="spacer-3"></div>

{{-- Relatie links (zoals voorbeeld) --}}
<div class="client-block">
  <p class="line strong">{{ $clientName }}</p>
  @if($clientStreet)<p class="line">{{ $clientStreet }}</p>@endif
  @if($clientZip || $clientCity)<p class="line">{{ trim(($clientZip ?? '').'  '.($clientCity ?? '')) }}</p>@endif
</div>

<div class="spacer-3"></div>

{{-- Titel + meta --}}
<div class="title">Offerte</div>

<table class="meta">
  <tr>
    <td class="label">Offertenummer:</td>
    <td class="value">{{ $quote->quote_number ?? '—' }}</td>
  </tr>
  <tr>
    <td class="label">Offertedatum:</td>
    <td class="value">{{ $quoteDate }}</td>
  </tr>
  <tr>
    <td class="label">Vervaldatum:</td>
    <td class="value">{{ $expireDate }}</td>
  </tr>
</table>

<div class="spacer-2"></div>

{{-- Items tabel --}}
<table class="items">
  <thead>
    <tr>
      <th class="c-desc">Omschrijving</th>
      <th class="c-unit right">Bedrag</th>
      <th class="c-qty right">Aantal</th>
      <th class="c-total right">Totaal</th>
    </tr>
  </thead>
  <tbody>
    @foreach($quote->items as $it)
      <tr>
        <td class="c-desc">{{ $it->description }}</td>
        <td class="c-unit right">{{ $fmt($it->unit_price_cents) }}</td>
        <td class="c-qty right">{{ (int)$it->quantity }}</td>
        <td class="c-total right">{{ $fmt($it->line_total_cents) }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

{{-- Totals rechts onder --}}
<div class="totals-wrap">
  <table class="totals">
    <tr>
      <td class="label right">Subtotaal</td>
      <td class="value">{{ $fmt($quote->sub_total_cents) }}</td>
    </tr>
    <tr>
      <td class="label right">{{ (int)$quote->vat_rate }}% btw</td>
      <td class="value">{{ $fmt($quote->vat_cents) }}</td>
    </tr>
    <tr class="divider"><td colspan="2"></td></tr>
    <tr class="grand">
      <td class="label right">Totaal</td>
      <td class="value">{{ $fmt($quote->total_cents) }}</td>
    </tr>
  </table>
</div>

@if($quote->notes)
  <div class="spacer-2"></div>
  <div class="muted"><strong>Notities</strong></div>
  <div class="muted">{{ $quote->notes }}</div>
@endif

</body>
</html>
