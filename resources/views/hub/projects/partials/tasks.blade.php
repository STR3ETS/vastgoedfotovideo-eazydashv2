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
  x-data='{
    projectId: @json($project->id),
    storageKey: null,

    selected: [],
    bulkStatus: "active",

    singleStatusUrl: @json(route("support.projecten.taken.update", ["project" => $project, "task" => "__TASK__"])),
    bulkStatusUrl: @json(route("support.projecten.taken.bulk_status", ["project" => $project])),

    addOpen: false,
    newTaskName: "",

    openAdd(){
      this.addOpen = true;
      this.$nextTick(() => this.$refs.newTaskInput?.focus());
    },

    closeAdd(){
      this.addOpen = false;
      this.newTaskName = "";
    },

    init(){
      this.storageKey = "project_tasks_selected_" + this.projectId;

      // restore selectie na HTMX swap / page reload
      this.restoreSelection();

      // drop ids die niet meer bestaan in de huidige DOM
      this.$nextTick(() => this.normalizeSelection());

      // persist bij elke wijziging
      this.$watch("selected", (v) => {
        try { sessionStorage.setItem(this.storageKey, JSON.stringify((v || []).map(String))); } catch(e) {}
      });
    },

    restoreSelection(){
      try{
        const raw = sessionStorage.getItem(this.storageKey);
        if(!raw) return;
        const arr = JSON.parse(raw);
        if(Array.isArray(arr)){
          this.selected = arr.map(String);
        }
      } catch(e) {}
    },

    normalizeSelection(){
      const ids = Array.from(this.$root.querySelectorAll("input[data-task-checkbox]"))
        .map(cb => String(cb.value));

      this.selected = (this.selected || []).map(String).filter(id => ids.includes(id));
    },

    isSelected(id){
      return this.selected.includes(String(id));
    },

    toggleAll(ev){
      const on = ev.target.checked;
      const ids = Array.from(this.$root.querySelectorAll("input[data-task-checkbox]"))
        .filter(cb => !cb.disabled)
        .map(cb => String(cb.value));

      this.selected = on ? ids : [];
    },

    isAllSelected(){
      const ids = Array.from(this.$root.querySelectorAll("input[data-task-checkbox]"))
        .filter(cb => !cb.disabled)
        .map(cb => String(cb.value));

      return ids.length > 0 && ids.every(id => this.selected.includes(id));
    },

    clearSelection(){
      this.selected = [];
      const master = this.$root.querySelector("input[data-master-checkbox]");
      if(master) master.checked = false;
    }
  }'
  x-init="init()"
>
  {{-- BELANGRIJK: overflow-visible zodat popovers niet worden afgeknipt --}}
  <div class="rounded-2xl overflow-visible">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
    <div class="flex items-center gap-3">
        <div class="flex items-center gap-2">
          <p class="text-[#191D38] font-black text-sm">Taken</p>
        </div>
        <p class="ml-4 text-[#191D38] font-bold text-xs opacity-50">
            Totaal: <span>{{ (int) ($project->tasks?->count() ?? 0) }}</span>
        </p>
      </div>

      <div class="flex items-center gap-2">
        <div
          x-cloak
          x-show="selected.length > 0"
          class="flex items-center gap-2 mr-4"
        >
          <span class="text-[#191D38] font-bold text-xs opacity-50">
              Geselecteerd: <span x-text="selected.length"></span>
          </span>
        </div>
        <button
          type="button"
          x-on:click="openAdd()"
          class="h-8 px-4 inline-flex items-center gap-2 rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition duration-200"
        >
          Nieuwe taak aanmaken
        </button>
        <form
          x-cloak
          x-show="selected.length > 0"
          method="POST"
          action="{{ route('support.projecten.taken.bulk_destroy', ['project' => $project]) }}"

          hx-post="{{ route('support.projecten.taken.bulk_destroy', ['project' => $project]) }}"
          hx-target="#project-tasks"
          hx-swap="outerHTML"
          hx-confirm="Weet je zeker dat je de geselecteerde taken wilt verwijderen?"
        >
          @csrf
          @method('DELETE')

          <template x-for="id in selected" :key="'bulk-del-'+id">
            <input type="hidden" name="task_ids[]" :value="id">
          </template>

          <button
            type="submit"
            class="h-8 px-4 inline-flex items-center gap-2 rounded-full bg-[#DF2935] text-white text-xs font-semibold hover:bg-[#DF2935]/80 transition duration-200"
          >
            Verwijder geselecteerde
          </button>
        </form>
      </div>
    </div>

    {{-- Header row --}}
    <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
      <div class="grid grid-cols-[0.18fr_1.12fr_0.45fr_0.65fr_0.45fr_0.45fr_0.45fr_0.22fr] items-center gap-6">
        <div class="flex items-center">
          <input
            type="checkbox"
            data-master-checkbox
            class="h-4 w-4 rounded border-[#191D38]/20"
            x-on:change="toggleAll($event)"
            :checked="isAllSelected()"
          >
        </div>

        <p class="text-[#191D38] font-bold text-xs opacity-50 pl-4">Taak</p>

        {{-- ✅ center boven pill --}}
        <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>

        <p class="text-[#191D38] font-bold text-xs opacity-50">Verantwoordelijke</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Locatie</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Datum te voldoen</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Datum voldaan</p>

        {{-- ✅ netter uitlijnen met icon-row --}}
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-right pr-1">Acties</p>
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

          <div
            class="py-3 grid grid-cols-[0.18fr_1.12fr_0.45fr_0.65fr_0.45fr_0.45fr_0.45fr_0.22fr] items-center gap-6 transition-opacity duration-200"
            x-data='{
              edit:false,
              name: @json($t->name),
              oName: @json($t->name),

              openEdit(){
                this.edit = true;
                this.$nextTick(() => this.$refs.nameInput?.focus());
              },

              cancelEdit(){
                this.name = this.oName;
                this.edit = false;
              }
            }'
            x-bind:class="(selected.length > 0 && !selected.includes(String({{ $t->id }}))) ? 'opacity-50' : 'opacity-100'"
          >
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
              {{-- read --}}
              <div x-show="!edit" class="h-9 flex items-center">
                <p class="w-full px-4 text-[#191D38] font-semibold text-sm truncate">
                  {{ $t->name }}
                </p>
              </div>

              {{-- edit --}}
              <form
                x-cloak
                x-show="edit"
                x-ref="nameForm"
                method="POST"
                action="{{ route('support.projecten.taken.name', ['project' => $project, 'task' => $t]) }}"

                hx-patch="{{ route('support.projecten.taken.name', ['project' => $project, 'task' => $t]) }}"
                hx-target="#project-tasks"
                hx-swap="outerHTML"
              >
                @csrf
                @method('PATCH')

                <input
                  x-ref="nameInput"
                  type="text"
                  name="name"
                  x-model="name"
                  class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
                  x-on:keydown.enter.prevent="$refs.nameForm.requestSubmit()"
                  x-on:keydown.escape.prevent="cancelEdit()"
                  required
                >
              </form>
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
                class="{{ $taskPill['class'] }} cursor-pointer w-full text-xs font-semibold rounded-full py-1.5 inline-flex items-center justify-start gap-2 px-4 text-left"
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
                    class="max-h-64 overflow-y-auto custom-scroll flex flex-col gap-2"

                    hx-patch="{{ route('support.projecten.taken.update', ['project' => $project, 'task' => $t]) }}"
                    hx-target="#project-tasks"
                    hx-swap="outerHTML"
                  >
                    @csrf
                    @method('PATCH')

                    {{-- ✅ Als deze taak geselecteerd is: stuur ALLE geselecteerde task_ids[] mee --}}
                    <template x-if="selected.length > 0 && isSelected({{ $t->id }})">
                      <div>
                        <template x-for="id in selected" :key="'bulk-status-'+id">
                          <input type="hidden" name="task_ids[]" :value="id">
                        </template>
                      </div>
                    </template>

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
                class="w-full inline-flex items-center justify-between gap-2 text-sm hover:text-[#009AC3] transition"
              >
                @if($t->assignedUser)
                  <span class="text-[#191D38] text-sm truncate max-w-[120px]">
                    {{ $t->assignedUser->name }}
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 px-2.5 w-full h-8 border border-dashed border-[#191D38] rounded-full transition duration-200 opacity-20 hover:opacity-50 cursor-pointer">
                    <i class="fa-solid fa-user text-[#191D38]"></i>
                    <p class="text-[#191D38] text-xs">Kies verantwoordelijke</p>
                  </span>
                @endif
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
                    {{-- ✅ Als deze taak geselecteerd is: stuur ALLE geselecteerde task_ids[] mee --}}
                    <template x-if="selected.length > 0 && isSelected({{ $t->id }})">
                      <div>
                        <template x-for="id in selected" :key="'bulk-assignee-'+id">
                          <input type="hidden" name="task_ids[]" :value="id">
                        </template>
                      </div>
                    </template>
                    {{-- Zoek --}}
                    <div class="mb-6">
                      <button
                        type="button"
                        x-on:click="pick(null)"
                        class="w-full cursor-pointer rounded-full py-2.5 text-xs font-semibold text-center transition duration-200 mb-2
                              text-[#DF2935] bg-[#DF2935]/20 hover:opacity-90"
                      >
                        Verantwoordelijke ontkoppelen
                      </button>
                      <input
                        type="text"
                        x-model="q"
                        placeholder="Zoek gebruiker..."
                        class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition duration-200"
                      >
                    </div>
                    {{-- Lijst --}}
                    <div class="max-h-60 overflow-y-auto custom-scroll space-y-2">
                      {{-- ✅ Unassign / NULL --}}
                      <template x-for="u in filtered()" :key="'u-'+u.id">
                        <button
                          type="button"
                          x-on:click="pick(u.id)"
                          class="w-full cursor-pointer rounded-full py-2.5 text-xs font-semibold text-center transition duration-200"
                          :class="(String(selectedId) === String(u.id))
                            ? 'bg-[#009AC3] hover:bg-[#009AC3]/70 text-white'
                            : 'bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40'
                          "
                        >
                          <span x-text="u.name"></span>
                        </button>
                      </template>
                      <div
                        class="py-6 text-center text-xs font-semibold text-[#191D38]/40"
                        x-show="filtered().length === 0"
                      >
                        Geen resultaten gevonden.
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>

            {{-- Locatie (klikbaar + autocomplete) --}}
            <div
              class="relative"
              x-data='{
                open:false,
                q: @json($t->location ?? ""),
                loading:false,
                items: [],
                timer:null,

                fetch(){
                  const q = (this.q || "").trim();
                  if(q.length < 3){ this.items = []; return; }

                  this.loading = true;

                  fetch(@json(route('support.projecten.taken.location_suggest', ['project' => $project])) + "?q=" + encodeURIComponent(q), {
                    headers: { "Accept": "application/json" }
                  })
                  .then(r => r.json())
                  .then(data => { this.items = (data.items || []); })
                  .catch(() => { this.items = []; })
                  .finally(() => { this.loading = false; });
                },

                onInput(){
                  clearTimeout(this.timer);
                  this.timer = setTimeout(() => this.fetch(), 180);
                },

                pick(item){
                  this.q = item.value;
                  this.open = false;
                  this.$nextTick(() => this.$refs.locForm.requestSubmit());
                },

                clear(){
                  this.q = "";
                  this.open = false;
                  this.$nextTick(() => this.$refs.locForm.requestSubmit());
                }
              }'
              x-on:click.outside="open=false"
              x-on:keydown.escape.window="open=false"
            >
              <button
                type="button"
                x-on:click="open = !open; if(open) { $nextTick(() => $refs.locInput?.focus()); }"
                class="w-full inline-flex items-center justify-between gap-2 text-sm hover:text-[#009AC3] transition"
              >
                @if(!empty($t->location))
                  <span class="text-[#191D38] text-sm truncate max-w-[120px]">{{ $t->location }}</span>
                @else
                  <span class="inline-flex items-center gap-1.5 px-2.5 w-full h-8 border border-dashed border-[#191D38] rounded-full transition duration-200 opacity-20 hover:opacity-50 cursor-pointer">
                    <i class="fa-solid fa-location-dot text-[#191D38]"></i>
                    <p class="text-[#191D38] text-xs">Kies locatie</p>
                  </span>
                @endif
              </button>

              <div
                x-cloak
                x-show="open"
                x-transition.origin.top
                class="absolute z-50 top-full mt-2 left-1/2 -translate-x-1/2 w-80"
              >
                <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-3 shadow-lg">
                  <form
                    method="POST"
                    action="{{ route('support.projecten.taken.location', ['project' => $project, 'task' => $t]) }}"
                    x-ref="locForm"

                    hx-patch="{{ route('support.projecten.taken.location', ['project' => $project, 'task' => $t]) }}"
                    hx-target="#project-tasks"
                    hx-swap="outerHTML"
                  >
                    @csrf
                    @method('PATCH')

                    <input type="hidden" name="location" :value="q">

                    {{-- ✅ Als deze taak geselecteerd is: stuur ALLE geselecteerde task_ids[] mee --}}
                    <template x-if="selected.length > 0 && isSelected({{ $t->id }})">
                      <div>
                        <template x-for="id in selected" :key="'bulk-location-'+id">
                          <input type="hidden" name="task_ids[]" :value="id">
                        </template>
                      </div>
                    </template>

                    <div class="flex flex-col gap-2 mb-6">
                      <button
                        type="button"
                        x-on:click="clear()"
                        class="w-full cursor-pointer rounded-full py-2.5 text-xs font-semibold text-center transition duration-200
                              text-[#DF2935] bg-[#DF2935]/20 hover:opacity-90"
                      >
                        Locatie resetten
                      </button>
                      <input
                        type="text"
                        x-ref="locInput"
                        x-model="q"
                        x-on:input="onInput()"
                        placeholder="Typ een adres..."
                        class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition duration-200"
                      >
                    </div>

                    <div class="text-[11px] font-semibold text-[#191D38]/50">
                      <span x-show="loading">Zoeken...</span>
                      <span x-show="!loading && (items.length === 0) && (q || '').trim().length >= 3">Geen resultaten.</span>
                      <span x-show="(q || '').trim().length < 3">Typ minimaal 3 tekens.</span>
                    </div>

                    <div class="max-h-60 overflow-y-auto custom-scroll space-y-2">
                      <template x-for="it in items" :key="it.value">
                        <button
                          type="button"
                          x-on:click="pick(it)"
                          class="w-full cursor-pointer rounded-full py-2.5 text-xs font-semibold text-center transition duration-200"
                          :class="(q === it.value)
                            ? 'bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40'
                            : 'bg-[#2A324B]/20 hover:bg-[#2A324B]/10 text-[#2A324B]/40'
                          "
                        >
                          <span x-text="it.label"></span>
                        </button>
                      </template>
                    </div>
                  </form>
                </div>
              </div>
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
                class="w-full inline-flex items-center gap-2 text-sm hover:text-[#009AC3] transition"
              >
                @if(!empty($t->due_date))
                  <span class="text-[#191D38] text-sm">{{ optional($t->due_date)->format('d-m-Y') }}</span>
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

                    {{-- ✅ Als deze taak geselecteerd is: stuur ALLE geselecteerde task_ids[] mee --}}
                    <template x-if="selected.length > 0 && isSelected({{ $t->id }})">
                      <div>
                        <template x-for="id in selected" :key="'bulk-due-'+id">
                          <input type="hidden" name="task_ids[]" :value="id">
                        </template>
                      </div>
                    </template>

                    <button
                      type="button"
                      x-on:click="
                        selectedDate = null;
                        open = false;
                        $nextTick(() => $refs.dueForm.requestSubmit());
                      "
                      class="w-full cursor-pointer rounded-full py-2.5 text-xs font-semibold text-center transition duration-200 mb-3
                            text-[#DF2935] bg-[#DF2935]/20 hover:opacity-90"
                    >
                      Datum resetten
                    </button>

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
            <div class="text-[#009AC3] text-sm">
              {{ optional($t->completed_at)->format('d-m-Y H:i') ?? 'Te voldoen' }}
            </div>

            {{-- Acties --}}
            <div class="flex items-center justify-end gap-2">
              {{-- pencil --}}
              <!-- <button
                type="button"
                x-show="!edit"
                x-on:click="openEdit()"
                class="cursor-pointer"
                title="Bewerk taaknaam"
              >
                <i class="fa-solid fa-pen-to-square hover:text-[#009AC3] transition duration-200"></i>
              </button> -->

              {{-- save/cancel (edit mode) --}}
              <button
                x-cloak
                x-show="edit"
                type="button"
                x-on:click="$refs.nameForm.requestSubmit()"
                class="cursor-pointer"
                title="Opslaan (Enter)"
              >
                <i class="fa-solid fa-check hover:text-[#009AC3] transition duration-200"></i>
              </button>

              <button
                x-cloak
                x-show="edit"
                type="button"
                x-on:click="cancelEdit()"
                class="cursor-pointer"
                title="Annuleren (Esc)"
              >
                <i class="fa-solid fa-xmark hover:text-[#009AC3] transition duration-200"></i>
              </button>

              {{-- eye + trash alleen als niet aan het editen --}}
              <a
                x-cloak
                x-show="!edit"
                href="{{ route('support.projecten.taken.show', ['project' => $project, 'task' => $t]) }}"
                class="cursor-pointer"
                title="Bekijk taak"
              >
                <i class="fa-solid fa-eye hover:text-[#009AC3] transition duration-200"></i>
              </a>

              <form
                x-cloak
                x-show="!edit"
                method="POST"
                action="{{ route('support.projecten.taken.destroy', ['project' => $project, 'task' => $t]) }}"

                hx-post="{{ route('support.projecten.taken.destroy', ['project' => $project, 'task' => $t]) }}"
                hx-target="#project-tasks"
                hx-swap="outerHTML"
                hx-confirm="Weet je zeker dat je deze taak wilt verwijderen?"
              >
                @csrf
                @method('DELETE')

                <button class="cursor-pointer" type="submit" title="Verwijder taak">
                  <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                </button>
              </form>
            </div>
          </div>
        @empty
          <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
            Nog geen taken.
          </div>
        @endforelse
        {{-- ✅ Nieuwe taak rij (onderaan) --}}
        <form
          x-cloak
          x-show="addOpen"
          class="py-3 flex items-center justify-between gap-6"
          method="POST"
          action="{{ route('support.projecten.taken.store', ['project' => $project]) }}"

          hx-post="{{ route('support.projecten.taken.store', ['project' => $project]) }}"
          hx-target="#project-tasks"
          hx-swap="outerHTML"
        >
          @csrf

          {{-- kolom 2 --}}
          <div class="w-1/2">
            <input
              x-ref="newTaskInput"
              type="text"
              name="name"
              x-model="newTaskName"
              placeholder="Nieuwe taaknaam..."
              class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              x-on:keydown.escape.prevent="closeAdd()"
            >
          </div>

          {{-- kolom 7 --}}
          <div class="w-1/3 flex items-center justify-end gap-2">
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
              x-bind:disabled="!newTaskName || !newTaskName.trim()"
            >
              Opslaan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
