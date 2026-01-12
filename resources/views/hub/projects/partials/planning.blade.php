{{-- resources/views/hub/projects/partials/planning.blade.php --}}

@php
  // UI helper classes (zelfde als show)
  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";

  // Kolommen zoals "tabel"
  $planningCols = "grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_minmax(0,0.7fr)_minmax(0,0.55fr)_90px]";
@endphp

<div id="project-planning" class="col-span-2">
  <div class="{{ $sectionWrap }}">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Planning</p>
        <p class="ml-4 text-[#191D38] font-bold text-xs opacity-50">
          Totaal: <span>{{ (int) ($project->planningItems?->count() ?? 0) }}</span>
        </p>
      </div>

      {{-- optioneel: link naar planning management --}}
      <a
        href="{{ route('support.planning.index') }}"
        class="h-8 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#191D38] text-white text-xs font-semibold hover:bg-[#191D38]/80 transition duration-200"
      >
        Planning beheren
      </a>
    </div>

    {{-- Header row --}}
    <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
      <div class="grid {{ $planningCols }} items-center gap-6">
        <p class="text-[#191D38] font-bold text-xs opacity-50">Item</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Locatie</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Datum & tijd</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Verantwoordelijke</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-right pr-1">Acties</p>
      </div>
    </div>

    {{-- Body --}}
    <div class="{{ $sectionBody }} rounded-b-2xl">
      <div class="px-6 py-2 divide-y divide-[#191D38]/10">
        @forelse($project->planningItems as $p)
          @php
            $start = $p->start_at ? optional($p->start_at)->format('d-m-Y H:i') : null;
            $end   = $p->end_at ? optional($p->end_at)->format('H:i') : null;
          @endphp

          <div class="py-3 grid {{ $planningCols }} items-center gap-6">
            {{-- Item --}}
            <div class="min-w-0">
              <p class="text-[#191D38] font-semibold text-sm truncate">
                {{ $p->notes ?? 'Planning item' }}
              </p>
            </div>

            {{-- Locatie --}}
            <div class="min-w-0">
              <p class="text-[#191D38] text-sm truncate">
                {{ $p->location ?? '—' }}
              </p>
            </div>

            {{-- Datum/tijd --}}
            <div class="text-[#009AC3] text-sm">
              @if($start)
                {{ $start }}@if($end) → {{ $end }}@endif
              @else
                —
              @endif
            </div>

            {{-- Verantwoordelijke --}}
            <div class="min-w-0">
              <p class="text-[#191D38] text-sm font-semibold truncate">
                {{ $p->assignee?->name ?? '—' }}
              </p>
            </div>

            {{-- Acties --}}
            <div class="flex items-center justify-end gap-2">
              <a
                href="{{ route('support.planning.edit', ['planningItem' => $p]) }}"
                class="cursor-pointer"
                title="Bewerk planning"
              >
                <i class="fa-solid fa-pencil hover:text-[#009AC3] transition duration-200"></i>
              </a>

              <form
                method="POST"
                action="{{ route('support.planning.destroy', ['planningItem' => $p]) }}"
                onsubmit="return confirm('Weet je zeker dat je deze planning wilt verwijderen?');"
              >
                @csrf
                @method('DELETE')

                <button class="cursor-pointer" type="submit" title="Verwijder planning">
                  <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                </button>
              </form>
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
