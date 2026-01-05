@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
    <div class="flex-1 w-full min-h-0 overflow-y-auto">
      <div class="max-w-2xl w-full h-full mx-auto flex items-center justify-center">

        <form method="POST" action="{{ route('support.onboarding.step2.store') }}" class="w-full basis-full">
          @csrf

          {{-- Contactpersoon --}}
          <h1 class="text-[#191D38] text-3xl font-black tracking-tight text-center mb-4">Contactpersoon & Makelaardij.</h1>

          <div class="text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold text-center mb-6 px-4 rounded-full py-1.5">
            Bijna halverwege! We hebben nog wat contactgegevens nodig om je aanvraag te verwerken.
          </div>

          <div class="grid gap-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Voornaam Contactpersoon <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="contact_first_name"
                  value="{{ old('contact_first_name', session('onboarding.contact_first_name')) }}"
                  placeholder="Voornaam"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('contact_first_name')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Achternaam Contactpersoon <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="contact_last_name"
                  value="{{ old('contact_last_name', session('onboarding.contact_last_name')) }}"
                  placeholder="Achternaam"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('contact_last_name')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Email Contactpersoon <span class="text-red-500">*</span>
                </label>
                <input
                  type="email"
                  name="contact_email"
                  value="{{ old('contact_email', session('onboarding.contact_email')) }}"
                  placeholder="naam@voorbeeld.nl"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('contact_email')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Telefoonnummer Contactpersoon <span class="text-red-500">*</span>
                </label>
                <input
                  type="tel"
                  name="contact_phone"
                  value="{{ old('contact_phone', session('onboarding.contact_phone')) }}"
                  placeholder="06 1234 5678"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('contact_phone')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Makelaardij gegevens --}}
          <div class="grid gap-4 mt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Voornaam Makelaar <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="agency_first_name"
                  value="{{ old('agency_first_name', session('onboarding.agency_first_name')) }}"
                  placeholder="Voornaam"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('agency_first_name')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Achternaam Makelaar <span class="text-red-500">*</span>
                </label>
                <input
                  type="text"
                  name="agency_last_name"
                  value="{{ old('agency_last_name', session('onboarding.agency_last_name')) }}"
                  placeholder="Achternaam"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('agency_last_name')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Email Makelaar <span class="text-red-500">*</span>
                </label>
                <input
                  type="email"
                  name="agency_email"
                  value="{{ old('agency_email', session('onboarding.agency_email')) }}"
                  placeholder="admin@voorbeeld.nl"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('agency_email')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>

              <div>
                <label class="block text-xs font-bold text-[#191D38] mb-2">
                  Telefoonnummer Makelaar <span class="text-red-500">*</span>
                </label>
                <input
                  type="tel"
                  name="agency_phone"
                  value="{{ old('agency_phone', session('onboarding.agency_phone')) }}"
                  placeholder="06 1234 5678"
                  class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
                >
                @error('agency_phone')
                  <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
                @enderror
              </div>
            </div>
          </div>

          {{-- Updates checkbox --}}
          <div class="mt-8">
            <label class="flex items-start gap-3 cursor-pointer select-none">
              <input
                type="checkbox"
                name="contact_updates"
                value="1"
                {{ old('contact_updates', session('onboarding.contact_updates')) ? 'checked' : '' }}
                class="mt-1 h-4 w-4 rounded border border-[#191D38]/20 text-[#009AC3] focus:ring-[#009AC3]"
              >
              <span class="text-sm text-[#191D38] font-semibold">
                Contactpersoon op de hoogte houden
                <span class="block text-xs font-medium text-[#191D38]/60 mt-1">
                  Stuur de contactpersoon updates over de status van de aanvraag.
                </span>
              </span>
            </label>
          </div>

          {{-- Footer navigatie --}}
          @php
            $navPrevBtn = "h-11 inline-flex items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";
            $navNextBtn = "h-11 inline-flex items-center justify-center bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer";
          @endphp

          <div class="mt-10 pt-2">
            <div class="grid grid-cols-3 items-center">
              <div class="justify-self-start">
                <a href="{{ route('support.onboarding.step1') }}" class="{{ $navPrevBtn }}">
                  Vorige stap
                </a>
              </div>

              <div class="justify-self-center">
                <p class="text-xs font-bold text-[#191D38]/30">
                  Stap 2 van 6
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
