@extends('hub.layouts.app')

@section('content')
<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">
    <div class="flex-1 w-full overflow-hidden flex flex-col min-h-0">

      {{-- Top bar --}}
      <div class="mb-4 flex items-center justify-between gap-4">
        <form method="GET" action="{{ route('support.onboarding.index') }}" class="flex items-center gap-3">
          <input
            type="text"
            name="q"
            value="{{ $q ?? '' }}"
            placeholder="Zoeken op persoon..."
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
      </div>

      {{-- Header row --}}
      <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-tl-2xl rounded-tr-2xl">
        <div class="grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
          <p class="text-[#191D38] font-bold text-xs opacity-50">ID</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Onboarding door</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Onboarding op</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
          <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
        </div>
      </div>

      {{-- Body --}}
      <div class="flex-1 min-h-0 bg-[#191D38]/5 overflow-y-auto rounded-bl-2xl rounded-br-2xl">
        <div class="px-6 py-2 divide-y divide-[#191D38]/10">
          @php
            $statusMap = [
              'concept'   => ['label' => 'Concept',    'class' => 'text-[#2A324B] bg-[#2A324B]/20'],
              'completed' => ['label' => 'Voltooid',   'class' => 'text-[#87A878] bg-[#87A878]/20'],
              'cancelled' => ['label' => 'Geannuleerd','class' => 'text-[#DF2935] bg-[#DF2935]/20'],
              'archived'  => ['label' => 'Gearchiveerd','class'=> 'text-[#DF9A57] bg-[#DF9A57]/20'],
            ];
          @endphp
          @if(!empty($hasDraft))
            @php
              $draftDate = now()->format('d-m-Y'); // geen started_at? dan vandaag
              // Optioneel: als je ooit started_at toevoegt in session:
              // $draftDate = !empty($wizard['started_at'] ?? null) ? \Carbon\Carbon::parse($wizard['started_at'])->format('d-m-Y') : now()->format('d-m-Y');
              $pill = $statusMap['concept'];
            @endphp
            <div class="py-3 pt-0 grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
              <div class="text-[#191D38] font-semibold text-sm">—</div>
              <div class="text-[#191D38] text-sm">{{ $user->name ?? 'Onbekend' }}</div>
              <div class="text-[#191D38] text-sm">{{ $draftDate }}</div>
              <div class="{{ $pill['class'] }} text-xs font-semibold rounded-full py-1.5 text-center">
                {{ $pill['label'] }}
              </div>
              <div class="justify-end text-[#191D38] flex items-center gap-2">
                <a href="{{ route('support.onboarding.step1') }}" class="cursor-pointer">
                  <i class="fa-solid fa-pen-to-square hover:text-[#009AC3] transition duration-200"></i>
                </a>
              </div>
            </div>
          @endif
          @forelse($rows as $r)
            @php
              // Voor nu: alles wat in DB staat = “Voltooid”
              // Later kan je $r->status = cancelled/archived gebruiken
              $key = 'completed';
              if ($r->status === 'cancelled') $key = 'cancelled';
              if ($r->status === 'archived')  $key = 'archived';

              $pill = $statusMap[$key];
            @endphp
            <div class="py-3 grid grid-cols-[0.2fr_1fr_1fr_1fr_0.8fr] items-center gap-6">
              <div class="text-[#191D38] font-semibold text-sm">{{ $r->id }}</div>
              <div class="text-[#191D38] text-sm">
                {{ $r->user->name ?? 'Onbekend' }}
              </div>
              <div class="text-[#191D38] text-sm">
                {{ optional($r->created_at)->format('d-m-Y') }}
              </div>
              <div class="{{ $pill['class'] }} text-xs font-semibold rounded-full py-1.5 text-center">
                {{ $pill['label'] }}
              </div>
              <div class="justify-end text-[#191D38] flex items-center gap-2">
                <a href="{{ route('support.onboarding.show', $r) }}" class="cursor-pointer">
                  <i class="fa-solid fa-eye hover:text-[#009AC3] transition duration-200"></i>
                </a>
              </div>
            </div>
          @empty
            @if(empty($hasDraft))
              <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
                Nog geen onboarding aanvragen gevonden.
              </div>
            @endif
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
