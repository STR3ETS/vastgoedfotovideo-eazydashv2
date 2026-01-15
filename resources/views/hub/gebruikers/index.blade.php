{{-- resources/views/hub/gebruikers/index.blade.php --}}
@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

    @php
      $crumbLabel = 'Overzicht';
      $navNewBtn  = "h-9 inline-flex text-xs items-center justify-center bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer";

      $q    = $q    ?? (string) request()->query('q', '');
      $sort = $sort ?? (string) request()->query('sort', 'newest');

      // ✅ rol is optioneel (zonder ?rol= => alles)
      $rolRaw = isset($rol) ? (string) $rol : (string) request()->query('rol', '');
      $rolRaw = trim($rolRaw);
      $rol    = $rolRaw !== '' ? $rolRaw : null;

      // paginator of collection
      $rows = $rows ?? ($users ?? collect());

      $isAdmin = (auth()->user()->rol ?? null) === 'admin';

      $roleMap = [
        'admin'          => ['label' => 'Admin',         'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
        'klant'          => ['label' => 'Klant',         'class' => 'text-[#009AC3] bg-[#009AC3]/20'],
        'team-manager'   => ['label' => 'Team manager',  'class' => 'text-[#87A878] bg-[#87A878]/20'],
        'client-manager' => ['label' => 'Klant manager', 'class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
        'fotograaf'      => ['label' => 'Fotograaf',     'class' => 'text-[#2A324B] bg-[#2A324B]/20'],
      ];

      // helper: detail url met behoud van filters
      $detailQsBase = array_filter([
        'rol'  => $rol,
        'q'    => $q ?: null,
        'sort' => $sort ?: null,
      ]);
    @endphp

    {{-- Breadcrumbs + filters --}}
    <div class="shrink-0 mb-4 flex items-center justify-between">
      <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-[#191D38]/50">
        <a href="{{ route('support.dashboard') }}" class="hover:text-[#191D38] transition">Dashboard</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.gebruikers.index') }}" class="hover:text-[#191D38] transition">Gebruikers</a>
        <span class="opacity-40">/</span>
        <span class="text-[#009AC3]">{{ $crumbLabel }}</span>
      </nav>

      <div class="flex items-center gap-3">
        <form method="GET" action="{{ route('support.gebruikers.index') }}" class="flex items-center gap-3">
          {{-- ✅ rol behouden als je gefilterd bent --}}
          @if($rol)
            <input type="hidden" name="rol" value="{{ $rol }}">
          @endif

          <input
            type="text"
            name="q"
            value="{{ $q }}"
            placeholder="Zoeken op naam of e-mail..."
            class="h-9 bg-white border border-gray-200 flex items-center px-4 w-[300px] rounded-full text-xs text-[#191D38] font-medium outline-none"
          >

          <div class="relative">
            <select
              name="sort"
              class="h-9 bg-white border border-gray-200 pl-4 pr-10 rounded-full text-xs text-[#191D38] font-medium outline-none appearance-none cursor-pointer"
              onchange="this.form.submit()"
            >
              <option value="newest"    {{ $sort === 'newest' ? 'selected' : '' }}>Nieuwste eerst</option>
              <option value="oldest"    {{ $sort === 'oldest' ? 'selected' : '' }}>Oudste eerst</option>
              <option value="name_asc"  {{ $sort === 'name_asc' ? 'selected' : '' }}>Naam A–Z</option>
              <option value="name_desc" {{ $sort === 'name_desc' ? 'selected' : '' }}>Naam Z–A</option>
              <option value="email"     {{ $sort === 'email' ? 'selected' : '' }}>E-mail</option>
            </select>

            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[#191D38]/60">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd"/>
              </svg>
            </span>
          </div>
        </form>

        @if($isAdmin)
          <a
            href="{{ route('support.gebruikers.index', array_filter(['rol' => $rol, 'q' => $q ?: null, 'sort' => $sort ?: null, 'create' => 1])) }}"
            class="{{ $navNewBtn }}"
          >
            Gebruiker aanmaken
          </a>
        @endif
      </div>
    </div>

    <div class="flex-1 w-full overflow-hidden flex flex-col min-h-0">

      {{-- Header row --}}
      <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-tl-2xl rounded-tr-2xl">
        <div class="grid grid-cols-[1.1fr_1.2fr_0.9fr_0.75fr_0.45fr] items-center gap-6">
          <p class="text-[#191D38] font-bold text-xs opacity-50">Naam</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">E-mail</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Telefoonnummer</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Rol</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
        </div>
      </div>

      {{-- Body --}}
      <div class="flex-1 min-h-0 bg-[#191D38]/5 overflow-y-auto rounded-bl-2xl rounded-br-2xl">
        <div class="px-6 py-2 divide-y divide-[#191D38]/10">

          @forelse($rows as $u)
            @php
              $roleKey = strtolower((string) ($u->rol ?? 'klant'));
              $pill = $roleMap[$roleKey] ?? ['label' => ucfirst($roleKey), 'class' => 'text-[#191D38] bg-[#191D38]/20'];

              $detailUrl = route('support.gebruikers.show', $u);
              if (!empty($detailQsBase)) {
                $detailUrl .= '?' . http_build_query($detailQsBase);
              }
            @endphp

            <div class="py-3 grid grid-cols-[1.1fr_1.2fr_0.9fr_0.75fr_0.45fr] items-center gap-6">
              <div class="text-[#191D38] text-sm font-semibold">
                {{ $u->name ?? ('Gebruiker #'.$u->id) }}
              </div>

              <div class="text-[#191D38] text-sm">
                {{ $u->email ?? '—' }}
              </div>

              <div class="text-[#191D38] text-sm">
                {{ $u->phone ?? '—' }}
              </div>

              <div class="{{ $pill['class'] }} text-xs font-semibold rounded-full py-1.5 text-center">
                {{ $pill['label'] }}
              </div>

              <div class="justify-end text-[#191D38] flex items-center gap-3">
                <a
                  href="{{ $detailUrl }}"
                  class="cursor-pointer"
                  title="Bewerken / bekijken"
                >
                  <i class="fa-solid fa-eye hover:text-[#009AC3] transition duration-200"></i>
                </a>

                @if($isAdmin)
                  <form
                    method="POST"
                    action="{{ route('support.gebruikers.destroy', $u) }}"
                    onsubmit="return confirm('Weet je zeker dat je {{ addslashes((string)($u->name ?? 'deze gebruiker')) }} wilt verwijderen?');"
                  >
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="cursor-pointer" title="Verwijderen">
                      <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                    </button>
                  </form>
                @endif
              </div>
            </div>
          @empty
            <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
              Nog geen gebruikers gevonden.
            </div>
          @endforelse

          @if(!empty($rows) && method_exists($rows, 'links'))
            <div class="pt-6">
              {{ $rows->links() }}
            </div>
          @endif

        </div>
      </div>

    </div>
  </div>
</div>
@endsection
