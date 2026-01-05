@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
    <div class="flex-1 w-full min-h-0 overflow-y-auto pr-2 pl-1">
      <div class="max-w-2xl w-full mx-auto">

        <form
          method="POST"
          action="{{ route('support.onboarding.step4.store') }}"
          class="w-full basis-full pt-2"
          x-data='{"extras": @json(old("extras", session("onboarding.extras", []))) }'
        >
          @csrf

          <h1 class="text-[#191D38] text-3xl font-black tracking-tight text-center mb-4">Maak het compleet.</h1>

          <div class="w-fit text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold text-center mb-8 px-4 rounded-full py-1.5 mx-auto">
            Maak het compleet en vul je pakket aan. Extra’s om je media naar het volgende level te tillen.
          </div>

          <div class="mb-4">
            <label class="block text-xs font-bold text-[#191D38] mb-2">
              Selecteer extra's
            </label>

            @error('extras')
              <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
            @enderror

            @error('extras.*')
              <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
            @enderror
          </div>

          @php
            $extras = [
              ['key' => 'privacy_check',         'title' => 'Privacy check', 'desc' => 'Kentekens, fotolijstjes in huis onscherp', 'price' => 10],
              ['key' => 'detailfotos',           'title' => 'Detailfoto’s', 'desc' => 'Extra detailbeelden voor keuken, badkamer en afwerking.', 'price' => 25],
              ['key' => 'hoogtefotografie_8m',   'title' => 'Hoogtefotografie 8 meter', 'desc' => 'Fotografie vanaf hoogte voor een ruimtelijker beeld.', 'price' => 25],
              ['key' => 'plattegrond_in_video',  'title' => 'Plattegronden verwerkt in video', 'desc' => 'Voeg plattegronden toe aan de woningvideo voor extra context.', 'price' => 15],
              ['key' => 'tekst_video',           'title' => 'Tekst toevoegen video', 'desc' => 'Bijv. totaal m2, energielabel', 'price' => 15],
              ['key' => 'floorplanner_3d',       'title' => 'Floorplanner plattegronden 3D', 'desc' => '3D visualisatie van de plattegronden voor betere beleving.', 'price' => 10],
              ['key' => 'meubels_toevoegen',     'title' => 'Plattegronden toevoegen: meubels', 'desc' => 'Meubels toevoegen om ruimtes duidelijker te tonen.', 'price' => 10],
              ['key' => 'tuin_toevoegen',        'title' => 'Plattegronden toevoegen: tuin', 'desc' => 'Incl. terras, tuinhuisje, carport, voortuin, oprit', 'price' => 10],
              ['key' => 'artist_impression',     'title' => 'Artist impression', 'desc' => 'Sfeervolle impressie om potentie van de woning te laten zien.', 'price' => 95],
              ['key' => 'woningtekst',           'title' => 'Woningtekst', 'desc' => 'Professionele woningomschrijving klaar voor Funda en social.', 'price' => 85],
              ['key' => 'video_1min',            'title' => '1 minuut video', 'desc' => 'Korte video voor social media en snelle bezichtiging.', 'price' => 15],
              ['key' => 'foto_slideshow',        'title' => 'Foto slideshow', 'desc' => 'Slideshow video van geselecteerde foto’s met rustige flow.', 'price' => 15],
              ['key' => 'levering_24u',          'title' => 'Levering binnen 24 uur', 'desc' => 'Spoedlevering zodat je snel online kunt met je woning.', 'price' => 35],
              ['key' => 'huisstijl_plattegrond', 'title' => 'Plattegronden in eigen huisstijl', 'desc' => 'Plattegronden in jouw kleuren en branding.', 'price' => 10],
              ['key' => 'm2_per_ruimte',         'title' => 'Plattegronden: m2 per ruimte aangegeven', 'desc' => 'Per ruimte de m² duidelijk weergegeven op de plattegrond.', 'price' => 5],
              ['key' => 'style_shoot',           'title' => 'Style shoot', 'desc' => 'Styling op locatie voor een luxe en consistente uitstraling.', 'price' => 40],
            ];

            $extrasSelected = old('extras', session('onboarding.extras', []));
          @endphp

          <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($extras as $e)
              <label
                class="rounded-2xl ring-1 p-4 transition cursor-pointer select-none hover:ring-[#009AC3]/40"
                :class="extras.includes('{{ $e['key'] }}')
                  ? 'ring-[#009AC3]/40 bg-[#009AC3]/[0.03]'
                  : 'ring-[#191D38]/10 bg-white'"
              >
                <div class="flex items-start gap-3">
                  <input
                    type="checkbox"
                    name="extras[]"
                    value="{{ $e['key'] }}"
                    x-model="extras"
                    @checked(in_array($e['key'], $extrasSelected ?? []))
                    class="mt-1 h-4 w-4 rounded border border-[#191D38]/20 text-[#009AC3] focus:ring-[#009AC3]"
                  >

                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-3">
                      <p class="text-sm font-black text-[#191D38] leading-tight">
                        {{ $e['title'] }}
                      </p>

                      <span class="shrink-0 text-xs font-black text-[#191D38]">
                        €{{ $e['price'] }},-
                      </span>
                    </div>

                    <p class="text-xs font-semibold text-[#191D38]/60 mt-1">
                      {{ $e['desc'] }}
                    </p>
                  </div>
                </div>
              </label>
            @endforeach
          </div>

          {{-- Footer navigatie --}}
          @php
            $navPrevBtn = "h-11 inline-flex items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";
            $navNextBtn = "h-11 inline-flex items-center justify-center bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer";
          @endphp

          <div class="mt-10 pt-2">
            <div class="grid grid-cols-3 items-center">
              <div class="justify-self-start">
                <a href="{{ route('support.onboarding.step3') }}" class="{{ $navPrevBtn }}">
                  Vorige stap
                </a>
              </div>

              <div class="justify-self-center">
                <p class="text-xs font-bold text-[#191D38]/30">
                  Stap 4 van 6
                </p>
              </div>

              <div class="justify-self-end">
                <button type="submit" class="{{ $navNextBtn }}">
                  Volgende stap
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
