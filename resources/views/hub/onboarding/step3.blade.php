@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
    <div class="flex-1 w-full min-h-0 overflow-y-auto pr-2 pl-1">
      <div class="w-full mx-auto">

        <form
          method="POST"
          action="{{ route('support.onboarding.step3.store') }}"
          class="w-full basis-full pt-2"
          x-data='{
            selected: @json(old("package", session("onboarding.package")))
          }'
        >
          @csrf

          <h1 class="text-[#191D38] text-3xl font-black tracking-tight text-center mb-4">Kies je pakket.</h1>

          <div class="w-fit text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold text-center mb-6 px-4 rounded-full py-1.5 mx-auto">
            Kies het pakket dat het beste bij je wensen past. Elk pakket bevat verschillende diensten om je zo goed mogelijk van dienst te zijn.
          </div>

          <div class="mb-4">
            <label class="block text-xs font-bold text-[#191D38] mb-2">
              Selecteer een pakket <span class="text-red-500">*</span>
            </label>

            @error('package')
              <p class="text-xs font-semibold text-red-500 mb-2">{{ $message }}</p>
            @enderror
          </div>

          @php
            // ✅ Alle mogelijke punten (zelfde lijst voor iedereen)
            $allBullets = [
              "Fotografie: 15-45 foto’s",
              "360° fotografie",
              "Woningvideo",
              "Inmeten + rapport",
              "Plattegronden",
              "Dronefotografie: 5 foto’s",
              "Woningvideo met plattegrond",
              "Gemeubileerde 3D plattegronden + tuin",
              "Privacy check",
              "Voorfoto",
              "Dronevideo: 2-5 video’s",
              "Detailfoto’s: 10 foto’s",
              "m2 per plattegrond",
              "Woningtekst",
              "Buitenfotografie: 4-10 foto’s",
              "360° fotografie buiten",
              "Buitenvideo",
            ];

            $makeSet = fn($arr) => array_fill_keys($arr, true);

            // ✅ Prijs aflopend (duurste eerst)
            $packages = [
              [
                'key' => 'pro',
                'title' => 'Pro pakket',
                'subtitle' => "Alles van Plus + interactieve tools.",
                'price' => '€509,-',
                'bullets' => $makeSet([
                  "Fotografie: 15-45 foto’s",
                  "360° fotografie",
                  "Woningvideo",
                  "Inmeten + rapport",
                  "Plattegronden",
                  "Dronefotografie: 5 foto’s",
                  "Woningvideo met plattegrond",
                  "Gemeubileerde 3D plattegronden + tuin",
                  "Privacy check",
                  "Voorfoto",
                  "Dronevideo: 2-5 video’s",
                  "Detailfoto’s: 10 foto’s",
                  "m2 per plattegrond",
                  "Woningtekst",
                ]),
              ],
              [
                'key' => 'plus',
                'title' => 'Plus pakket',
                'subtitle' => "Alles van Essentials + extra functies & beelden.",
                'price' => '€385,-',
                'bullets' => $makeSet([
                  "Fotografie: 15-45 foto’s",
                  "360° fotografie",
                  "Woningvideo",
                  "Inmeten + rapport",
                  "Plattegronden",
                  "Dronefotografie: 5 foto’s",
                  "Woningvideo met plattegrond",
                  "Gemeubileerde 3D plattegronden + tuin",
                  "Privacy check",
                  "Voorfoto",
                ]),
              ],
              [
                'key' => 'essentials',
                'title' => 'Essentials pakket',
                'subtitle' => "Compleet pakket met foto’s en video’s.",
                'price' => '€325,-',
                'bullets' => $makeSet([
                  "Fotografie: 15-45 foto’s",
                  "360° fotografie",
                  "Woningvideo",
                  "Inmeten + rapport",
                  "Plattegronden",
                ]),
              ],
              [
                'key' => 'media',
                'title' => 'Media pakket',
                'subtitle' => "Foto- en videografie + 360 graden foto’s.",
                'price' => '€260,-',
                'bullets' => $makeSet([
                  "Fotografie: 15-45 foto’s",
                  "360° fotografie",
                  "Woningvideo",
                ]),
              ],
              [
                'key' => 'buiten',
                'title' => 'Buiten pakket',
                'subtitle' => "Foto’s, video en 360 fotografie buiten.",
                'price' => '€75,-',
                'bullets' => $makeSet([
                  "Buitenfotografie: 4-10 foto’s",
                  "360° fotografie buiten",
                  "Buitenvideo",
                ]),
              ],
              [
                'key' => 'funda_klaar',
                'title' => 'Funda klaar pakket',
                'subtitle' => "Alle essentials direct klaar voor Funda.",
                'price' => '€50,-',
                'bullets' => $makeSet([
                  "Fotografie: 15-45 foto’s",
                  "Inmeten + rapport",
                  "Plattegronden",
                ]),
              ],
            ];

            // ✅ max 5 eerst
            $previewCount = 5;

            $selectedServer = old('package', session('onboarding.package'));
          @endphp

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-stretch">
            @foreach($packages as $p)
              <label class="block cursor-pointer select-none h-full">
                <input
                  type="radio"
                  name="package"
                  value="{{ $p['key'] }}"
                  class="sr-only"
                  x-model="selected"
                  @checked($selectedServer === $p['key'])
                >

                <div
                  class="rounded-2xl ring-1 p-5 transition hover:ring-[#009AC3]/40 flex flex-col h-full min-h-[360px]"
                  :class="selected === '{{ $p['key'] }}'
                    ? 'ring-[#009AC3]/40 bg-[#009AC3]/[0.03]'
                    : 'ring-[#191D38]/10 bg-white'"
                >
                  <div class="flex items-start justify-between gap-3 mb-2">
                    <div>
                      <p class="text-[#191D38] font-black text-sm">{{ $p['title'] }}</p>
                      <p class="text-[#191D38]/60 text-xs font-semibold mt-1">{{ $p['subtitle'] }}</p>
                    </div>

                    {{-- ✅ Bolletje rechtsboven: bij selected -> blauw + wit vinkje --}}
                    <div
                      class="h-5 w-5 rounded-full ring-1 flex items-center justify-center transition shrink-0"
                      :class="selected === '{{ $p['key'] }}'
                        ? 'bg-[#009AC3] ring-[#009AC3]'
                        : 'bg-white ring-[#191D38]/10'"
                    >
                      <i
                        class="fa-solid fa-check text-[10px] text-white"
                        x-show="selected === '{{ $p['key'] }}'"
                      ></i>
                    </div>
                  </div>

                  <div class="text-[#191D38] text-2xl font-black my-3">
                    {{ $p['price'] }}
                  </div>

                  {{-- ✅ Bullet preview (max 5) --}}
                  <div class="grid gap-2 mb-3">
                    @foreach(array_slice($allBullets, 0, $previewCount) as $b)
                      @php $has = isset($p['bullets'][$b]); @endphp
                      <div class="flex items-start gap-2 {{ $has ? '' : 'opacity-40' }}">
                        @if($has)
                          <i class="fa-solid fa-check text-[#009AC3] text-[11px] mt-0.5"></i>
                        @else
                          <i class="fa-solid fa-xmark text-[#191D38] text-[11px] mt-0.5"></i>
                        @endif
                        <p class="text-[#191D38] text-xs font-semibold">{{ $b }}</p>
                      </div>
                    @endforeach
                  </div>

                  {{-- ✅ Expand/collapse --}}
                  <details class="group">
                    <summary class="list-none cursor-pointer select-none">
                      <div class="inline-flex items-center gap-2 text-xs font-bold text-[#009AC3]">
                        <span class="group-open:hidden">Toon alle punten</span>
                        <span class="hidden group-open:inline">Toon minder</span>
                        <i class="fa-solid fa-chevron-down text-[10px] transition group-open:rotate-180"></i>
                      </div>
                    </summary>

                    <div class="grid gap-2 mt-3">
                      @foreach(array_slice($allBullets, $previewCount) as $b)
                        @php $has = isset($p['bullets'][$b]); @endphp
                        <div class="flex items-start gap-2 {{ $has ? '' : 'opacity-40' }}">
                          @if($has)
                            <i class="fa-solid fa-check text-[#009AC3] text-[11px] mt-0.5"></i>
                          @else
                            <i class="fa-solid fa-xmark text-[#191D38] text-[11px] mt-0.5"></i>
                          @endif
                          <p class="text-[#191D38] text-xs font-semibold">{{ $b }}</p>
                        </div>
                      @endforeach
                    </div>
                  </details>

                  {{-- ✅ Knop onderaan: bij selected -> exact zoals Next knop --}}
                  <div class="mt-auto pt-4">
                    <div
                      class="h-11 inline-flex items-center justify-center w-full rounded-full font-semibold text-xs transition duration-200"
                      :class="selected === '{{ $p['key'] }}'
                        ? 'bg-[#009AC3] hover:bg-[#009AC3]/70 text-white'
                        : 'ring-1 ring-[#191D38]/10 text-[#191D38] bg-white hover:ring-[#009AC3]/40'"
                    >
                      <span x-show="selected !== '{{ $p['key'] }}'">Selecteer pakket</span>

                      <span x-show="selected === '{{ $p['key'] }}'" class="inline-flex items-center gap-2">
                        <i class="fa-solid fa-check text-[12px]"></i>
                        Geselecteerd
                      </span>
                    </div>
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
                <a href="{{ route('support.onboarding.step2') }}" class="{{ $navPrevBtn }}">
                  Vorige stap
                </a>
              </div>

              <div class="justify-self-center">
                <p class="text-xs font-bold text-[#191D38]/30">
                  Stap 3 van 6
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
