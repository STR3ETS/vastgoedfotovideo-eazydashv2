@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
    <div class="flex-1 w-full min-h-0 overflow-y-auto">
      <div class="max-w-2xl w-full h-full mx-auto flex items-center justify-center">

        <form method="POST" action="{{ route('support.onboarding.step1.store') }}" class="w-full basis-full">
          @csrf

          {{-- Over de locatie --}}
          <h1 class="text-[#191D38] text-3xl font-black tracking-tight text-center mb-4">Over de locatie.</h1>

          <div class="text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold text-center mb-6 px-4 rounded-full py-1.5">
            Geweldig! Laten we beginnen met de locatiegegevens. Dit helpt ons om de juiste producten voor je te selecteren.
          </div>

          <div class="grid gap-4">
            {{-- Adres --}}
            <div>
              <label class="block text-xs font-bold text-[#191D38] mb-2">
                Adres <span class="text-red-500">*</span>
              </label>
              <input
                type="text"
                name="address"
                value="{{ old('address', session('onboarding.address')) }}"
                placeholder="Straatnaam 1 a"
                class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
              >
              @error('address')
                <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
              @enderror
            </div>

            {{-- Postcode + Plaatsnaam --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Postcode <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="postcode"
                  value="{{ old('postcode', session('onboarding.postcode')) }}"
                  placeholder="1234 AB"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('postcode')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Plaatsnaam <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="city"
                  value="{{ old('city', session('onboarding.city')) }}"
                  placeholder="Veenendaal"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('city')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Oppervlakte --}}
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8">
            <div>
              <label class="block text-xs font-bold text-[#191D38] mb-2">
                Oppervlakte woning (m²)
              </label>
              <input
                type="number"
                min="0"
                name="surface_home"
                value="{{ old('surface_home', session('onboarding.surface_home', 0)) }}"
                class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
              >
              @error('surface_home')
                <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label class="block text-xs font-bold text-[#191D38] mb-2">
                Oppervlakte bijgebouwen (m²)
              </label>
              <input
                type="number"
                min="0"
                name="surface_outbuildings"
                value="{{ old('surface_outbuildings', session('onboarding.surface_outbuildings', 0)) }}"
                class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
              >
              @error('surface_outbuildings')
                <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label class="block text-xs font-bold text-[#191D38] mb-2">
                Oppervlakte perceel (m²)
              </label>
              <input
                type="number"
                min="0"
                name="surface_plot"
                value="{{ old('surface_plot', session('onboarding.surface_plot', 0)) }}"
                class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
              >
              @error('surface_plot')
                <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
              @enderror
            </div>
          </div>

          {{-- Footer navigatie --}}
          @php
            $navPrevBtn = "h-11 inline-flex items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";
            $navNextBtn = "h-11 inline-flex items-center justify-center bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer";
          @endphp

          <div class="mt-10 pt-2">
            <div class="grid grid-cols-3 items-center">
              <div class="justify-self-start">
                <a href="{{ route('support.onboarding.create') }}" class="{{ $navPrevBtn }}">
                  Vorige stap
                </a>
              </div>

              <div class="justify-self-center">
                <p class="text-xs font-bold text-[#191D38]/30">
                  Stap 1 van 6
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
