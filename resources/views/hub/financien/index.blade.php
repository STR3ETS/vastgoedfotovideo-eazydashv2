{{-- resources/views/hub/financien/index.blade.php --}}
@extends('hub.layouts.app')

@section('content')
@php
  // Zelfde “look” als je andere tabellen/sections
  $sectionHeader = "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = "bg-[#191D38]/5";

  $fmtCents = fn($cents) => '€' . number_format(((int)$cents) / 100, 2, ',', '.');

  // Controller stuurt: filters, kpis, breakdown, chart
  $filters = $filters ?? [];
  $kpis    = $kpis ?? [];
  $chart   = $chart ?? [];

  $period   = (string) ($filters['period']   ?? request('period', 'this_month'));
  $customer = (string) ($filters['customer'] ?? request('customer', ''));

  // ✅ KPI’s: exact de keys die je controller returnt
  $totalIncomeCents  = (int) ($kpis['total_income_cents']  ?? 0);
  $openInvoicesCount = (int) ($kpis['open_invoices_count'] ?? 0);
  $activeQuotesCount = (int) ($kpis['active_quotes_count'] ?? 0);

  // kan null zijn
  $avgPaymentDays = $kpis['avg_payment_days'] ?? null;

  // ✅ Chart keys: controller gebruikt labels/invoices/offers
  $labels   = $chart['labels']   ?? ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Aug','Sep','Okt','Nov','Dec'];
  $invoices = $chart['invoices'] ?? array_fill(0, count($labels), 0);
  $offers   = $chart['offers']   ?? array_fill(0, count($labels), 0);

  $chartPayload = [
    'labels'   => array_values($labels),
    'invoices' => array_values($invoices),
    'offers'   => array_values($offers),
  ];
@endphp

<div class="col-span-5 flex-1 min-h-0">
  {{-- ✅ Alles blijft binnen dit witte vak --}}
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col overflow-hidden">

    {{-- ✅ Filters (1 blok) --}}
    <!-- <form method="GET" action="{{ route('support.financien.index') }}" class="w-full overflow-hidden rounded-2xl border border-gray-200">
      <div class="{{ $sectionHeader }} flex items-center justify-between gap-4">
        <p class="text-[#191D38] font-black text-sm">Filters</p>
      </div>

      <div class="{{ $sectionBody }} px-6 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] gap-6 items-end min-w-0">

          {{-- Periode --}}
          <div class="min-w-0">
            <label class="block text-[#191D38] font-bold text-xs opacity-50 mb-2">Periode</label>

            <div class="relative min-w-0">
              <select
                name="period"
                class="h-11 w-full bg-white ring-1 ring-[#191D38]/10 rounded-full pl-4 pr-12 text-xs font-semibold text-[#191D38] outline-none focus:ring-[#009AC3] appearance-none cursor-pointer transition"
              >
                <option value="this_month" {{ $period === 'this_month' ? 'selected' : '' }}>Deze maand</option>
                <option value="last_month" {{ $period === 'last_month' ? 'selected' : '' }}>Vorige maand</option>
                <option value="last_30"    {{ $period === 'last_30'    ? 'selected' : '' }}>Laatste 30 dagen</option>
                <option value="this_year"  {{ $period === 'this_year'  ? 'selected' : '' }}>Dit jaar</option>
                <option value="all"        {{ $period === 'all'        ? 'selected' : '' }}>Alles</option>
              </select>

              <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#191D38]/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M8 2v4M16 2v4M3 10h18"/>
                  <path d="M5 6h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z"/>
                </svg>
              </span>
            </div>
          </div>

          {{-- Klant (UI-only nu) --}}
          <div class="min-w-0">
            <label class="block text-[#191D38] font-bold text-xs opacity-50 mb-2">Klant</label>

            <div class="relative min-w-0">
              <input
                name="customer"
                value="{{ $customer }}"
                placeholder="Alle klanten"
                class="h-11 w-full bg-white ring-1 ring-[#191D38]/10 rounded-full pl-4 pr-12 text-xs font-semibold text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              >
              <span class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[#191D38]/40">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="11" cy="11" r="7"></circle>
                  <path d="M21 21l-4.3-4.3"></path>
                </svg>
              </span>
            </div>
          </div>

          {{-- Button --}}
          <div class="flex justify-start lg:justify-end">
            <button
              type="submit"
              class="h-11 cursor-pointer px-6 inline-flex items-center gap-2 rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition duration-200"
            >
              Filters toepassen
            </button>
          </div>
        </div>
      </div>
    </form> -->

    {{-- ✅ KPI cards --}}
    <div class="grid grid-cols-2 gap-8">
        <div class="bg-[#191D38]/5 w-full shrink-0 rounded-2xl p-8 min-w-0 grid grid-cols-2 min-h-0 gap-2">
            <div class="w-full p-6 bg-white rounded-2xl flex flex-col shrink-0">
                <p class="text-[#191D38] font-semibold text-sm shrink-0">Totale inkomsten</p>
                <p class="text-[#191D38] font-black text-3xl shrink-0">{{ $fmtCents($totalIncomeCents) }}</p>
            </div>
            <div class="w-full p-6 bg-white rounded-2xl flex flex-col shrink-0">
                <p class="text-[#191D38] font-semibold text-sm shrink-0">Openstaande facturen</p>
                <p class="text-[#191D38] font-black text-3xl shrink-0">{{ $openInvoicesCount }}</p>
            </div>
            <div class="w-full p-6 bg-white rounded-2xl flex flex-col shrink-0">
                <p class="text-[#191D38] font-semibold text-sm shrink-0">Gemiddelde betalingstermijn</p>
                <p class="text-[#191D38] font-black text-3xl shrink-0">
                {{ is_null($avgPaymentDays) ? '—' : ((int) $avgPaymentDays . ' dagen') }}
                </p>
            </div>
            <div class="w-full p-6 bg-white rounded-2xl flex flex-col shrink-0">
                <p class="text-[#191D38] font-semibold text-sm shrink-0">Actieve offertes</p>
                <p class="text-[#191D38] font-black text-3xl shrink-0">{{ $activeQuotesCount }}</p>
            </div>
        </div>
    </div>

    {{-- ✅ Chart block --}}
    <div class="w-full shrink-0 rounded-2xl mt-8">
      <div class="{{ $sectionHeader }} flex items-center justify-between gap-4 rounded-t-2xl">
        <p class="text-[#191D38] font-black text-sm">Inkomstenoverzicht</p>
      </div>

      <div class="{{ $sectionBody }} px-6 py-6 rounded-b-2xl">
        <div class="relative w-full h-[360px] min-w-0">
          <canvas id="financeIncomeChart" class="w-full h-full"></canvas>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
  window.__financeChart = @json($chartPayload);
</script>

<script>
  (function () {
    var el = document.getElementById('financeIncomeChart');
    if (!el) return;

    var payload = window.__financeChart || {};
    var labels = Array.isArray(payload.labels) ? payload.labels : [];
    var invoices = Array.isArray(payload.invoices) ? payload.invoices : [];
    var offers = Array.isArray(payload.offers) ? payload.offers : [];

    if (!window.Chart) return;

    var ctx = el.getContext('2d');

    new window.Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Invoices',
            data: invoices,
            tension: 0.35,
            borderWidth: 2,
            pointRadius: 2,
            borderColor: '#009AC3',
            backgroundColor: 'rgba(0,154,195,0.10)',
            fill: false
          },
          {
            label: 'Offers',
            data: offers,
            tension: 0.35,
            borderWidth: 2,
            pointRadius: 2,
            borderColor: '#7C3AED',
            backgroundColor: 'rgba(124,58,237,0.10)',
            fill: false
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: true }
        },
        scales: {
          y: { beginAtZero: true },
          x: { grid: { display: false } }
        }
      }
    });
  })();
</script>
@endsection
