{{-- resources/views/hub/gebruikers/show.blade.php --}}
@extends('hub.layouts.app')

@section('content')
@php
  $crumbLabel = 'Gebruiker';

  $q    = (string) request()->query('q', '');
  $sort = (string) request()->query('sort', 'newest');

  // ✅ rol mag leeg zijn (alle gebruikers). Als leeg: geen rol in back url.
  $rolRaw = trim((string) request()->query('rol', ''));
  $rol    = $rolRaw !== '' ? $rolRaw : null;

  $isAdmin = (auth()->user()->rol ?? null) === 'admin';

  // ✅ Dit is de gebruiker die je bewerkt
  $u = $targetUser ?? $user;

  // Data vanuit controller (default safe)
  $planningItems = $planningItems ?? collect();
  $projects      = $projects ?? collect();

  $backUrl = route('support.gebruikers.index');
  $backQs  = array_filter([
    'rol'  => $rol ?: null,
    'q'    => $q ?: null,
    'sort' => $sort ?: null,
  ]);
  if (!empty($backQs)) {
    $backUrl .= '?' . http_build_query($backQs);
  }

  $roles = [
    'klant'          => 'Klant',
    'admin'          => 'Admin',
    'team-manager'   => 'Team manager',
    'client-manager' => 'Klant manager',
    'fotograaf'      => 'Fotograaf',
  ];

  // Werkuren: verwacht array/json
  $wh = is_array($u->work_hours ?? null) ? $u->work_hours : [];
  $days = [
    'monday'    => 'Maandag',
    'tuesday'   => 'Dinsdag',
    'wednesday' => 'Woensdag',
    'thursday'  => 'Donderdag',
    'friday'    => 'Vrijdag',
    'saturday'  => 'Zaterdag',
    'sunday'    => 'Zondag',
  ];
@endphp

<div class="col-span-5 flex-1 min-h-0" x-data="{ tab: 'gegevens' }">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

    {{-- Breadcrumbs --}}
    <div class="shrink-0 mb-4 flex items-center justify-between">
      <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-[#191D38]/50">
        <a href="{{ route('support.dashboard') }}" class="hover:text-[#191D38] transition">Dashboard</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.gebruikers.index') }}" class="hover:text-[#191D38] transition">Gebruikers</a>
        <span class="opacity-40">/</span>
        <span class="text-[#009AC3]">{{ $crumbLabel }}</span>
      </nav>

      <div class="flex items-center gap-3">
        <a
          href="{{ $backUrl }}"
          class="h-9 inline-flex text-xs items-center justify-center bg-white border border-gray-200 hover:bg-gray-50 transition duration-200 px-6 text-[#191D38] rounded-full font-semibold cursor-pointer"
        >
          Terug
        </a>
      </div>
    </div>

    {{-- Header --}}
    <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-tl-2xl rounded-tr-2xl">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-[#191D38] font-black text-sm">
            {{ $u->name ?? 'Gebruiker' }}
          </p>
          <p class="text-[#191D38]/60 text-xs font-semibold">
            {{ $u->email ?? '—' }}
          </p>
        </div>

        <div class="text-xs font-semibold text-[#191D38]/50">
          ID: <span class="text-[#191D38] font-black">{{ $u->id }}</span>
        </div>
      </div>
    </div>

    {{-- Tabs --}}
    <div class="shrink-0 px-6 py-3 bg-white border-x border-gray-200">
      <div class="flex items-center gap-2">
        @php
          $tabBtnBase = "h-9 inline-flex items-center justify-center px-4 rounded-full text-xs font-semibold border transition";
          $tabActive  = "bg-[#009AC3]/10 border-[#009AC3]/30 text-[#009AC3]";
          $tabIdle    = "bg-white border-gray-200 text-[#191D38]/60 hover:text-[#191D38] hover:bg-gray-50";
        @endphp

        <button type="button" @click="tab='gegevens'" class="{{ $tabBtnBase }}" :class="tab==='gegevens' ? '{{ $tabActive }}' : '{{ $tabIdle }}'">Gegevens</button>
        <button type="button" @click="tab='werkuren'" class="{{ $tabBtnBase }}" :class="tab==='werkuren' ? '{{ $tabActive }}' : '{{ $tabIdle }}'">Werkuren</button>
        <button type="button" @click="tab='planning'" class="{{ $tabBtnBase }}" :class="tab==='planning' ? '{{ $tabActive }}' : '{{ $tabIdle }}'">Planning</button>
        <button type="button" @click="tab='projecten'" class="{{ $tabBtnBase }}" :class="tab==='projecten' ? '{{ $tabActive }}' : '{{ $tabIdle }}'">Projecten</button>
      </div>
    </div>

    {{-- Body --}}
    <div class="flex-1 min-h-0 bg-[#191D38]/5 overflow-y-auto rounded-bl-2xl rounded-br-2xl">
      <div class="px-6 py-6">

        {{-- ✅ UPDATE FORM: bevat Gegevens + Werkuren (zelfde PATCH) --}}
        <form id="user-update-form" method="POST" action="{{ route('support.gebruikers.update', $u) }}">
          @csrf
          @method('PATCH')

          {{-- GEGEVENS --}}
          <div x-show="tab==='gegevens'" x-cloak class="grid gap-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="text-[#191D38] font-bold text-xs opacity-50">Naam *</label>
                <input
                  name="name"
                  value="{{ old('name', $u->name) }}"
                  required
                  @disabled(!$isAdmin)
                  class="h-9 bg-white border border-gray-200 flex items-center px-4 w-full rounded-full text-xs text-[#191D38] font-medium outline-none disabled:bg-gray-50 disabled:text-[#191D38]/60"
                >
              </div>

              <div>
                <label class="text-[#191D38] font-bold text-xs opacity-50">E-mail *</label>
                <input
                  type="email"
                  name="email"
                  value="{{ old('email', $u->email) }}"
                  required
                  @disabled(!$isAdmin)
                  class="h-9 bg-white border border-gray-200 flex items-center px-4 w-full rounded-full text-xs text-[#191D38] font-medium outline-none disabled:bg-gray-50 disabled:text-[#191D38]/60"
                >
              </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="text-[#191D38] font-bold text-xs opacity-50">Rol *</label>
                <select
                  name="rol"
                  required
                  @disabled(!$isAdmin)
                  class="h-9 bg-white border border-gray-200 pl-4 pr-10 rounded-full text-xs text-[#191D38] font-medium outline-none appearance-none cursor-pointer w-full disabled:bg-gray-50 disabled:text-[#191D38]/60"
                >
                  @foreach($roles as $slug => $label)
                    <option value="{{ $slug }}" @selected(old('rol', $u->rol) === $slug)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>

              <div>
                <label class="text-[#191D38] font-bold text-xs opacity-50">Telefoon</label>
                <input
                  name="phone"
                  value="{{ old('phone', $u->phone) }}"
                  @disabled(!$isAdmin)
                  class="h-9 bg-white border border-gray-200 flex items-center px-4 w-full rounded-full text-xs text-[#191D38] font-medium outline-none disabled:bg-gray-50 disabled:text-[#191D38]/60"
                >
              </div>

              <div>
                <label class="text-[#191D38] font-bold text-xs opacity-50">Postcode</label>
                <input
                  name="postal_code"
                  value="{{ old('postal_code', $u->postal_code) }}"
                  @disabled(!$isAdmin)
                  class="h-9 bg-white border border-gray-200 flex items-center px-4 w-full rounded-full text-xs text-[#191D38] font-medium outline-none disabled:bg-gray-50 disabled:text-[#191D38]/60"
                >
              </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
              <div class="col-span-2">
                <label class="text-[#191D38] font-bold text-xs opacity-50">Adres</label>
                <input
                  name="address"
                  value="{{ old('address', $u->address) }}"
                  @disabled(!$isAdmin)
                  class="h-9 bg-white border border-gray-200 flex items-center px-4 w-full rounded-full text-xs text-[#191D38] font-medium outline-none disabled:bg-gray-50 disabled:text-[#191D38]/60"
                >
              </div>

              <div>
                <label class="text-[#191D38] font-bold text-xs opacity-50">Stad</label>
                <input
                  name="city"
                  value="{{ old('city', $u->city) }}"
                  @disabled(!$isAdmin)
                  class="h-9 bg-white border border-gray-200 flex items-center px-4 w-full rounded-full text-xs text-[#191D38] font-medium outline-none disabled:bg-gray-50 disabled:text-[#191D38]/60"
                >
              </div>
            </div>
          </div>

          {{-- WERKUREN --}}
          <div x-show="tab==='werkuren'" x-cloak class="grid gap-4">
            <div class="p-4 bg-white border border-gray-200 rounded-2xl">
              <p class="text-[#191D38] font-black text-sm">Werkuren</p>
              <p class="text-[#191D38]/60 text-xs font-semibold mt-1">
                Vul start- en eindtijd in (of laat leeg als er geen werktijd is).
              </p>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
              <div class="px-6 py-4 bg-[#191D38]/10">
                <div class="grid grid-cols-[1fr_0.6fr_0.6fr] gap-4 text-xs font-bold text-[#191D38]/50">
                  <div>Dag</div>
                  <div>Start</div>
                  <div>Eind</div>
                </div>
              </div>

              <div class="px-6 py-2 divide-y divide-[#191D38]/10">
                @foreach($days as $dayKey => $dayLabel)
                  @php
                    $startVal = old("work_hours.$dayKey.start", data_get($wh, "$dayKey.start"));
                    $endVal   = old("work_hours.$dayKey.end", data_get($wh, "$dayKey.end"));
                  @endphp

                  <div class="py-3 grid grid-cols-[1fr_0.6fr_0.6fr] gap-4 items-center">
                    <div class="text-sm font-semibold text-[#191D38]">{{ $dayLabel }}</div>

                    <div>
                      <input
                        type="time"
                        name="work_hours[{{ $dayKey }}][start]"
                        value="{{ $startVal }}"
                        @disabled(!$isAdmin)
                        class="h-9 bg-white border border-gray-200 flex items-center px-4 w-full rounded-full text-xs text-[#191D38] font-medium outline-none disabled:bg-gray-50 disabled:text-[#191D38]/60"
                      >
                    </div>

                    <div>
                      <input
                        type="time"
                        name="work_hours[{{ $dayKey }}][end]"
                        value="{{ $endVal }}"
                        @disabled(!$isAdmin)
                        class="h-9 bg-white border border-gray-200 flex items-center px-4 w-full rounded-full text-xs text-[#191D38] font-medium outline-none disabled:bg-gray-50 disabled:text-[#191D38]/60"
                      >
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          </div>

        </form>

        {{-- Validatie errors --}}
        @if($errors->any())
          <div class="mt-4 py-3 px-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs font-semibold">
            {{ $errors->first() }}
          </div>
        @endif

        {{-- PLANNING (read-only) --}}
        <div x-show="tab==='planning'" x-cloak class="grid gap-4">
          <div class="p-4 bg-white border border-gray-200 rounded-2xl">
            <p class="text-[#191D38] font-black text-sm">Planning</p>
            <p class="text-[#191D38]/60 text-xs font-semibold mt-1">
              Overzicht van (recente/toekomstige) planningitems gekoppeld aan deze gebruiker.
            </p>
          </div>

          <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-[#191D38]/10">
              <div class="grid grid-cols-[0.8fr_0.8fr_1.2fr_0.6fr] gap-4 text-xs font-bold text-[#191D38]/50">
                <div>Datum/tijd</div>
                <div>Locatie</div>
                <div>Notities</div>
                <div class="text-right">Actie</div>
              </div>
            </div>

            <div class="px-6 py-2 divide-y divide-[#191D38]/10">
              @forelse($planningItems as $pi)
                @php
                  $start = $pi->start_at ?? $pi->start ?? $pi->starts_at ?? null;
                  $end   = $pi->end_at ?? $pi->end ?? $pi->ends_at ?? null;

                  $startTxt = $start ? \Carbon\Carbon::parse($start)->format('d-m-Y H:i') : '—';
                  $endTxt   = $end ? \Carbon\Carbon::parse($end)->format('H:i') : null;

                  $loc   = $pi->location ?? $pi->adres ?? $pi->address ?? '—';
                  $notes = $pi->notes ?? $pi->description ?? $pi->omschrijving ?? '—';

                  $editUrl = null;
                  // planning-management edit route bestaat: support.planning.edit
                  if (!empty($pi->id)) {
                    $editUrl = route('support.planning.edit', $pi->id);
                  }
                @endphp

                <div class="py-3 grid grid-cols-[0.8fr_0.8fr_1.2fr_0.6fr] gap-4 items-center">
                  <div class="text-sm font-semibold text-[#191D38]">
                    {{ $startTxt }}@if($endTxt) – {{ $endTxt }}@endif
                  </div>
                  <div class="text-sm text-[#191D38]">{{ $loc }}</div>
                  <div class="text-sm text-[#191D38] truncate">{{ $notes }}</div>

                  <div class="text-right">
                    @if($editUrl)
                      <a href="{{ $editUrl }}" class="text-xs font-semibold text-[#009AC3] hover:underline">Open</a>
                    @else
                      <span class="text-xs font-semibold text-[#191D38]/40">—</span>
                    @endif
                  </div>
                </div>
              @empty
                <div class="py-8 text-center text-sm font-semibold text-[#191D38]/50">
                  Geen planningitems gevonden.
                </div>
              @endforelse
            </div>
          </div>
        </div>

        {{-- PROJECTEN (read-only) --}}
        <div x-show="tab==='projecten'" x-cloak class="grid gap-4">
          <div class="p-4 bg-white border border-gray-200 rounded-2xl">
            <p class="text-[#191D38] font-black text-sm">Projecten</p>
            <p class="text-[#191D38]/60 text-xs font-semibold mt-1">
              Projecten die aan deze gebruiker gekoppeld zijn.
            </p>
          </div>

          <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
            <div class="px-6 py-4 bg-[#191D38]/10">
              <div class="grid grid-cols-[1.2fr_0.8fr_0.6fr] gap-4 text-xs font-bold text-[#191D38]/50">
                <div>Project</div>
                <div>Status</div>
                <div class="text-right">Actie</div>
              </div>
            </div>

            <div class="px-6 py-2 divide-y divide-[#191D38]/10">
              @forelse($projects as $p)
                @php
                  $title = $p->name ?? $p->title ?? $p->project_name ?? ('Project #'.$p->id);
                  $status = $p->status ?? $p->fase ?? '—';
                  $showUrl = route('support.projecten.show', $p->id);
                @endphp

                <div class="py-3 grid grid-cols-[1.2fr_0.8fr_0.6fr] gap-4 items-center">
                  <div class="text-sm font-semibold text-[#191D38]">{{ $title }}</div>
                  <div class="text-sm text-[#191D38]">{{ $status }}</div>
                  <div class="text-right">
                    <a href="{{ $showUrl }}" class="text-xs font-semibold text-[#009AC3] hover:underline">Open</a>
                  </div>
                </div>
              @empty
                <div class="py-8 text-center text-sm font-semibold text-[#191D38]/50">
                  Geen projecten gekoppeld.
                </div>
              @endforelse
            </div>
          </div>
        </div>

        {{-- ACTIONS (geen nested forms) --}}
        @if($isAdmin)
          <div class="pt-6 flex items-center justify-between gap-3">

            <form
              method="POST"
              action="{{ route('support.gebruikers.destroy', $u) }}"
              onsubmit="return confirm('Weet je zeker dat je {{ addslashes((string)($u->name ?? 'deze gebruiker')) }} wilt verwijderen?');"
            >
              @csrf
              @method('DELETE')

              <button
                type="submit"
                class="h-9 inline-flex text-xs items-center justify-center bg-red-600 hover:bg-red-500 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer"
              >
                Verwijderen
              </button>
            </form>

            <button
              type="submit"
              form="user-update-form"
              class="h-9 inline-flex text-xs items-center justify-center bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer"
            >
              Opslaan
            </button>
          </div>
        @else
          <div class="pt-6">
            <div class="rounded-2xl bg-white border border-gray-200 px-4 py-3 text-xs font-semibold text-[#191D38]/60">
              Alleen admins kunnen gebruikers wijzigen.
            </div>
          </div>
        @endif

      </div>
    </div>

  </div>
</div>
@endsection
