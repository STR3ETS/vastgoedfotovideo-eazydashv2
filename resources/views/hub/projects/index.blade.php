{{-- resources/views/hub/projecten/index.blade.php --}}
@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

    @php
      // Breadcrumbs labels
      $crumbLabel = 'Overzicht';
      $navNewBtn  = "h-9 inline-flex text-xs items-center justify-center bg-[#009AC3] hover:bg-[#009AC3]/70 transition duration-200 px-6 text-white rounded-full font-semibold cursor-pointer";
    @endphp

    {{-- Breadcrumbs (altijd zichtbaar) --}}
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
        <span class="text-[#009AC3]">
          {{ $crumbLabel }}
        </span>
      </nav>

      <div class="flex items-center gap-3">
        <form method="GET" action="{{ route('support.projecten.index') }}" class="flex items-center gap-3">
          <input
            type="text"
            name="q"
            value="{{ $q ?? '' }}"
            placeholder="Zoeken op project..."
            class="h-9 bg-white border border-gray-200 flex items-center px-4 w-[300px] rounded-full text-xs text-[#191D38] font-medium outline-none"
          >

          <div class="relative">
            <select
              name="sort"
              class="h-9 bg-white border border-gray-200 pl-4 pr-10 rounded-full text-xs text-[#191D38] font-medium outline-none appearance-none cursor-pointer"
              onchange="this.form.submit()"
            >
              <option value="newest"     {{ ($sort ?? 'newest') === 'newest' ? 'selected' : '' }}>Nieuwste eerst</option>
              <option value="oldest"     {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oudste eerst</option>
              <option value="title_asc"  {{ ($sort ?? '') === 'title_asc' ? 'selected' : '' }}>Titel A–Z</option>
              <option value="title_desc" {{ ($sort ?? '') === 'title_desc' ? 'selected' : '' }}>Titel Z–A</option>
              <option value="status"     {{ ($sort ?? '') === 'status' ? 'selected' : '' }}>Status</option>
            </select>

            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[#191D38]/60">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.24 4.5a.75.75 0 0 1-1.08 0l-4.24-4.5a.75.75 0 0 1 .02-1.06z" clip-rule="evenodd"/>
              </svg>
            </span>
          </div>
        </form>

        {{-- Projecten worden bij jou via onboarding aangemaakt --}}
        <a href="{{ route('support.projecten.create') }}" class="{{ $navNewBtn }}">
          Project aanmaken
        </a>
      </div>
    </div>

    <div class="flex-1 w-full overflow-hidden flex flex-col min-h-0">

      {{-- Header row --}}
      <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-tl-2xl rounded-tr-2xl">
        <div class="grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
          <p class="text-[#191D38] font-bold text-xs opacity-50">ID</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Project</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Klant</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
        </div>
      </div>

      {{-- Body --}}
      <div class="flex-1 min-h-0 bg-[#191D38]/5 overflow-y-auto rounded-bl-2xl rounded-br-2xl">
        <div class="px-6 py-2 divide-y divide-[#191D38]/10">
          @php
            $statusMap = [
              'active'   => ['label' => 'Actief',     'class' => 'text-[#87A878] bg-[#87A878]/20'],
              'pending'  => ['label' => 'In afwachting','class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
              'done'     => ['label' => 'Afgerond',   'class' => 'text-[#2A324B] bg-[#2A324B]/20'],
              'archived' => ['label' => 'Gearchiveerd','class'=> 'text-[#191D38] bg-[#191D38]/20'],
            ];
          @endphp

          @forelse($rows as $r)
            @php
              $key  = $r->status ?? 'active';
              $pill = $statusMap[$key] ?? ['label' => ucfirst((string) $key), 'class' => 'text-[#2A324B] bg-[#2A324B]/20'];
            @endphp

            <div class="py-3 grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
              <div class="text-[#191D38] font-semibold text-sm">{{ $r->id }}</div>

              <div class="text-[#191D38] text-sm">
                {{ $r->title ?? ('Project #'.$r->id) }}
              </div>

              <div class="text-[#191D38] text-sm">
                {{ $r->client->name ?? $r->client->email ?? 'Onbekend' }}
              </div>

              <div class="{{ $pill['class'] }} text-xs font-semibold rounded-full py-1.5 text-center">
                {{ $pill['label'] }}
              </div>

              <div class="justify-end text-[#191D38] flex items-center gap-2">
                <a href="{{ route('support.projecten.show', $r) }}" class="cursor-pointer">
                  <i class="fa-solid fa-eye hover:text-[#009AC3] transition duration-200"></i>
                </a>
              </div>
            </div>
          @empty
            <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
              Nog geen projecten gevonden.
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
