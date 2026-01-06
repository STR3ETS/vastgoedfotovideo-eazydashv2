@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

    @php
      // status labels (later uitbreidbaar)
      $status = $row->status ?? 'new';

      $statusLabel = match($status) {
        'new' => 'Voltooid',
        'cancelled' => 'Geannuleerd',
        'archived' => 'Gearchiveerd',
        default => 'Voltooid',
      };

      $statusClass = match($status) {
        'new' => 'text-[#87A878] bg-[#87A878]/20',
        'cancelled' => 'text-[#DF2935] bg-[#DF2935]/20',
        'archived' => 'text-[#DF9A57] bg-[#DF9A57]/20',
        default => 'text-[#87A878] bg-[#87A878]/20',
      };

      $packages = [
        'pro' => [
          'title' => 'Pro pakket',
          'subtitle' => "Alles van Plus + interactieve tools.",
          'price' => '€509,-',
        ],
        'plus' => [
          'title' => 'Plus pakket',
          'subtitle' => "Alles van Essentials + extra functies & beelden.",
          'price' => '€385,-',
        ],
        'essentials' => [
          'title' => 'Essentials pakket',
          'subtitle' => "Compleet pakket met foto’s en video’s.",
          'price' => '€325,-',
        ],
        'media' => [
          'title' => 'Media pakket',
          'subtitle' => "Foto- en videografie + 360 graden foto’s.",
          'price' => '€260,-',
        ],
        'buiten' => [
          'title' => 'Buiten pakket',
          'subtitle' => "Foto’s, video en 360 fotografie buiten.",
          'price' => '€75,-',
        ],
        'funda_klaar' => [
          'title' => 'Funda klaar pakket',
          'subtitle' => "Alle essentials direct klaar voor Funda.",
          'price' => '€50,-',
        ],
      ];

      $extrasMap = [
        'privacy_check' => ['title' => 'Privacy check', 'price' => 10],
        'detailfotos' => ['title' => 'Detailfoto’s', 'price' => 25],
        'hoogtefotografie_8m' => ['title' => 'Hoogtefotografie 8 meter', 'price' => 25],
        'plattegrond_in_video' => ['title' => 'Plattegronden verwerkt in video', 'price' => 15],
        'tekst_video' => ['title' => 'Tekst toevoegen video', 'price' => 15],
        'floorplanner_3d' => ['title' => 'Floorplanner plattegronden 3D', 'price' => 10],
        'meubels_toevoegen' => ['title' => 'Plattegronden toevoegen: meubels', 'price' => 10],
        'tuin_toevoegen' => ['title' => 'Plattegronden toevoegen: tuin', 'price' => 10],
        'artist_impression' => ['title' => 'Artist impression', 'price' => 95],
        'woningtekst' => ['title' => 'Woningtekst', 'price' => 85],
        'video_1min' => ['title' => '1 minuut video', 'price' => 15],
        'foto_slideshow' => ['title' => 'Foto slideshow', 'price' => 15],
        'levering_24u' => ['title' => 'Levering binnen 24 uur', 'price' => 35],
        'huisstijl_plattegrond' => ['title' => 'Plattegronden in eigen huisstijl', 'price' => 10],
        'm2_per_ruimte' => ['title' => 'Plattegronden: m2 per ruimte aangegeven', 'price' => 5],
        'style_shoot' => ['title' => 'Style shoot', 'price' => 40],
      ];

      // Selected package/extras
      $packageKey = strtolower((string) ($row->package ?? ''));
      $selectedPackage = $packages[$packageKey] ?? null;

      $selectedExtras = $row->extras ?? [];
      if (is_string($selectedExtras)) {
        $decoded = json_decode($selectedExtras, true);
        $selectedExtras = is_array($decoded) ? $decoded : [];
      }
      if (!is_array($selectedExtras)) $selectedExtras = [];

      // Invoice helpers
      $fmt = fn($amount) => '€' . number_format((float) $amount, 0, ',', '.') . ',-';
      $toAmount = function($price) {
        if (is_numeric($price)) return (float) $price;
        $digits = preg_replace('/[^\d]/', '', (string) $price);
        return $digits === '' ? 0 : (float) $digits;
      };

      // Build invoice lines
      $lines = [];

      if ($selectedPackage) {
        $pkgAmount = (int) $toAmount($selectedPackage['price'] ?? 0);

        $lines[] = [
          'title'    => $selectedPackage['title'] ?? 'Pakket',
          'subtitle' => $selectedPackage['subtitle'] ?? null,
          'qty'      => 1,
          'unit'     => $pkgAmount,
          'total'    => $pkgAmount,
        ];
      } else {
        $lines[] = [
          'title'    => 'Pakket (onbekend)',
          'subtitle' => 'Key: ' . (($row->package ?? '') !== '' ? $row->package : '—'),
          'qty'      => 1,
          'unit'     => 0,
          'total'    => 0,
        ];
      }

      foreach ($selectedExtras as $key) {
        $ex = $extrasMap[$key] ?? null;

        $lines[] = [
          'title'    => $ex['title'] ?? $key,
          'subtitle' => null,
          'qty'      => 1,
          'unit'     => (int) ($ex['price'] ?? 0),
          'total'    => (int) ($ex['price'] ?? 0),
        ];
      }

      $grandTotal = array_sum(array_map(fn($l) => (int) ($l['total'] ?? 0), $lines));

      // UI helper classes (zelfde style als kostenplaatje)
      $sectionWrap   = "overflow-hidden rounded-2xl";
      $sectionHeader = "shrink-0 px-6 py-4 bg-[#191D38]/10";
      $sectionBody   = "bg-[#191D38]/5";
      $labelClass    = "text-[#191D38] font-bold text-xs opacity-50";
      $valueClass    = "text-[#191D38] text-sm font-semibold";

      $navPrevBtn = "h-9 inline-flex text-xs items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";

      // Map query (Google Maps embed)
      $fullAddress = trim(($row->address ?? '') . ', ' . ($row->postcode ?? '') . ' ' . ($row->city ?? ''));
      $mapsQ = urlencode($fullAddress);

      // Breadcrumb label (klein, alleen voor inzien)
      $crumbLabel = trim(($row->address ?? '') . ' — ' . ($row->city ?? ''));
      $crumbLabel = $crumbLabel !== ' — ' ? $crumbLabel : 'Aanvraag';
    @endphp

    {{-- Breadcrumbs (sticky / altijd zichtbaar) --}}
    <div class="shrink-0 mb-4 flex items-center justify-between">
      <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-[#191D38]/50">
        <a href="{{ route('support.dashboard') }}" class="hover:text-[#191D38] transition">
          Dashboard
        </a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.onboarding.index') }}" class="hover:text-[#191D38] transition">
          Onboarding
        </a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.onboarding.index') }}" class="hover:text-[#191D38] transition">
          Overzicht
        </a>
        <span class="opacity-40">/</span>
        <span class="text-[#009AC3]">
          {{ $crumbLabel }}
        </span>
      </nav>
      <div class="flex items-center gap-4">
        <a href="{{ route('support.onboarding.index') }}" class="{{ $navPrevBtn }}">
          Terug naar overzicht
        </a>
        <div class="{{ $statusClass }} text-xs font-semibold rounded-full h-9 flex items-center px-8 text-center">
          {{ $statusLabel }}
        </div>
      </div>
    </div>

    {{-- Scroll container --}}
    <div class="flex-1 w-full min-h-0 overflow-y-auto pr-2 pl-1">
      <div class="w-full mx-auto">
        {{-- Content blocks --}}
        <div class="grid grid-cols-2 gap-4 pb-1">

          {{-- Locatie (col-span-2 met links data / rechts map) --}}
          <div class="col-span-2 {{ $sectionWrap }}">
            <div class="{{ $sectionHeader }}">
              <p class="text-[#191D38] font-black text-sm">Locatie</p>
            </div>

            <div class="{{ $sectionBody }}">
              <div class="px-6 py-4">
                <div class="flex flex-col lg:flex-row gap-6">

                  {{-- Links: data --}}
                  <div class="w-full lg:w-1/2">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                      <div>
                        <p class="{{ $labelClass }} mb-1">Adres</p>
                        <p class="{{ $valueClass }}">{{ $row->address }}</p>
                      </div>
                      <div>
                        <p class="{{ $labelClass }} mb-1">Postcode & Plaats</p>
                        <p class="{{ $valueClass }}">{{ $row->postcode }} — {{ $row->city }}</p>
                      </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-[#191D38]/10">
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                          <p class="{{ $labelClass }} mb-1">Oppervlakte woning</p>
                          <p class="{{ $valueClass }}">{{ (int) ($row->surface_home ?? 0) }} m²</p>
                        </div>
                        <div>
                          <p class="{{ $labelClass }} mb-1">Bijgebouwen</p>
                          <p class="{{ $valueClass }}">{{ (int) ($row->surface_outbuildings ?? 0) }} m²</p>
                        </div>
                        <div>
                          <p class="{{ $labelClass }} mb-1">Perceel</p>
                          <p class="{{ $valueClass }}">{{ (int) ($row->surface_plot ?? 0) }} m²</p>
                        </div>
                      </div>
                    </div>

                    {{-- Planning (ruimte vullen, puur data) --}}
                    <div class="mt-4 pt-4 border-t border-[#191D38]/10">
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                          <p class="{{ $labelClass }} mb-1">Datum</p>
                          <p class="{{ $valueClass }}">
                            {{ optional($row->shoot_date)->format('d-m-Y') ?? $row->shoot_date }}
                          </p>
                        </div>
                        <div>
                          <p class="{{ $labelClass }} mb-1">Tijdsblok</p>
                          <p class="{{ $valueClass }}">{{ $row->shoot_slot }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Rechts: map --}}
                  <div class="w-full lg:w-1/2">
                    <div class="w-full aspect-[31/10] rounded-2xl overflow-hidden ring-1 ring-[#191D38]/10 bg-white">
                      <iframe
                        title="Map"
                        class="w-full h-full"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q={{ $mapsQ }}&output=embed"
                      ></iframe>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>

          {{-- Contactpersoon --}}
          <div class="{{ $sectionWrap }}">
            <div class="{{ $sectionHeader }}">
              <p class="text-[#191D38] font-black text-sm">Contactpersoon</p>
            </div>

            <div class="{{ $sectionBody }}">
              <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <p class="{{ $labelClass }} mb-1">Naam</p>
                    <p class="{{ $valueClass }}">
                      {{ $row->contact_first_name }} {{ $row->contact_last_name }}
                    </p>
                  </div>
                  <div>
                    <p class="{{ $labelClass }} mb-1">Updates</p>
                    <p class="{{ $valueClass }}">
                      {{ $row->contact_updates ? 'Ja, op de hoogte houden' : 'Nee' }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="border-t border-[#191D38]/10 px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <p class="{{ $labelClass }} mb-1">E-mail</p>
                    <p class="{{ $valueClass }}">{{ $row->contact_email }}</p>
                  </div>
                  <div>
                    <p class="{{ $labelClass }} mb-1">Telefoon</p>
                    <p class="{{ $valueClass }}">{{ $row->contact_phone }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {{-- Makelaardij --}}
          <div class="{{ $sectionWrap }}">
            <div class="{{ $sectionHeader }}">
              <p class="text-[#191D38] font-black text-sm">Makelaardij</p>
            </div>

            <div class="{{ $sectionBody }}">
              <div class="px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <p class="{{ $labelClass }} mb-1">Naam</p>
                    <p class="{{ $valueClass }}">
                      {{ $row->agency_first_name }} {{ $row->agency_last_name }}
                    </p>
                  </div>
                  <div>
                    <p class="{{ $labelClass }} mb-1">Telefoon</p>
                    <p class="{{ $valueClass }}">{{ $row->agency_phone }}</p>
                  </div>
                </div>
              </div>

              <div class="border-t border-[#191D38]/10 px-6 py-4">
                <div>
                  <p class="{{ $labelClass }} mb-1">E-mail</p>
                  <p class="{{ $valueClass }}">{{ $row->agency_email }}</p>
                </div>
              </div>
            </div>
          </div>

          <hr class="border-[#191D38]/10 col-span-2 my-4">

          {{-- Kostenplaatje --}}
          <div class="col-span-2">
            <div class="{{ $sectionWrap }}">
              <div class="{{ $sectionHeader }} flex items-center justify-between gap-4">
                <p class="text-[#191D38] font-black text-sm">Kostenplaatje</p>
                <p class="text-[#191D38]/60 text-xs font-semibold">
                  Totaal: <span class="text-[#191D38] font-black">{{ $fmt($grandTotal) }}</span>
                </p>
              </div>

              <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
                <div class="grid grid-cols-[1fr_0.25fr_0.35fr_0.35fr] items-center gap-6">
                  <p class="text-[#191D38] font-bold text-xs opacity-50">Omschrijving</p>
                  <p class="text-[#191D38] font-bold text-xs opacity-50 text-start">Aantal</p>
                  <p class="text-[#191D38] font-bold text-xs opacity-50">Prijs</p>
                  <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Totaalprijs</p>
                </div>
              </div>

              <div class="{{ $sectionBody }}">
                <div class="px-6 py-2 divide-y divide-[#191D38]/10">
                  @foreach($lines as $line)
                    <div class="py-3 grid grid-cols-[1fr_0.25fr_0.35fr_0.35fr] items-start gap-6">
                      <div class="min-w-0">
                        <p class="text-[#191D38] font-semibold text-sm">
                          {{ $line['title'] }}
                        </p>
                      </div>

                      <div class="text-[#191D38] text-sm">
                        {{ $line['qty'] }}
                      </div>

                      <div class="text-[#191D38] text-sm">
                        {{ $fmt($line['unit']) }}
                      </div>

                      <div class="text-[#009AC3] text-sm text-right">
                        {{ $fmt($line['total']) }}
                      </div>
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>
@endsection
