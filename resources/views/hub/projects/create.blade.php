{{-- resources/views/hub/projects/create.blade.php --}}
@extends('hub.layouts.app')

@section('content')

<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col relative">

    @php
      $crumbLabel = 'Project aanmaken';
      $navPrevBtn = "h-11 inline-flex items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";
      $navNextBtn = "h-11 inline-flex items-center justify-center bg-[#009AC3] text-white rounded-full px-6 font-semibold cursor-pointer hover:bg-[#009AC3]/80 transition";
    @endphp

    <div class="shrink-0 mb-6 flex items-center justify-between absolute left-8 top-8">
      <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-[#191D38]/50">
        <a href="{{ route('support.dashboard') }}" class="hover:text-[#191D38] transition">Dashboard</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.projecten.index') }}" class="hover:text-[#191D38] transition">Projecten</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.projecten.index') }}" class="hover:text-[#191D38] transition">Overzicht</a>
        <span class="opacity-40">/</span>
        <span class="text-[#009AC3]">{{ $crumbLabel }}</span>
      </nav>
    </div>

    <div class="flex-1 min-h-0 flex items-center justify-center">
      <div class="w-full max-w-2xl">
        <div class="text-center mb-4">
          <h1 class="text-[#191D38] text-3xl font-black tracking-tight">Project aanmaken.</h1>
        </div>
        <div class="text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold text-center mb-6 px-4 rounded-full py-1.5">
            Geef het project een naam en kies de startmethode.
        </div>

        @if ($errors->any())
          <div class="mb-6 rounded-2xl bg-[#DF2935]/10 text-[#DF2935] text-xs font-semibold px-4 py-3">
            {{ $errors->first() }}
          </div>
        @endif

        <form
            method="POST"
            action="{{ route('support.projecten.store') }}"
            x-data='{ mode: @json(old("mode", "empty")) }'
        >
          @csrf

          <div class="mb-6">
            <label class="block text-xs font-bold text-[#191D38] mb-2">Projectnaam <span class="text-red-500">*</span></label>
            <input
              type="text"
              name="title"
              value="{{ old('title') }}"
              placeholder="Bijv. Website MK Autobedrijf"
              class="w-full px-4 py-3 rounded-lg ring-1 ring-[#191D38]/10 text-[#191D38] focus:outline-none focus:ring-[#009AC3] transition"
              required
            >
          </div>

        <div class="mb-8">
            <label class="block text-xs font-bold text-[#191D38] mb-2">
                Startmethode <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-2 gap-3">
                {{-- Leeg project --}}
                <label
                class="rounded-2xl ring-1 p-4 transition cursor-pointer select-none hover:ring-[#009AC3]/40"
                :class="mode === 'empty'
                    ? 'ring-[#009AC3]/40 bg-[#009AC3]/[0.03]'
                    : 'ring-[#191D38]/10 bg-white'"
                >
                <div class="flex items-start gap-3">
                    <input
                    type="radio"
                    name="mode"
                    value="empty"
                    x-model="mode"
                    class="mt-1 h-4 w-4 text-[#009AC3] focus:ring-[#009AC3]"
                    >

                    <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-[#191D38] leading-tight">
                        Leeg project
                    </p>
                    <p class="text-xs font-semibold text-[#191D38]/60 mt-1">
                        Start vanaf nul, zonder content.
                    </p>
                    </div>
                </div>
                </label>
                {{-- Sjabloon maken --}}
                <label
                class="rounded-2xl ring-1 p-4 transition cursor-pointer select-none hover:ring-[#009AC3]/40"
                :class="mode === 'template_create'
                    ? 'ring-[#009AC3]/40 bg-[#009AC3]/[0.03]'
                    : 'ring-[#191D38]/10 bg-white'"
                >
                <div class="flex items-start gap-3">
                    <input
                    type="radio"
                    name="mode"
                    value="template_create"
                    x-model="mode"
                    class="mt-1 h-4 w-4 text-[#009AC3] focus:ring-[#009AC3]"
                    >

                    <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-[#191D38] leading-tight">
                        Sjabloon maken
                    </p>
                    <p class="text-xs font-semibold text-[#191D38]/60 mt-1">
                        Maak een project dat je later als template gebruikt.
                    </p>
                    </div>
                </div>
                </label>
                {{-- Sjabloon gebruiken --}}
                <label
                class="rounded-2xl ring-1 p-4 transition cursor-pointer select-none hover:ring-[#009AC3]/40"
                :class="mode === 'template_use'
                    ? 'ring-[#009AC3]/40 bg-[#009AC3]/[0.03]'
                    : 'ring-[#191D38]/10 bg-white'"
                >
                <div class="flex items-start gap-3">
                    <input
                    type="radio"
                    name="mode"
                    value="template_use"
                    x-model="mode"
                    class="mt-1 h-4 w-4 text-[#009AC3] focus:ring-[#009AC3]"
                    >

                    <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-[#191D38] leading-tight">
                        Sjabloon gebruiken
                    </p>
                    <p class="text-xs font-semibold text-[#191D38]/60 mt-1">
                        Start op basis van een bestaand sjabloon.
                    </p>
                    </div>
                </div>
                </label>
                {{-- Kopieer bestaand project --}}
                <label
                class="rounded-2xl ring-1 p-4 transition cursor-pointer select-none hover:ring-[#009AC3]/40"
                :class="mode === 'copy_existing'
                    ? 'ring-[#009AC3]/40 bg-[#009AC3]/[0.03]'
                    : 'ring-[#191D38]/10 bg-white'"
                >
                <div class="flex items-start gap-3">
                    <input
                    type="radio"
                    name="mode"
                    value="copy_existing"
                    x-model="mode"
                    class="mt-1 h-4 w-4 text-[#009AC3] focus:ring-[#009AC3]"
                    >
                    <div class="flex-1 min-w-0">
                    <p class="text-sm font-black text-[#191D38] leading-tight">
                        Kopieer van bestaand project
                    </p>
                    <p class="text-xs font-semibold text-[#191D38]/60 mt-1">
                        Maak een kopie van een bestaand project (taken, financiën, etc.).
                    </p>
                    </div>
                </div>
                </label>
            </div>
        </div>

{{-- ✅ Extra keuzevelden afhankelijk van mode --}}
<div class="mt-4 space-y-3 mb-8">

  {{-- Sjabloon kiezen --}}
  <div
    x-cloak
    x-show="mode === 'template_use'"
    x-transition
  >
    <label class="block text-xs font-bold text-[#191D38] mb-2">
      Kies een sjabloon <span class="text-red-500">*</span>
    </label>

    <select
      name="template_project_id"
      class="h-11 w-full bg-white ring-1 ring-[#191D38]/10 rounded-full px-4 text-xs text-[#191D38] font-semibold outline-none focus:ring-[#009AC3] transition cursor-pointer"
    >
      <option value="" disabled {{ old('template_project_id') ? '' : 'selected' }}>
        Selecteer een sjabloon...
      </option>

      @foreach(($templates ?? collect()) as $t)
        <option value="{{ $t->id }}" {{ (string)old('template_project_id') === (string)$t->id ? 'selected' : '' }}>
          #{{ $t->id }} — {{ $t->title ?? ('Project '.$t->id) }}
        </option>
      @endforeach
    </select>

    @error('template_project_id')
      <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
    @enderror
  </div>

  {{-- Project kopiëren --}}
  <div
    x-cloak
    x-show="mode === 'copy_existing'"
    x-transition
  >
    <label class="block text-xs font-bold text-[#191D38] mb-2">
      Kies project om te kopiëren <span class="text-red-500">*</span>
    </label>

    <select
      name="copy_project_id"
      class="h-11 w-full bg-white ring-1 ring-[#191D38]/10 rounded-full px-4 text-xs text-[#191D38] font-semibold outline-none focus:ring-[#009AC3] transition cursor-pointer"
    >
      <option value="" disabled {{ old('copy_project_id') ? '' : 'selected' }}>
        Selecteer een project...
      </option>

      @foreach(($projects ?? collect()) as $p)
        <option value="{{ $p->id }}" {{ (string)old('copy_project_id') === (string)$p->id ? 'selected' : '' }}>
          #{{ $p->id }} — {{ $p->title ?? ('Project '.$p->id) }}
        </option>
      @endforeach
    </select>

    @error('copy_project_id')
      <p class="text-xs font-semibold text-red-500 mt-2">{{ $message }}</p>
    @enderror
  </div>

</div>

          <div class="flex items-center justify-between gap-3 pt-4">
            <a href="{{ route('support.projecten.index') }}" class="{{ $navPrevBtn }}">Annuleren</a>
            <button type="submit" class="{{ $navNextBtn }}">Project aanmaken</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
@endsection
