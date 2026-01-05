@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
    <div class="flex-1 w-full min-h-0 overflow-y-auto pr-2 pl-1">
      <div class="w-full mx-auto">

        {{-- Header --}}
        <div class="pt-2">
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
                'bullets' => [
                  "Alles uit plus",
                  "Dronevideo: 2-5 video’s",
                  "Detailfoto’s: 10 foto’s",
                  "m2 per plattegrond",
                  "Woningtekst",
                ],
              ],
              'plus' => [
                'title' => 'Plus pakket',
                'subtitle' => "Alles van Essentials + extra functies & beelden.",
                'price' => '€385,-',
                'bullets' => [
                  "Alles uit Essentials",
                  "Dronefotografie: 5 foto’s",
                  "Woningvideo met plattegrond",
                  "Gemeubileerde 3D plattegronden + tuin",
                  "Privacy check",
                  "Voorfoto",
                ],
              ],
              'essentials' => [
                'title' => 'Essentials pakket',
                'subtitle' => "Compleet pakket met foto’s en video’s.",
                'price' => '€325,-',
                'bullets' => [
                  "Fotografie: 15-45 foto’s",
                  "360° fotografie",
                  "Woningvideo",
                  "Inmeten + rapport",
                  "Plattegronden",
                ],
              ],
              'media' => [
                'title' => 'Media pakket',
                'subtitle' => "Foto- en videografie + 360 graden foto’s.",
                'price' => '€260,-',
                'bullets' => [
                  "Fotografie: 15-45 foto’s",
                  "360° fotografie",
                  "Woningvideo",
                ],
              ],
              'buiten' => [
                'title' => 'Buiten pakket',
                'subtitle' => "Foto’s, video en 360 fotografie buiten.",
                'price' => '€75,-',
                'bullets' => [
                  "Buitenfotografie: 4-10 foto’s",
                  "360° fotografie buiten",
                  "Buitenvideo",
                ],
              ],
              'funda_klaar' => [
                'title' => 'Funda klaar pakket',
                'subtitle' => "Alle essentials direct klaar voor Funda.",
                'price' => '€50,-',
                'bullets' => [
                  "Fotografie: 15-45 foto’s",
                  "Inmeten + rapport",
                  "Plattegronden",
                ],
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

            $selectedPackage = $packages[$row->package] ?? null;
            $selectedExtras = is_array($row->extras) ? $row->extras : [];
          @endphp

          <div class="w-full flex items-center mb-8">
            @php
              $navPrevBtn = "h-11 inline-flex items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";
            @endphp
            <div class="justify-self-start">
              <a href="{{ route('support.onboarding.index') }}" class="{{ $navPrevBtn }}">
                Terug
              </a>
            </div>
          </div>
        </div>

        {{-- Content blocks --}}
        <div class="grid grid-cols-2 gap-4 pb-1">

          {{-- Locatie --}}
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <p class="text-[#191D38] font-black text-sm mb-4">Locatie</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Adres</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ $row->address }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Postcode & Plaats</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ $row->postcode }} — {{ $row->city }}</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Oppervlakte woning</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ (int) $row->surface_home }} m²</p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Bijgebouwen</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ (int) $row->surface_outbuildings }} m²</p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Perceel</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ (int) $row->surface_plot }} m²</p>
              </div>
            </div>
          </div>

          {{-- Contactpersoon --}}
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <p class="text-[#191D38] font-black text-sm mb-4">Contactpersoon</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Naam</p>
                <p class="text-sm font-semibold text-[#191D38]">
                  {{ $row->contact_first_name }} {{ $row->contact_last_name }}
                </p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Updates</p>
                <p class="text-sm font-semibold text-[#191D38]">
                  {{ $row->contact_updates ? 'Ja, op de hoogte houden' : 'Nee' }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">E-mail</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ $row->contact_email }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Telefoon</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ $row->contact_phone }}</p>
              </div>
            </div>
          </div>

          {{-- Makelaardij --}}
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <p class="text-[#191D38] font-black text-sm mb-4">Makelaardij</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Naam</p>
                <p class="text-sm font-semibold text-[#191D38]">
                  {{ $row->agency_first_name }} {{ $row->agency_last_name }}
                </p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Telefoon</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ $row->agency_phone }}</p>
              </div>
            </div>

            <div class="mt-4">
              <p class="text-xs font-bold text-[#191D38]/50 mb-1">E-mail</p>
              <p class="text-sm font-semibold text-[#191D38]">{{ $row->agency_email }}</p>
            </div>
          </div>

          {{-- Pakket --}}
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <p class="text-[#191D38] font-black text-sm mb-4">Gekozen pakket</p>

            @if($selectedPackage)
              <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-[#191D38] font-black text-sm">{{ $selectedPackage['title'] }}</p>
                    <p class="text-[#191D38]/60 text-xs font-semibold mt-1">{{ $selectedPackage['subtitle'] }}</p>
                  </div>
                  <div class="text-[#191D38] text-xl font-black">
                    {{ $selectedPackage['price'] }}
                  </div>
                </div>

                <div class="grid gap-2 mt-4">
                  @foreach(($selectedPackage['bullets'] ?? []) as $b)
                    <div class="flex items-start gap-2">
                      <i class="fa-solid fa-check text-[#009AC3] text-[11px] mt-0.5"></i>
                      <p class="text-[#191D38] text-xs font-semibold">{{ $b }}</p>
                    </div>
                  @endforeach
                </div>
              </div>
            @else
              <p class="text-sm font-semibold text-[#191D38]/70">Geen pakket gevonden (key: {{ $row->package }})</p>
            @endif
          </div>

          {{-- Extra's --}}
          <div class="rounded-2xl col-span-2 ring-1 ring-[#191D38]/10 bg-white p-5">
            <p class="text-[#191D38] font-black text-sm mb-4">Extra’s</p>

            @if(count($selectedExtras))
              <div class="grid gap-3">
                @foreach($selectedExtras as $key)
                  @php $ex = $extrasMap[$key] ?? null; @endphp
                  <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-4">
                    <div class="flex items-start justify-between gap-3">
                      <p class="text-sm font-black text-[#191D38] leading-tight">
                        {{ $ex['title'] ?? $key }}
                      </p>
                      <span class="shrink-0 text-xs font-black text-[#191D38]">
                        @if($ex) €{{ $ex['price'] }},- @endif
                      </span>
                    </div>
                  </div>
                @endforeach
              </div>
            @else
              <p class="text-sm font-semibold text-[#191D38]/70">Geen extra’s gekozen.</p>
            @endif
          </div>

          {{-- Planning --}}
          <div class="rounded-2xl col-span-2 ring-1 ring-[#191D38]/10 bg-white p-5">
            <p class="text-[#191D38] font-black text-sm mb-4">Planning</p>

            <div class="flex items-center gap-10">
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Datum</p>
                <p class="text-sm font-semibold text-[#191D38]">
                  {{ optional($row->shoot_date)->format('d-m-Y') ?? $row->shoot_date }}
                </p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#191D38]/50 mb-1">Tijdsblok</p>
                <p class="text-sm font-semibold text-[#191D38]">{{ $row->shoot_slot }}</p>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>
</div>
@endsection
