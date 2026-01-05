{{-- resources/views/hub/onboarding/step6.blade.php --}}
@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
    <div class="flex-1 w-full min-h-0 overflow-y-auto pr-2 pl-1">
      <div class="max-w-2xl w-full mx-auto">

        @php
          $onb = session('onboarding', []);

          $val = function(string $key, $default = '—') use ($onb) {
            return old($key, data_get($onb, $key, $default));
          };

          $packageTitles = [
            'pro'         => 'Pro pakket',
            'plus'        => 'Plus pakket',
            'essentials'  => 'Essentials pakket',
            'media'       => 'Media pakket',
            'funda_klaar' => 'Funda klaar pakket',
            'buiten'      => 'Buiten pakket',
          ];

          $extrasTitles = [
            'privacy_check'         => 'Privacy check',
            'detailfotos'           => 'Detailfoto’s',
            'hoogtefotografie_8m'   => 'Hoogtefotografie 8 meter',
            'plattegrond_in_video'  => 'Plattegronden verwerkt in video',
            'tekst_video'           => 'Tekst toevoegen video',
            'floorplanner_3d'       => 'Floorplanner plattegronden 3D',
            'meubels_toevoegen'     => 'Plattegronden toevoegen: meubels',
            'tuin_toevoegen'        => 'Plattegronden toevoegen: tuin incl. terras/tuinhuisje/carport/voortuin/oprit',
            'artist_impression'     => 'Artist impression',
            'woningtekst'           => 'Woningtekst',
            'video_1min'            => '1 minuut video',
            'foto_slideshow'        => 'Foto slideshow',
            'levering_24u'          => 'Levering binnen 24 uur',
            'huisstijl_plattegrond' => 'Plattegronden in eigen huisstijl',
            'm2_per_ruimte'         => 'Plattegronden: m2 per ruimte aangegeven',
            'style_shoot'           => 'Style shoot',
          ];

          $chosenPackageKey = old('package', data_get($onb, 'package'));
          $chosenPackage    = $packageTitles[$chosenPackageKey] ?? '—';

          $chosenExtrasKeys = old('extras', data_get($onb, 'extras', []));
          if (!is_array($chosenExtrasKeys)) $chosenExtrasKeys = [];

          $chosenExtras = collect($chosenExtrasKeys)
            ->map(fn($k) => $extrasTitles[$k] ?? $k)
            ->values()
            ->all();
        @endphp

        <form
          method="POST"
          action="{{ route('support.onboarding.submit') }}"
          class="w-full basis-full pt-2"
          x-data='{
            confirmTruth: @json((bool) old("confirm_truth")),
            confirmTerms: @json((bool) old("confirm_terms"))
          }'
        >
          @csrf

          <h1 class="text-[#191D38] text-3xl font-black tracking-tight text-center mb-4">Controleer & bevestig.</h1>

          <div class="w-fit text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold text-center mb-8 px-4 rounded-full py-1.5 mx-auto">
            Controleer of alle gegevens correct zijn.
          </div>

          {{-- Over de locatie --}}
          <h2 class="text-[#191D38] text-sm font-black tracking-tight mb-2">Over de locatie</h2>
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <div class="grid grid-cols-2 gap-y-2 gap-x-6">
              <p class="text-xs font-bold text-[#191D38]">Adres:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('address') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Postcode:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('postcode') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Plaatsnaam:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('city') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Oppervlakte woning:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('surface_home', 0) }} m²</p>

              <p class="text-xs font-bold text-[#191D38]">Oppervlakte bijgebouwen:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('surface_outbuildings', 0) }} m²</p>

              <p class="text-xs font-bold text-[#191D38]">Oppervlakte perceel:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('surface_plot', 0) }} m²</p>
            </div>
          </div>

          {{-- Contactpersoon --}}
          <h2 class="text-[#191D38] text-sm font-black tracking-tight mt-6 mb-2">Contactpersoon</h2>
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <div class="grid grid-cols-2 gap-y-2 gap-x-6">
              <p class="text-xs font-bold text-[#191D38]">Voornaam:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('contact_first_name') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Achternaam:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('contact_last_name') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Email:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('contact_email') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Telefoonnummer:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('contact_phone') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Contact updates:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">
                {{ (bool) $val('contact_updates', false) ? 'Ja' : 'Nee' }}
              </p>
            </div>
          </div>

          {{-- Makelaardij gegevens --}}
          <h2 class="text-[#191D38] text-sm font-black tracking-tight mt-6 mb-2">Makelaardij gegevens</h2>
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <div class="grid grid-cols-2 gap-y-2 gap-x-6">
              <p class="text-xs font-bold text-[#191D38]">Voornaam:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('agency_first_name') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Achternaam:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('agency_last_name') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Email:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('agency_email') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Telefoonnummer:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('agency_phone') }}</p>
            </div>
          </div>

          {{-- Gekozen pakket --}}
          <h2 class="text-[#191D38] text-sm font-black tracking-tight mt-6 mb-2">Gekozen pakket</h2>
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <div class="grid grid-cols-2 gap-y-2 gap-x-6">
              <p class="text-xs font-bold text-[#191D38]">Pakket:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $chosenPackage }}</p>

              <p class="text-xs font-bold text-[#191D38]">Extra’s:</p>
              <div class="text-right">
                @if(count($chosenExtras))
                  <div class="inline-flex flex-col gap-1">
                    @foreach($chosenExtras as $ex)
                      <p class="text-xs font-semibold text-[#191D38]/60">{{ $ex }}</p>
                    @endforeach
                  </div>
                @else
                  <p class="text-xs font-semibold text-[#191D38]/60">Geen extra’s gekozen</p>
                @endif
              </div>
            </div>
          </div>

          {{-- Afspraak --}}
          <h2 class="text-[#191D38] text-sm font-black tracking-tight mt-6 mb-2">Afspraak</h2>
          <div class="rounded-2xl ring-1 ring-[#191D38]/10 bg-white p-5">
            <div class="grid grid-cols-2 gap-y-2 gap-x-6">
              <p class="text-xs font-bold text-[#191D38]">Datum:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('shoot_date') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Tijdslot:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">{{ $val('shoot_slot') }}</p>

              <p class="text-xs font-bold text-[#191D38]">Tijdzone:</p>
              <p class="text-xs font-semibold text-[#191D38]/60 text-right">Europe/Amsterdam</p>
            </div>
          </div>

          {{-- Bevestiging --}}
          <div class="mt-8 grid gap-3">
            <label class="flex items-start gap-3 cursor-pointer select-none">
              <input
                type="checkbox"
                name="confirm_truth"
                value="1"
                class="mt-1 h-4 w-4 rounded border border-[#191D38]/20 text-[#009AC3] focus:ring-[#009AC3]"
                x-model="confirmTruth"
                {{ old('confirm_truth') ? 'checked' : '' }}
              >
              <span class="text-sm text-[#191D38] font-semibold">
                Alle informatie is naar waarheid ingevuld.
                <span class="block text-xs font-medium text-[#191D38]/60 mt-1">
                  Controleer bovenstaande gegevens voordat je indient.
                </span>
              </span>
            </label>

            <label class="flex items-start gap-3 cursor-pointer select-none">
              <input
                type="checkbox"
                name="confirm_terms"
                value="1"
                class="mt-1 h-4 w-4 rounded border border-[#191D38]/20 text-[#009AC3] focus:ring-[#009AC3]"
                x-model="confirmTerms"
                {{ old('confirm_terms') ? 'checked' : '' }}
              >
              <span class="text-sm text-[#191D38] font-semibold">
                Ik ga akkoord met de algemene voorwaarden.
                <span class="block text-xs font-medium text-[#191D38]/60 mt-1">
                  <a href="#" class="text-[#009AC3] hover:underline font-semibold">Bekijk de algemene voorwaarden</a>
                </span>
              </span>
            </label>

            @error('confirm_truth')
              <p class="text-xs font-semibold text-red-500">{{ $message }}</p>
            @enderror
            @error('confirm_terms')
              <p class="text-xs font-semibold text-red-500">{{ $message }}</p>
            @enderror
          </div>

          {{-- Footer navigatie --}}
          @php
            $navPrevBtn = "h-11 inline-flex items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";
            $navNextBtn = "h-11 inline-flex items-center justify-center bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer";
          @endphp

          <div class="mt-10 pt-2">
            <div class="grid grid-cols-3 items-center">
              <div class="justify-self-start">
                <a href="{{ route('support.onboarding.step5') }}" class="{{ $navPrevBtn }}">
                  Vorige stap
                </a>
              </div>

              <div class="justify-self-center">
                <p class="text-xs font-bold text-[#191D38]/30">
                  Stap 6 van 6
                </p>
              </div>

              <div class="justify-self-end">
                <button
                  type="submit"
                  class="{{ $navNextBtn }}"
                  :class="(!confirmTruth || !confirmTerms) ? 'opacity-50 pointer-events-none' : ''"
                >
                  Versturen
                </button>
              </div>
            </div>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
@endsection
