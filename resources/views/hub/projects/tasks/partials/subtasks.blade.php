@php
  $sectionHeader = $sectionHeader ?? "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = $sectionBody   ?? "bg-[#191D38]/5";

  $assignees = $assignees ?? collect();
  $assigneeOptions = $assignees->map(fn($u) => [
    'id' => $u->id,
    'name' => $u->name,
    'rol' => $u->rol ?? null,
  ])->values();

  $subtaskCols = "grid-cols-[28px_minmax(260px,1fr)_110px_44px] gap-5";
@endphp

<div
  id="task-subtasks"
  class="col-span-2"
  x-data='{
    taskId: @json($task->id),
    storageKey: null,

    selected: [],
    addOpen: false,
    newName: "",

    openAdd(){
      this.addOpen = true;
      this.$nextTick(() => this.$refs.newInput?.focus());
    },
    closeAdd(){
      this.addOpen = false;
      this.newName = "";
    },

    init(){
      this.storageKey = "task_subtasks_selected_" + this.taskId;

      this.restoreSelection();
      this.$nextTick(() => this.normalizeSelection());

      this.$watch("selected", (v) => {
        try { sessionStorage.setItem(this.storageKey, JSON.stringify((v || []).map(String))); } catch(e) {}
      });
    },

    restoreSelection(){
      try{
        const raw = sessionStorage.getItem(this.storageKey);
        if(!raw) return;
        const arr = JSON.parse(raw);
        if(Array.isArray(arr)) this.selected = arr.map(String);
      } catch(e) {}
    },

    normalizeSelection(){
      const ids = Array.from(this.$root.querySelectorAll("input[data-subtask-checkbox]"))
        .map(cb => String(cb.value));
      this.selected = (this.selected || []).map(String).filter(id => ids.includes(id));
    },

    isSelected(id){ return this.selected.includes(String(id)); },

    toggleAll(ev){
      const on = ev.target.checked;
      const ids = Array.from(this.$root.querySelectorAll("input[data-subtask-checkbox]"))
        .filter(cb => !cb.disabled)
        .map(cb => String(cb.value));
      this.selected = on ? ids : [];
    },

    isAllSelected(){
      const ids = Array.from(this.$root.querySelectorAll("input[data-subtask-checkbox]"))
        .filter(cb => !cb.disabled)
        .map(cb => String(cb.value));
      return ids.length > 0 && ids.every(id => this.selected.includes(id));
    }
  }'
  x-init="init()"
>
  <div class="rounded-2xl overflow-visible">
    <div class="{{ $sectionHeader }} rounded-t-2xl flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <p class="text-[#191D38] font-black text-sm">Subtaken</p>
        <p class="ml-4 text-[#191D38] font-bold text-xs opacity-50">
          Totaal: <span>{{ (int) ($task->subtasks?->count() ?? 0) }}</span>
        </p>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          x-on:click="openAdd()"
          class="h-8 px-4 inline-flex items-center gap-2 rounded-full bg-[#009AC3] text-white text-xs font-semibold hover:bg-[#009AC3]/80 transition duration-200"
        >
          Nieuwe subtaak
        </button>

        <form
          x-cloak
          x-show="selected.length > 0"
          method="POST"
          action="{{ route('support.projecten.taken.subtasks.bulk_destroy', ['project' => $project, 'task' => $task]) }}"

          hx-post="{{ route('support.projecten.taken.subtasks.bulk_destroy', ['project' => $project, 'task' => $task]) }}"
          hx-target="#task-subtasks"
          hx-swap="outerHTML"
          hx-confirm="Weet je zeker dat je de geselecteerde subtaken wilt verwijderen?"
        >
          @csrf
          @method('DELETE')

          <template x-for="id in selected" :key="'bulk-sub-del-'+id">
            <input type="hidden" name="subtask_ids[]" :value="id">
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

<div class="overflow-visible">
  <div class="w-full">
    {{-- Header row --}}
    <div class="px-6 py-4 bg-[#191D38]/10 border-t border-[#191D38]/10">
      <div class="grid {{ $subtaskCols }} items-center">
        <div class="flex items-center">
          <input
            type="checkbox"
            data-master-checkbox
            class="h-4 w-4 rounded border-[#191D38]/20"
            x-on:change="toggleAll($event)"
            :checked="isAllSelected()"
          >
        </div>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Subtaak</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
        <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
      </div>
    </div>

    {{-- Body --}}
    <div class="{{ $sectionBody }} rounded-b-2xl">
      <div class="px-6 py-2 divide-y divide-[#191D38]/10">
        @forelse($task->subtasks as $st)
          @php
            $statusMap = [
              'pending'   => ['label' => 'Te voldoen',   'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
              'active'    => ['label' => 'Bezig',       'class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
              'done'      => ['label' => 'Voldaan',     'class' => 'text-[#87A878] bg-[#87A878]/20'],
              'cancelled' => ['label' => 'Gesloten',    'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
              'archived'  => ['label' => 'Gearchiveerd','class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
            ];

            $k = strtolower((string) ($st->status ?? 'active'));
            $pill = $statusMap[$k] ?? ['label' => ucfirst($k), 'class' => 'text-[#2A324B] bg-[#2A324B]/20'];
          @endphp

          <div
            class="py-3 grid {{ $subtaskCols }} items-center transition-opacity duration-200"
            x-bind:class="(selected.length > 0 && !selected.includes(String({{ $st->id }}))) ? 'opacity-50' : 'opacity-100'"
          >
            {{-- Checkbox --}}
            <div class="flex items-center">
              <input
                type="checkbox"
                value="{{ $st->id }}"
                data-subtask-checkbox
                class="h-4 w-4 rounded border-[#191D38]/20"
                x-model="selected"
              >
            </div>

            {{-- Naam --}}
            <div class="min-w-0">
              <p class="text-[#191D38] font-semibold text-sm truncate">{{ $st->name }}</p>
            </div>

            {{-- Status popover --}}
            <div class="relative" x-data="{ open:false }" x-on:click.outside="open=false" x-on:keydown.escape.window="open=false">
              <button
                type="button"
                x-on:click="open = !open"
                class="{{ $pill['class'] }} cursor-pointer w-full text-xs font-semibold rounded-full py-1.5 text-center inline-flex items-center justify-center"
              >
                {{ $pill['label'] }}
              </button>

              <div x-cloak x-show="open" x-transition.origin.top class="absolute z-50 top-full mt-2 left-1/2 -translate-x-1/2 w-56">
                <div class="bg-white ring-1 ring-[#191D38]/10 rounded-2xl p-2 shadow-lg">
                  <form
                    method="POST"
                    action="{{ route('support.projecten.taken.subtasks.status', ['project' => $project, 'task' => $task, 'subtask' => $st]) }}"
                    class="flex flex-col gap-2"

                    hx-patch="{{ route('support.projecten.taken.subtasks.status', ['project' => $project, 'task' => $task, 'subtask' => $st]) }}"
                    hx-target="#task-subtasks"
                    hx-swap="outerHTML"
                  >
                    @csrf
                    @method('PATCH')

                    <template x-if="selected.length > 0 && isSelected({{ $st->id }})">
                      <div>
                        <template x-for="id in selected" :key="'bulk-st-status-'+id">
                          <input type="hidden" name="subtask_ids[]" :value="id">
                        </template>
                      </div>
                    </template>

                    <button type="submit" name="status" value="pending" class="text-[#DF2935] bg-[#DF2935]/20 w-full rounded-full py-2.5 text-xs font-semibold hover:opacity-90 transition">
                      Te voldoen
                    </button>
                    <button type="submit" name="status" value="active" class="text-[#DF9A57] bg-[#DF9A57]/20 w-full rounded-full py-2.5 text-xs font-semibold hover:opacity-90 transition">
                      Bezig
                    </button>
                    <button type="submit" name="status" value="done" class="text-[#87A878] bg-[#87A878]/20 w-full rounded-full py-2.5 text-xs font-semibold hover:opacity-90 transition">
                      Voldaan
                    </button>
                  </form>
                </div>
              </div>
            </div>

            {{-- Acties --}}
            <div class="flex items-center justify-end">
              <form
                method="POST"
                action="{{ route('support.projecten.taken.subtasks.destroy', ['project' => $project, 'task' => $task, 'subtask' => $st]) }}"

                hx-post="{{ route('support.projecten.taken.subtasks.destroy', ['project' => $project, 'task' => $task, 'subtask' => $st]) }}"
                hx-target="#task-subtasks"
                hx-swap="outerHTML"
                hx-confirm="Weet je zeker dat je deze subtaak wilt verwijderen?"
              >
                @csrf
                @method('DELETE')

                <button class="cursor-pointer" type="submit" title="Verwijder subtaak">
                  <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                </button>
              </form>
            </div>
          </div>
        @empty
          <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
            Nog geen subtaken.
          </div>
        @endforelse

        {{-- Nieuwe subtaak rij --}}
        <form
          x-cloak
          x-show="addOpen"
          class="py-3 flex items-center justify-between gap-6"
          method="POST"
          action="{{ route('support.projecten.taken.subtasks.store', ['project' => $project, 'task' => $task]) }}"

          hx-post="{{ route('support.projecten.taken.subtasks.store', ['project' => $project, 'task' => $task]) }}"
          hx-target="#task-subtasks"
          hx-swap="outerHTML"
        >
          @csrf

          <div class="w-1/2">
            <input
              x-ref="newInput"
              type="text"
              name="name"
              x-model="newName"
              placeholder="Nieuwe subtaak..."
              class="h-9 w-full rounded-full px-4 text-xs font-semibold bg-white ring-1 ring-[#191D38]/10 text-[#191D38] outline-none focus:ring-[#009AC3] transition"
              x-on:keydown.escape.prevent="closeAdd()"
            >
          </div>

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
              x-bind:disabled="!newName || !newName.trim()"
            >
              Opslaan
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

  </div>
</div>
