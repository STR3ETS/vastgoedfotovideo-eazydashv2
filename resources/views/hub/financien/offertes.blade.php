{{-- resources/views/hub/financien/offertes.blade.php --}}
@extends('hub.layouts.app')

@section('content')
@php
  $quotes = $quotes ?? collect();

  $fmtCents = fn($cents) => '€' . number_format(((int)$cents) / 100, 2, ',', '.');

  $quoteStatusMap = [
    'draft'     => ['label' => 'Concept',             'class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
    'sent'      => ['label' => 'Verstuurd naar klant','class' => 'text-[#009AC3] bg-[#009AC3]/20'],
    'accepted'  => ['label' => 'Getekend',            'class' => 'text-[#87A878] bg-[#87A878]/20'],
    'cancelled' => ['label' => 'Geannuleerd',         'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
  ];

  // ✅ exact zoals in project-finance Offertes tabel
  $cols = "grid-cols-[40px_160px_140px_1fr_140px_140px_140px_90px]";
@endphp

<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

    {{-- ✅ Offertes (zelfde card-structuur als finance.blade.php) --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 flex-1 min-h-0 flex flex-col">

      {{-- header --}}
      <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-t-2xl flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <p class="text-[#191D38] font-black text-sm">Offertes</p>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                class="h-8 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition duration-200"
            >
                Nieuwe offerte maken
            </button>
        </div>
      </div>

      {{-- header row --}}
      <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
        <div class="grid {{ $cols }} gap-4 items-center">
          <div class="flex items-center">
            <input
              type="checkbox"
              disabled
              class="h-4 w-4 rounded border-[#191D38]/20 opacity-40 cursor-not-allowed"
              title="Bulk select komt later"
            >
          </div>

          <p class="text-[#191D38] font-bold text-xs opacity-50">Nummer</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Datum</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Ex. BTW</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">BTW</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Incl. BTW</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
        </div>
      </div>

      {{-- body --}}
      <div class="bg-[#191D38]/5 rounded-b-2xl divide-y divide-[#191D38]/10 px-6 py-2 flex-1 min-h-0 overflow-y-auto custom-scroll">
        @forelse($quotes as $q)
          @php
            $key  = strtolower((string)($q->status ?? 'draft'));
            $pill = $quoteStatusMap[$key] ?? ['label' => ucfirst($key), 'class' => 'text-[#191D38] bg-[#191D38]/10'];

            $sub = (int) ($q->sub_total_cents ?? 0);
            $vat = (int) ($q->vat_cents ?? 0);
            $tot = (int) ($q->total_cents ?? ($sub + $vat));
          @endphp

          <div class="grid {{ $cols }} gap-4 items-center py-3">
            {{-- checkbox --}}
            <div class="flex items-center">
              <input
                type="checkbox"
                disabled
                class="h-4 w-4 rounded border-[#191D38]/20 opacity-40 cursor-not-allowed"
                title="Bulk selectie komt later"
              >
            </div>

            {{-- nummer --}}
            <div class="text-[#191D38] font-semibold text-sm">
              {{ $q->quote_number ?? '—' }}
            </div>

            {{-- datum --}}
            <div class="text-[#191D38] text-sm">
              @if(!empty($q->quote_date))
                {{ \Carbon\Carbon::parse($q->quote_date)->format('d-m-Y') }}
              @else
                —
              @endif
            </div>

            {{-- status (zelfde pill look, maar zonder dropdown) --}}
            <div>
              <span class="w-full text-xs font-semibold rounded-full py-1.5 inline-flex items-center justify-center px-4 {{ $pill['class'] }}">
                {{ $pill['label'] }}
              </span>
            </div>

            {{-- bedragen --}}
            <div class="text-[#191D38] text-sm">{{ $fmtCents($sub) }}</div>
            <div class="text-[#191D38] text-sm">{{ $fmtCents($vat) }}</div>
            <div class="text-[#009AC3] text-sm">{{ $fmtCents($tot) }}</div>

            {{-- acties (nu nog placeholders, later koppelen we routes) --}}
            <div class="flex justify-end items-center gap-2">
              <span class="inline-flex items-center justify-center" title="Download PDF (later)">
                <i class="fa-solid fa-download text-[#191D38]/30"></i>
              </span>

              <span class="inline-flex items-center justify-center" title="Verwijderen (later)">
                <i class="fa-solid fa-trash-can text-[#191D38]/30"></i>
              </span>
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
@endsection