{{-- resources/views/hub/projecten/show.blade.php --}}
@extends('hub.layouts.app')

@section('content')
<style>
  [x-cloak]{display:none!important;}

  /* Dunne, subtiele scrollbar zonder pijltjes (voor status dropdown) */
  .custom-scroll{
    scrollbar-width: thin;
    scrollbar-color: #191D3820 transparent;
  }
  .custom-scroll::-webkit-scrollbar{ width:4px; height:4px; }
  .custom-scroll::-webkit-scrollbar-track{ background:transparent; }
  .custom-scroll::-webkit-scrollbar-thumb{ background-color:#191D3820; border-radius:9999px; }
  .custom-scroll::-webkit-scrollbar-button{ width:0; height:0; display:none; }
</style>
@php
  $req = $project->onboardingRequest;

  // status labels (project)
  $status = $project->status ?? 'active';

  $statusLabel = match($status) {
    'active' => 'Actief',
    'pending' => 'In afwachting',
    'done' => 'Afgerond',
    'archived' => 'Gearchiveerd',
    default => ucfirst((string) $status),
  };

  $statusClass = match($status) {
    'active' => 'text-[#87A878] bg-[#87A878]/20',
    'pending' => 'text-[#DF9A57] bg-[#DF9A57]/20',
    'done' => 'text-[#2A324B] bg-[#2A324B]/20',
    'archived' => 'text-[#DF9A57] bg-[#DF9A57]/20',
    default => 'text-[#2A324B] bg-[#2A324B]/20',
  };

  // UI helper classes (EXACT dezelfde stijl als onboarding show)
  $sectionWrap   = "overflow-hidden rounded-2xl";
  $sectionHeader = "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = "bg-[#191D38]/5";
  $labelClass    = "text-[#191D38] font-bold text-xs opacity-50";
  $valueClass    = "text-[#191D38] text-sm font-semibold";

  $navPrevBtn = "h-9 inline-flex text-xs items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";

  // Map query (Google Maps embed)
  $fullAddress = trim(($req->address ?? '') . ', ' . ($req->postcode ?? '') . ' ' . ($req->city ?? ''));
  $mapsQ = urlencode($fullAddress);

  // Breadcrumb label
  $crumbLabel = trim(($req->address ?? '') . ' — ' . ($req->city ?? ''));
  $crumbLabel = $crumbLabel !== ' — ' ? $crumbLabel : ($project->title ?? 'Project');

  // Finance helpers
  $fmtCents = fn($cents) => '€' . number_format(((int)$cents) / 100, 2, ',', '.');
  $financeTotal = (int) ($project->financeItems?->sum('total_cents') ?? 0);

  // Category pill (zelfde pill-stijl als status)
  $categoryLabel = $project->category ?? 'project';
  $categoryClass = 'text-[#009AC3] bg-[#009AC3]/20';
@endphp

<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

    {{-- Breadcrumbs (sticky / altijd zichtbaar) --}}
    <div class="shrink-0 mb-4 flex items-center justify-between">
      <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-[#191D38]/50">
        <a href="{{ route('support.dashboard') }}" class="hover:text-[#191D38] transition">
          Dashboard
        </a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.projecten.index') }}" class="hover:text-[#191D38] transition">
          Projecten
        </a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.projecten.index') }}" class="hover:text-[#191D38] transition">
          Overzicht
        </a>
        <span class="opacity-40">/</span>
        <span class="text-[#009AC3]">
          {{ $crumbLabel }}
        </span>
      </nav>

      <div class="flex items-center gap-4">
        <a href="{{ route('support.projecten.index') }}" class="{{ $navPrevBtn }}">
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
                        <p class="{{ $valueClass }}">{{ $req->address ?? '—' }}</p>
                      </div>
                      <div>
                        <p class="{{ $labelClass }} mb-1">Postcode & Plaats</p>
                        <p class="{{ $valueClass }}">{{ $req->postcode ?? '—' }} — {{ $req->city ?? '—' }}</p>
                      </div>
                    </div>

                    <div class="mt-4 pt-4 border-t border-[#191D38]/10">
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                          <p class="{{ $labelClass }} mb-1">Oppervlakte woning</p>
                          <p class="{{ $valueClass }}">{{ (int) ($req->surface_home ?? 0) }} m²</p>
                        </div>
                        <div>
                          <p class="{{ $labelClass }} mb-1">Bijgebouwen</p>
                          <p class="{{ $valueClass }}">{{ (int) ($req->surface_outbuildings ?? 0) }} m²</p>
                        </div>
                        <div>
                          <p class="{{ $labelClass }} mb-1">Perceel</p>
                          <p class="{{ $valueClass }}">{{ (int) ($req->surface_plot ?? 0) }} m²</p>
                        </div>
                      </div>
                    </div>

                    {{-- Planning (ruimte vullen, puur data) --}}
                    <div class="mt-4 pt-4 border-t border-[#191D38]/10">
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                          <p class="{{ $labelClass }} mb-1">Datum</p>
                          <p class="{{ $valueClass }}">
                            {{ optional($req->shoot_date)->format('d-m-Y') ?? ($req->shoot_date ?? '—') }}
                          </p>
                        </div>
                        <div>
                          <p class="{{ $labelClass }} mb-1">Tijdsblok</p>
                          <p class="{{ $valueClass }}">{{ $req->shoot_slot ?? '—' }}</p>
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
                      {{ $req->contact_first_name ?? '—' }} {{ $req->contact_last_name ?? '' }}
                    </p>
                  </div>
                  <div>
                    <p class="{{ $labelClass }} mb-1">Updates</p>
                    <p class="{{ $valueClass }}">
                      {{ ($req->contact_updates ?? false) ? 'Ja, op de hoogte houden' : 'Nee' }}
                    </p>
                  </div>
                </div>
              </div>

              <div class="border-t border-[#191D38]/10 px-6 py-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <p class="{{ $labelClass }} mb-1">E-mail</p>
                    <p class="{{ $valueClass }}">{{ $req->contact_email ?? '—' }}</p>
                  </div>
                  <div>
                    <p class="{{ $labelClass }} mb-1">Telefoon</p>
                    <p class="{{ $valueClass }}">{{ $req->contact_phone ?? '—' }}</p>
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
                      {{ $req->agency_first_name ?? '—' }} {{ $req->agency_last_name ?? '' }}
                    </p>
                  </div>
                  <div>
                    <p class="{{ $labelClass }} mb-1">Telefoon</p>
                    <p class="{{ $valueClass }}">{{ $req->agency_phone ?? '—' }}</p>
                  </div>
                </div>
              </div>

              <div class="border-t border-[#191D38]/10 px-6 py-4">
                <div>
                  <p class="{{ $labelClass }} mb-1">E-mail</p>
                  <p class="{{ $valueClass }}">{{ $req->agency_email ?? '—' }}</p>
                </div>
              </div>
            </div>
          </div>

          <hr class="border-[#191D38]/10 col-span-2 my-4">

          {{-- Project Tasks --}}
          @include('hub.projects.partials.tasks', [
            'project' => $project,
            'assignees' => $assignees,
            'sectionHeader' => $sectionHeader,
            'sectionBody' => $sectionBody,
          ])

          <hr class="border-[#191D38]/10 col-span-2 my-4">

          {{-- Finance --}}
          @include('hub.projects.partials.finance', [
            'project' => $project,
            'sectionWrap' => $sectionWrap,
            'sectionHeader' => $sectionHeader,
            'sectionBody' => $sectionBody,
          ])

          <hr class="border-[#191D38]/10 col-span-2 my-4">

          {{-- Planning --}}
          <div class="col-span-2">
            <div class="{{ $sectionWrap }}">
              <div class="{{ $sectionHeader }}">
                <p class="text-[#191D38] font-black text-sm">Planning</p>
              </div>

              <div class="{{ $sectionBody }}">
                <div class="px-6 py-2 divide-y divide-[#191D38]/10">
                  @forelse($project->planningItems as $p)
                    <div class="py-3 grid grid-cols-[1fr_0.35fr] items-start gap-6">
                      <div class="min-w-0">
                        <p class="text-[#191D38] font-semibold text-sm">
                          {{ $p->notes ?? 'Planning item' }}
                        </p>
                        <p class="text-xs font-semibold text-[#191D38]/50 mt-0.5">
                          {{ $p->location ?? '—' }}
                        </p>
                      </div>

                      <div class="text-right">
                        <p class="text-[#009AC3] text-sm font-semibold">
                          {{ optional($p->start_at)->format('d-m-Y H:i') ?? '—' }}
                          @if(!empty($p->end_at))
                            → {{ optional($p->end_at)->format('H:i') }}
                          @endif
                        </p>
                        <p class="text-xs font-semibold text-[#191D38]/50 mt-0.5">
                          {{ $p->assignee?->name ?? '—' }}
                        </p>
                      </div>
                    </div>
                  @empty
                    <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
                      Nog geen planning.
                    </div>
                  @endforelse
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>
<script>
  document.addEventListener('htmx:afterSwap', function (evt) {
    var target = evt.target;
    if (!target) return;

    if (window.Alpine && typeof window.Alpine.initTree === 'function') {
      if (target.id === 'project-tasks' || target.id === 'project-finance') {
        window.Alpine.initTree(target);
      }
    }
  });
</script>
@endsection