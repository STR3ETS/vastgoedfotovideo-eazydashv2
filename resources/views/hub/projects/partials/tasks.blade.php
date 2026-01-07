{{-- resources/views/hub/projecten/partials/tasks.blade.php --}}

@php
  // UI helper classes (zelfde als show)
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";

  $assignees = $assignees ?? collect();
  $assigneeOptions = $assignees->map(fn($u) => [
    'id' => $u->id,
    'name' => $u->name,
    'rol' => $u->rol ?? null,
  ])->values();
@endphp

<div
  id="project-tasks"
  class="col-span-2"
  x-data="{
    selected: [],
    bulkStatus: 'active',

    toggleAll(ev){
      const on = ev.target.checked;
      const ids = Array.from(this.$root.querySelectorAll('input[data-task-checkbox]'))
        .filter(cb => !cb.disabled)
        .map(cb => cb.value);

      this.selected = on ? ids : [];
    },

    isAllSelected(){
      const ids = Array.from(this.$root.querySelectorAll('input[data-task-checkbox]'))
        .filter(cb => !cb.disabled)
        .map(cb => cb.value);

      return ids.length > 0 && ids.every(id => this.selected.includes(id));
    },

    clearSelection(){
      this.selected = [];
      const master = this.$root.querySelector('input[data-master-checkbox]');
      if(master) master.checked = false;
    }
  }"
>
  {{-- BELANGRIJK: overflow-visible zodat popovers niet worden afgeknipt --}}
  <div class="rounded-2xl overflow-visible">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Taken</p>
        <div
        x-cloak
        x-show="selected.length > 0"
        class="flex items-center gap-2 ml-6"
        >
            <span class="text-[#191D38] font-bold text-xs opacity-50">
                Geselecteerd: <span x-text="selected.length"></span>
            </span>
            <form
                method="POST"
                action="{{ route('support.projecten.taken.bulk_status', ['project' => $project]) }}"
                class="flex items-center gap-2"

                hx-patch="{{ route('support.projecten.taken.bulk_status', ['project' => $project]) }}"
                hx-target="#project-tasks"
                hx-swap="outerHTML"
                x-on:htmx:afterRequest="clearSelection()"
            >
                @csrf
                @method('PATCH')
                {{-- hidden inputs voor ids --}}
                <template x-for="id in selected" :key="'sel-'+id">
                    <input type="hidden" name="task_ids[]" :value="id">
                </template>
                <select
                    name="status"
                    x-model="bulkStatus"
                    class="h-9 rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none"
                >
                    <option value="pending">Te voldoen</option>
                    <option value="active">Bezig</option>
                    <option value="done">Voldaan</option>
                    <option value="cancelled">Gesloten</option>
                    <option value="archived">Gearchiveerd</option>
                </select>
                <button
                type="submit"
                class="h-9 inline-flex items-center justify-center rounded-full px-5 text-xs font-semibold
                        bg-[#009AC3] text-white hover:opacity-90 transition"
                >
                    Update status
                </button>
            </form>
        </div>
    </div>

    <p class="text-[#191D38]/60 text-xs font-semibold">
        Totaal: <span class="text-[#191D38] font-black">{{ (int) ($project->tasks?->count() ?? 0) }}</span>
    </p>
    </div>

    {{-- Header row --}}
    <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
        <div class="grid grid-cols-[0.18fr_1.12fr_0.45fr_0.65fr_0.45fr_0.45fr_0.45fr] items-center gap-6">
            <div class="flex items-center">
                <input
                    type="checkbox"
                    data-master-checkbox
                    class="h-4 w-4 rounded border-[#191D38]/20"
                    x-on:change="toggleAll($event)"
                    :checked="isAllSelected()"
                >
            </div>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Taak</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Verantwoordelijke</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Locatie</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50">Datum te voldoen</p>
            <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Datum voldaan</p>
        </div>
    </div>

    {{-- Body --}}
    <div class="{{ $sectionBody }} rounded-b-2xl">
      <div class="px-6 py-2 divide-y divide-[#191D38]/10">
        @forelse($project->tasks as $t)
          @php
            $taskStatusMap = [
              'pending'   => ['label' => 'Te voldoen',   'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
              'active'    => ['label' => 'Bezig',       'class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
              'done'      => ['label' => 'Voldaan',     'class' => 'text-[#87A878] bg-[#87A878]/20'],
              'cancelled' => ['label' => 'Gesloten',    'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
              'archived'  => ['label' => 'Gearchiveerd','class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
            ];

            $taskKey  = strtolower((string) ($t->status ?? 'pending'));
            $taskPill = $taskStatusMap[$taskKey] ?? [
              'label' => ucfirst($taskKey),
              'class' => 'text-[#2A324B] bg-[#2A324B]/20',
            ];
          @endphp

          <div class="py-3 grid grid-cols-[0.18fr_1.12fr_0.45fr_0.65fr_0.45fr_0.45fr_0.45fr] items-center gap-6">
            {{-- Checkbox --}}
            <div class="flex items-center">
                <input
                    type="checkbox"
                    value="{{ $t->id }}"
                    data-task-checkbox
                    class="h-4 w-4 rounded border-[#191D38]/20"
                    x-model="selected"
                >
            </div>

            {{-- Taak --}}
            <div class="min-w-0">
              <p class="text-[#191D38] font-semibold text-sm truncate">
                {{ $t->name }}
              </p>
            </div>

            {{-- Status (klikbaar + popover) --}}
            <div
              class="relative"
              x-data="{ open:false }"
              x-on:click.outside="open=false"
              x-on:keydown.escape.window="open=false"
            >
              <button
                type="button"
                x-on:click="open = !open"
                class="{{ $taskPill['class'] }} cursor-pointer w-full text-xs font-semibold rounded-full py-1.5 text-center inline-flex items-center justify-center gap-2"
              >
                <span>{{ $taskPill['label'] }}</span>
              </button>

              <div
                x-cloak
                x-show="open"
                x-transition.origin.top
                class="absolute z-50 top-full mt-2 left-1/2 -translate-x-1/2 w-56"
              >
                <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-2 shadow-lg">
                  <form
                    method="POST"
                    action="{{ route('support.projecten.taken.update', ['project' => $project, 'task' => $t]) }}"
                    class="max-h-64 overflow-y-auto custom-scroll space-y-2"

                    {{-- ✅ HTMX: alleen tasks blok verversen --}}
                    hx-patch="{{ route('support.projecten.taken.update', ['project' => $project, 'task' => $t]) }}"
                    hx-target="#project-tasks"
                    hx-swap="outerHTML"
                  >
                    @csrf
                    @method('PATCH')

                    <button type="submit" name="status" value="pending"
                      class="text-[#DF2935] cursor-pointer bg-[#DF2935]/20 w-full rounded-full py-2.5 text-xs font-semibold text-center hover:opacity-90 transition duration-200">
                      Te voldoen
                    </button>

                    <button type="submit" name="status" value="active"
                      class="text-[#DF9A57] cursor-pointer bg-[#DF9A57]/20 w-full rounded-full py-2.5 text-xs font-semibold text-center hover:opacity-90 transition duration-200">
                      Bezig
                    </button>

                    <button type="submit" name="status" value="done"
                      class="text-[#87A878] cursor-pointer bg-[#87A878]/20 w-full rounded-full py-2.5 text-xs font-semibold text-center hover:opacity-90 transition duration-200">
                      Voldaan
                    </button>
                  </form>
                </div>
              </div>
            </div>

            {{-- Verantwoordelijke (klikbaar + popover + zoek) --}}
            <div
              class="relative"
              x-data='{
                open:false,
                q:"",
                users: @json($assigneeOptions),
                selectedId: @json($t->assigned_user_id),

                filtered(){
                  const q = (this.q || "").toLowerCase().trim();
                  if(!q) return this.users;
                  return this.users.filter(u => (u.name || "").toLowerCase().includes(q));
                },

                pick(id){
                  this.selectedId = id;
                  this.open = false;
                  this.$nextTick(() => this.$refs.assigneeForm.requestSubmit());
                }
              }'
              x-on:click.outside="open=false"
              x-on:keydown.escape.window="open=false"
            >
              <button
                type="button"
                x-on:click="open = !open"
                class="w-full inline-flex items-center justify-between gap-2 text-sm font-semibold hover:text-[#009AC3] transition"
              >
                <span class="text-[#191D38] truncate">
                  {{ $t->assignedUser?->name ?? 'Kies persoon' }}
                </span>
                <svg class="shrink-0 opacity-40" width="16" height="16" viewBox="0 0 24 24" fill="none">
                  <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </button>
              <div
                x-cloak
                x-show="open"
                x-transition.origin.top
                class="absolute z-50 top-full mt-2 left-1/2 -translate-x-1/2 w-72"
              >
                <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-3 shadow-lg">
                  <form
                    method="POST"
                    action="{{ route('support.projecten.taken.assignee', ['project' => $project, 'task' => $t]) }}"
                    x-ref="assigneeForm"

                    hx-patch="{{ route('support.projecten.taken.assignee', ['project' => $project, 'task' => $t]) }}"
                    hx-target="#project-tasks"
                    hx-swap="outerHTML"
                  >
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="assigned_user_id" :value="selectedId">
                    {{-- Zoek --}}
                    <div class="mb-2">
                      <input
                        type="text"
                        x-model="q"
                        placeholder="Zoek gebruiker..."
                        class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3]"
                      >
                    </div>
                    {{-- Lijst --}}
                    <div class="max-h-60 overflow-y-auto custom-scroll space-y-2">
                      {{-- Unassign --}}
                      <button
                        type="button"
                        x-on:click="pick(null)"
                        class="w-full rounded-full py-2.5 text-xs font-semibold text-center transition
                              bg-[#2A324B]/10 text-[#2A324B]/60 hover:opacity-90"
                      >
                        Geen verantwoordelijke
                      </button>
                      <template x-for="u in filtered()" :key="'u-'+u.id">
                        <button
                          type="button"
                          x-on:click="pick(u.id)"
                          class="w-full rounded-full py-2.5 text-xs font-semibold text-center transition
                                bg-[#191D38]/5 text-[#191D38] hover:bg-[#191D38]/10"
                        >
                          <span x-text="u.name"></span>
                          <span class="opacity-40" x-show="u.rol"> • <span x-text="u.rol"></span></span>
                        </button>
                      </template>
                      <div
                        class="py-6 text-center text-xs font-semibold text-[#191D38]/40"
                        x-show="filtered().length === 0"
                      >
                        Geen resultaten.
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            {{-- Locatie --}}
            <div class="text-[#191D38] text-sm">
              {{ $t->location ?: '—' }}
            </div>

            {{-- ✅ Datum te voldoen --}}
            <div
              class="relative"
              x-data='{
                open:false,
                monthNames: ["januari","februari","maart","april","mei","juni","juli","augustus","september","oktober","november","december"],
                dayNames: ["Ma","Di","Wo","Do","Vr","Za","Zo"],
                minDate: @json(now()->format("Y-m-d")),
                viewYear: (new Date()).getFullYear(),
                viewMonth: (new Date()).getMonth(),
                selectedDate: @json(optional($t->due_date)->format("Y-m-d")),

                init(){
                  if(this.selectedDate){
                    const d = this.parseKey(this.selectedDate);
                    if(d){ this.viewYear = d.getFullYear(); this.viewMonth = d.getMonth(); }
                  }
                },

                pad(n){ return String(n).padStart(2,"0"); },
                toKey(d){ return d.getFullYear()+"-"+this.pad(d.getMonth()+1)+"-"+this.pad(d.getDate()); },
                parseKey(k){
                  if(!k) return null;
                  const [y,m,dd] = k.split("-").map(Number);
                  return new Date(y, m-1, dd);
                },

                isWeekend(d){ const w = d.getDay(); return (w === 0 || w === 6); },
                isBeforeMin(d){ return this.toKey(d) < this.minDate; },
                inViewMonth(d){ return d.getMonth() === this.viewMonth && d.getFullYear() === this.viewYear; },

                monthLabel(){ return this.monthNames[this.viewMonth] + " " + this.viewYear; },

                prevMonth(){
                  const d = new Date(this.viewYear, this.viewMonth - 1, 1);
                  this.viewYear = d.getFullYear();
                  this.viewMonth = d.getMonth();
                },

                nextMonth(){
                  const d = new Date(this.viewYear, this.viewMonth + 1, 1);
                  this.viewYear = d.getFullYear();
                  this.viewMonth = d.getMonth();
                },

                calendarCells(){
                  const first = new Date(this.viewYear, this.viewMonth, 1);
                  const offset = (first.getDay() + 6) % 7;
                  const start = new Date(this.viewYear, this.viewMonth, 1 - offset);

                  const cells = [];
                  for(let i=0;i<42;i++){
                    const d = new Date(start.getFullYear(), start.getMonth(), start.getDate() + i);
                    const key = this.toKey(d);

                    const disabled = !this.inViewMonth(d) || this.isWeekend(d) || this.isBeforeMin(d);

                    cells.push({
                      date: d,
                      key,
                      day: d.getDate(),
                      disabled,
                      selected: this.selectedDate === key,
                    });
                  }
                  return cells;
                },

                pickDate(cell){
                  if(cell.disabled) return;
                  this.selectedDate = cell.key;
                  this.open = false;
                  this.$nextTick(() => this.$refs.dueForm.requestSubmit());
                }
              }'
              x-init="init()"
              x-on:click.outside="open=false"
              x-on:keydown.escape.window="open=false"
            >
              <button
                type="button"
                x-on:click="open = !open"
                class="w-full inline-flex items-center gap-2 text-sm font-semibold hover:text-[#009AC3] transition"
              >
                @if(!empty($t->due_date))
                  <span class="text-[#191D38]">{{ optional($t->due_date)->format('d-m-Y') }}</span>
                @else
                  <span class="inline-flex items-center gap-1.5 px-2.5 w-full h-8 border border-dashed border-[#191D38] rounded-full transition duration-200 opacity-20 hover:opacity-50 cursor-pointer">
                    <i class="fa-solid fa-calendar text-[#191D38]"></i>
                    <p class="text-[#191D38] text-xs">Kies datum</p>
                  </span>
                @endif
              </button>

              <div
                x-cloak
                x-show="open"
                x-transition.origin.top
                class="absolute z-50 top-full mt-2 left-1/2 -translate-x-1/2 w-[290px]"
              >
                <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-4 shadow-lg">
                  <form
                    method="POST"
                    action="{{ route('support.projecten.taken.due_date', ['project' => $project, 'task' => $t]) }}"
                    x-ref="dueForm"

                    {{-- ✅ HTMX: alleen tasks blok verversen --}}
                    hx-patch="{{ route('support.projecten.taken.due_date', ['project' => $project, 'task' => $t]) }}"
                    hx-target="#project-tasks"
                    hx-swap="outerHTML"
                  >
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="due_date" x-model="selectedDate">

                    <div class="flex items-center justify-between mb-3">
                      <button
                        type="button"
                        class="h-9 w-9 inline-flex items-center justify-center rounded-full font-semibold transition cursor-pointer
                              bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40"
                        x-on:click="prevMonth()"
                        aria-label="Vorige maand"
                      >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                          <path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>

                      <p class="text-sm font-black text-[#191D38]" x-text="monthLabel()"></p>

                      <button
                        type="button"
                        class="h-9 w-9 inline-flex items-center justify-center rounded-full font-semibold transition cursor-pointer
                              bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40"
                        x-on:click="nextMonth()"
                        aria-label="Volgende maand"
                      >
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                          <path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                      </button>
                    </div>

                    <p class="text-xs font-semibold text-[#191D38]/60 mb-3">
                      Alleen werkdagen (ma t/m vr).
                    </p>

                    <div class="grid grid-cols-7 gap-2 mb-2">
                      <template x-for="(dn, i) in dayNames" :key="i">
                        <div class="text-[11px] font-black text-[#191D38]/40 text-center" x-text="dn"></div>
                      </template>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                      <template x-for="(cell, i) in calendarCells()" :key="cell.key + '-' + i">
                        <button
                          type="button"
                          class="h-8 w-8 inline-flex items-center justify-center rounded-full font-black text-xs transition"
                          :class="cell.disabled
                            ? 'bg-[#2A324B]/5 text-[#2A324B]/15 cursor-not-allowed'
                            : (cell.selected
                              ? 'bg-[#009AC3] hover:bg-[#009AC3]/70 text-white cursor-pointer'
                              : 'bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40 cursor-pointer'
                            )"
                          x-on:click="pickDate(cell)"
                          :disabled="cell.disabled"
                        >
                          <span x-text="cell.day"></span>
                        </button>
                      </template>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            {{-- Datum voldaan --}}
            <div class="text-[#009AC3] text-sm text-right">
              {{ optional($t->completed_at)->format('d-m-Y H:i') ?? '—' }}
            </div>
          </div>
        @empty
          <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
            Nog geen taken.
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
