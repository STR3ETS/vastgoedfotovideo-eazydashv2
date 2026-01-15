{{-- resources/views/hub/projects/partials/planning.blade.php --}}

@php
  $sectionWrap   = $sectionWrap   ?? "overflow-hidden rounded-2xl";
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";

  $planningCols = "grid-cols-[minmax(0,0.95fr)_minmax(0,0.80fr)_minmax(0,0.80fr)_minmax(0,0.55fr)_220px]";

  $errorsBag = $planningErrors ?? null;
  $planningAssignees = $planningAssignees ?? collect();
@endphp

<div
  id="project-planning"
  class="col-span-2"
  x-data='{
    addOpen:false,
    newNotes:"",
    newLocation:"",

    // ✅ UX: datum + tijd van/tot (zelfde dag)
    newDate:"",
    newStartTime:"",
    newEndTime:"",
    // Hidden waarden voor backend (worden opgebouwd)
    newStart:"",
    newEnd:"",

    newAssignee:"",

    openAdd(){
      this.addOpen = true;
      this.$nextTick(() => this.$refs.notesInput?.focus());
    },

    pad(n){ return String(n).padStart(2,"0"); },

    displayDate(){
      if(!this.newDate) return "";
      const parts = String(this.newDate).split("-");
      if(parts.length !== 3) return "";
      return `${parts[2]}-${parts[1]}-${parts[0]}`; // dd-mm-yyyy
    },

    displayRange(){
      if(!this.newDate || !this.newStartTime || !this.newEndTime) return "";
      return `${this.displayDate()} ${this.newStartTime} - ${this.newEndTime}`;
    },

    buildDatetimes(){
      if(!this.newDate || !this.newStartTime || !this.newEndTime){
        this.newStart = "";
        this.newEnd = "";
        return;
      }

      // voorkom eindtijd vóór starttijd (zelfde dag)
      if(this.newEndTime < this.newStartTime){
        this.newEndTime = this.newStartTime;
      }

      this.newStart = `${this.newDate}T${this.newStartTime}`;
      this.newEnd   = `${this.newDate}T${this.newEndTime}`;
    },

    resetDateTime(){
      this.newDate = "";
      this.newStartTime = "";
      this.newEndTime = "";
      this.buildDatetimes();
    },

    closeAdd(){
      this.addOpen = false;
      this.newNotes = "";
      this.newLocation = "";

      this.resetDateTime();

      this.newAssignee = "";
    }
  }'
>
  <div class="{{ $sectionWrap }} overflow-visible">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Planning</p>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          x-on:click="openAdd()"
          class="h-8 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition duration-200"
        >
          Nieuwe planning aanmaken
        </button>

        <a
          href="{{ route('support.planning.index') }}"
          class="h-8 cursor-pointer px-4 inline-flex items-center gap-2 rounded-full bg-[#191D38] text-white text-xs font-semibold hover:bg-[#191D38]/80 transition duration-200"
        >
          Planning beheren
        </a>
      </div>
    </div>

    {{-- Errors --}}
    @if($errorsBag && method_exists($errorsBag, 'any') && $errorsBag->any())
      <div class="px-6 pt-4">
        <div class="rounded-2xl bg-[#DF2935]/10 text-[#DF2935] text-xs font-semibold px-4 py-3">
          {{ $errorsBag->first() }}
        </div>
      </div>
    @endif

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

          <div id="planning-item-{{ $p->id }}" class="py-3 grid {{ $planningCols }} items-center gap-6">
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
            <div class="text-[#009AC3] text-sm whitespace-nowrap">
              @if($start)
                {{ $start }}@if($end) - {{ $end }}@endif
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
                action="{{ route('support.projecten.planning.destroy', ['project' => $project, 'planningItem' => $p]) }}"

                hx-delete="{{ route('support.projecten.planning.destroy', ['project' => $project, 'planningItem' => $p]) }}"
                hx-target="#project-planning"
                hx-swap="outerHTML"
                hx-confirm="Weet je zeker dat je deze planning wilt verwijderen?"
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

        {{-- ✅ Nieuwe planning rij (onderaan) --}}
        <form
          x-cloak
          x-show="addOpen"
          class="py-3 grid {{ $planningCols }} items-center gap-6"
          method="POST"
          action="{{ route('support.projecten.planning.store', ['project' => $project]) }}"

          hx-post="{{ route('support.projecten.planning.store', ['project' => $project]) }}"
          hx-target="#project-planning"
          hx-swap="outerHTML"
        >
          @csrf

          {{-- Item --}}
          <div class="min-w-0">
            <input
              x-ref="notesInput"
              type="text"
              name="notes"
              x-model="newNotes"
              placeholder="Bijv. Fotoshoot, intake, opname..."
              class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              x-on:keydown.escape.prevent="closeAdd()"
              required
            >
          </div>

          {{-- Locatie --}}
          <div class="min-w-0">
            <input
              type="text"
              name="location"
              x-model="newLocation"
              placeholder="Locatie"
              class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              x-on:keydown.escape.prevent="closeAdd()"
            >
          </div>

          {{-- ✅ Datum & tijd (popover zoals Taken) --}}
          <div class="min-w-0">
            <div
              class="relative"
              x-data="{ open:false }"
              x-on:click.outside="open=false"
              x-on:keydown.escape.window="open=false"
            >
              {{-- Trigger --}}
              <button
                type="button"
                x-on:click="open = !open; if(open){ $nextTick(() => $refs.dtDate?.focus()); }"
                class="w-full inline-flex items-center justify-between gap-2 text-sm hover:text-[#009AC3] transition"
                title="Kies datum en tijd"
              >
                <template x-if="newStart && newEnd">
                  <span class="text-[#191D38] text-sm truncate" x-text="displayRange()"></span>
                </template>

                <template x-if="!newStart || !newEnd">
                  <span class="inline-flex items-center gap-1.5 px-2.5 w-full h-9 border border-dashed border-[#191D38] rounded-full transition duration-200 opacity-20 hover:opacity-50 cursor-pointer">
                    <i class="fa-solid fa-clock text-[#191D38]"></i>
                    <span class="text-[#191D38] text-xs font-semibold">Kies datum & tijd</span>
                  </span>
                </template>
              </button>

              {{-- Popover --}}
              <div
                x-cloak
                x-show="open"
                x-transition.origin.top
                class="absolute z-50 top-full mt-2 left-1/2 -translate-x-1/2 w-[340px]"
              >
                <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-4 shadow-lg">
                  <button
                    type="button"
                    x-on:click="resetDateTime(); open=false;"
                    class="w-full cursor-pointer h-9 px-4 inline-flex items-center justify-center rounded-full bg-[#DF2935]/20 text-[#DF2935] text-xs font-semibold hover:opacity-90 transition mb-6"
                  >
                    Datum & tijd resetten
                  </button>
                  <div class="grid grid-cols-12 gap-2">
                    {{-- Datum --}}
                    <div class="col-span-12">
                      <input
                        x-ref="dtDate"
                        type="date"
                        x-model="newDate"
                        x-on:input="buildDatetimes()"
                        class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                        required
                      >
                    </div>

                    {{-- Van --}}
                    <div class="col-span-6">
                      <input
                        type="time"
                        x-model="newStartTime"
                        x-on:input="buildDatetimes()"
                        class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                        required
                      >
                    </div>

                    {{-- Tot --}}
                    <div class="col-span-6">
                      <input
                        type="time"
                        x-model="newEndTime"
                        x-on:input="buildDatetimes()"
                        class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                        required
                      >
                    </div>
                  </div>

                  <div class="mt-6 flex items-center justify-between gap-2">
                    <button
                      type="button"
                      x-on:click="open=false"
                      class="w-full cursor-pointer h-9 px-4 inline-flex items-center justify-center rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition"
                      x-bind:disabled="!newStart || !newEnd"
                    >
                      Klaar
                    </button>
                  </div>
                </div>
              </div>

              {{-- Hidden fields voor backend --}}
              <input type="hidden" name="start_at" x-model="newStart">
              <input type="hidden" name="end_at" x-model="newEnd">
            </div>
          </div>

          {{-- Verantwoordelijke --}}
          <div class="min-w-0">
            <select
              name="assignee_user_id"
              x-model="newAssignee"
              class="h-9 w-full min-w-[170px] rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              x-on:keydown.escape.prevent="closeAdd()"
            >
              <option value="">Niet toegewezen</option>
              @foreach($planningAssignees as $u)
                <option value="{{ $u->id }}">{{ $u->name }}</option>
              @endforeach
            </select>
          </div>

          {{-- Acties --}}
          <div class="flex items-center justify-end gap-2 whitespace-nowrap">
            <button
              type="button"
              x-on:click="closeAdd()"
              class="h-9 px-4 inline-flex items-center rounded-full bg-[#2A324B]/20 text-[#2A324B]/60 text-xs font-semibold hover:bg-[#2A324B]/10 transition"
            >
              Annuleren
            </button>

            <button
              type="submit"
              class="h-9 px-4 inline-flex items-center rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition"
              x-bind:disabled="!newNotes || !newNotes.trim() || !newStart || !newEnd"
            >
              Opslaan
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>