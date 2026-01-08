@extends('hub.layouts.app')

@section('content')
@php
  $taskStatusMap = [
    'pending'   => ['label' => 'Te voldoen',   'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
    'active'    => ['label' => 'Bezig',       'class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
    'done'      => ['label' => 'Voldaan',     'class' => 'text-[#87A878] bg-[#87A878]/20'],
    'cancelled' => ['label' => 'Gesloten',    'class' => 'text-[#DF2935] bg-[#DF2935]/20'],
    'archived'  => ['label' => 'Gearchiveerd','class' => 'text-[#DF9A57] bg-[#DF9A57]/20'],
  ];

  $taskKey  = strtolower((string) ($task->status ?? 'pending'));
  $taskPill = $taskStatusMap[$taskKey] ?? [
    'label' => ucfirst($taskKey),
    'class' => 'text-[#2A324B] bg-[#2A324B]/20',
  ];

  // Breadcrumbs (zelfde gedachte als projecten/show)
  $req = $project->onboardingRequest ?? null;

  $crumbProject = trim(($req->address ?? '') . ' — ' . ($req->city ?? ''));
  $crumbProject = $crumbProject !== ' — ' ? $crumbProject : ($project->title ?? 'Project');

  // Buttons / UI helpers (zelfde stijl als projecten/show)
  $navPrevBtn = "h-9 inline-flex text-xs items-center justify-center bg-[#2A324B]/20 hover:bg-[#2A324B]/10 transition duration-200 px-6 text-[#2A324B]/40 rounded-full font-semibold cursor-pointer";

  $sectionWrap   = "overflow-hidden rounded-2xl";
  $sectionHeader = "shrink-0 px-6 py-4 bg-[#191D38]/10";
  $sectionBody   = "bg-[#191D38]/5";
  $labelClass    = "text-[#191D38] font-bold text-xs opacity-50";
  $valueClass    = "text-[#191D38] text-sm font-semibold";

  // Verantwoordelijke (task user)
  $assigned = $task->assignedUser;

  $assignedPhone =
    $assigned?->phone
    ?? $assigned?->phone_number
    ?? $assigned?->mobile
    ?? $assigned?->tel
    ?? null;

  $assignedRole =
    $assigned?->rol
    ?? $assigned?->role
    ?? null;

  $assignedRoleLabel = $assignedRole
    ? ucfirst(str_replace('-', ' ', (string) $assignedRole))
    : '—';

  // Taakbeschrijving (DB veld)
  $taskDescription = (string) ($task->description ?? '');
@endphp

<div class="col-span-5 flex-1 min-h-0">
  <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

    {{-- Breadcrumbs (sticky / altijd zichtbaar) --}}
    <div class="shrink-0 mb-4 flex items-center justify-between">
      <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs font-semibold text-[#191D38]/50">
        <a href="{{ route('support.dashboard') }}" class="hover:text-[#191D38] transition">Dashboard</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.projecten.index') }}" class="hover:text-[#191D38] transition">Projecten</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.projecten.index') }}" class="hover:text-[#191D38] transition">Overzicht</a>
        <span class="opacity-40">/</span>
        <a href="{{ route('support.projecten.show', ['project' => $project]) }}" class="hover:text-[#191D38] transition">
          {{ $crumbProject }}
        </a>
        <span class="opacity-40">/</span>
        <span class="text-[#009AC3]">{{ $task->name }}</span>
      </nav>

      <div class="flex items-center gap-4">
        <a href="{{ route('support.projecten.show', ['project' => $project]) }}" class="{{ $navPrevBtn }}">
          Terug naar project
        </a>

        <div class="{{ $taskPill['class'] }} text-xs font-semibold rounded-full h-9 flex items-center px-8 text-center">
          {{ $taskPill['label'] }}
        </div>
      </div>
    </div>

    {{-- Content --}}
    <div class="flex-1 min-h-0">
      {{-- 2 kolommen: links scrollt, rechts blijft staan --}}
      <div class="grid grid-cols-3 gap-6 h-full min-h-0">

        {{-- LINKS (scroll area) --}}
        <div class="col-span-2 min-h-0 overflow-y-auto pr-2 pl-1">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 pb-1">

            {{-- ... al je bestaande blokken links 그대로 ... --}}
            {{-- Verantwoordelijke --}}
            <div class="{{ $sectionWrap }}">
              <div class="{{ $sectionHeader }}">
                <p class="text-[#191D38] font-black text-sm">Verantwoordelijke</p>
              </div>
              <div class="{{ $sectionBody }}">
                <div class="px-6 py-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <p class="{{ $labelClass }} mb-1">Naam</p>
                      <p class="{{ $valueClass }}">{{ $assigned?->name ?? 'Niet gekoppeld' }}</p>
                    </div>
                    <div>
                      <p class="{{ $labelClass }} mb-1">Rol</p>
                      <p class="{{ $valueClass }}">{{ $assignedRoleLabel }}</p>
                    </div>
                  </div>
                </div>

                <div class="border-t border-[#191D38]/10 px-6 py-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <p class="{{ $labelClass }} mb-1">E-mail</p>
                      <p class="{{ $valueClass }}">{{ $assigned?->email ?? '—' }}</p>
                    </div>
                    <div>
                      <p class="{{ $labelClass }} mb-1">Telefoon</p>
                      <p class="{{ $valueClass }}">{{ $assignedPhone ?? '—' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Taakbeschrijving --}}
            <div class="{{ $sectionWrap }}">
              <div class="{{ $sectionHeader }}">
                <p class="text-[#191D38] font-black text-sm">Taakbeschrijving</p>
              </div>
              <div class="{{ $sectionBody }}">
                <div class="px-6 py-4">
                  <form
                    hx-patch="{{ route('support.projecten.taken.description', ['project' => $project, 'task' => $task]) }}"
                    hx-trigger="blur from:#task-description"
                    hx-swap="none"
                    hx-headers='{"X-CSRF-TOKEN":"{{ csrf_token() }}"}'
                  >
                    <textarea
                      id="task-description"
                      name="description"
                      class="w-full min-h-[105px] max-h-[105px] resize-y rounded-2xl bg-white px-4 py-3 text-xs font-semibold text-[#191D38] ring-1 ring-[#191D38]/10 focus:outline-none focus:ring-[#009AC3] transition duration-200"
                      placeholder="Beschrijf hier de taak..."
                    >{{ $taskDescription }}</textarea>
                  </form>
                </div>
              </div>
            </div>

            <hr class="border-[#191D38]/10 col-span-2 my-4">

            {{-- Subtaken --}}
            @include('hub.projects.tasks.partials.subtasks', [
              'project' => $project,
              'task' => $task,
              'assignees' => $assignees ?? collect(),
              'sectionHeader' => $sectionHeader ?? null,
              'sectionBody' => $sectionBody ?? null,
            ])

            <hr class="border-[#191D38]/10 col-span-2 my-4">

            {{-- Logboek --}}
            <div class="lg:col-span-2 {{ $sectionWrap }}">
              <div class="{{ $sectionHeader }}">
                <p class="text-[#191D38] font-black text-sm">Logboek</p>
              </div>

              <div class="{{ $sectionBody }}">
                <div class="px-6 divide-y divide-[#191D38]/10">
                  @forelse($task->logs as $log)
                    @php
                      $logLabel = match($log->event) {
                        'description_filled'  => 'Taakbeschrijving ingevuld',
                        'description_cleared' => 'Taakbeschrijving leeg gemaakt',
                        'description_updated' => 'Taakbeschrijving aangepast',
                        default               => ucfirst(str_replace('_',' ', (string) $log->event)),
                      };
                    @endphp

                    <div class="py-4 flex items-start justify-between relative">
                      <div class="min-w-0">
                        <div class="flex gap-6">
                          <div>
                            <p class="{{ $labelClass }} mb-1">Wat er is gebeurd</p>
                            <p class="{{ $valueClass }}">{{ $logLabel }}</p>
                          </div>
                          <div>
                            <p class="{{ $labelClass }} mb-1">Door</p>
                            <p class="{{ $valueClass }}">{{ $log->user?->name ?? 'Onbekend' }}</p>
                          </div>
                          @if(!empty($log->new_value))
                              <div>
                                <p class="{{ $labelClass }} mb-1">Bericht</p>
                                <p class="text-[#191D38] text-sm italic max-w-[300px] truncate">{{ \Illuminate\Support\Str::limit($log->new_value, 180) }}</p>
                              </div>
                          @endif
                        </div>
                      </div>
                      <div class="shrink-0 absolute right-0 top-1/2 -translate-y-1/2">
                        <span class="text-[#DF9A57] bg-[#DF9A57]/20 text-xs font-semibold rounded-full py-1.5 px-4 text-center">
                          Aanpassing
                        </span>
                      </div>
                    </div>
                  @empty
                    <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">
                      Nog geen logs.
                    </div>
                  @endforelse
                </div>
              </div>
            </div>

          </div>
        </div>

        {{-- RECHTS (sticky chat) --}}
        <div class="col-span-1 h-full min-h-0">
          <div class="sticky top-0 h-full min-h-0 rounded-2xl overflow-hidden ring-1 ring-[#191D38]/10 flex flex-col">
            {{-- Header --}}
            <div class="shrink-0 shrink-0 px-6 py-4 bg-[#191D38]/10 border-b border-[#191D38]/10">
              <p class="text-[#191D38] font-black text-sm">Chat</p> 
            </div>
            {{-- Messages (scroll only inside chat) --}}
            <div
              id="task-chat-messages"
              class="flex-1 min-h-0 overflow-y-auto px-3 py-3 custom-scroll bg-[#191D38]/5"
              hx-get="{{ route('support.projecten.taken.chat.messages', ['project' => $project, 'task' => $task]) }}"
              hx-trigger="load, every 3s"
              hx-swap="innerHTML"
            >
              {{-- initial content fallback --}}
              <div class="py-10 text-center text-sm font-semibold text-[#191D38]/50">Chat laden…</div>
            </div>
            {{-- Composer --}}
            <div class="shrink-0 bg-[#191D38]/10 border-t border-[#191D38]/10 p-3">
              <form
                id="task-chat-form"
                class="w-full"
                hx-post="{{ route('support.projecten.taken.chat.store', ['project' => $project, 'task' => $task]) }}"
                hx-encoding="multipart/form-data"
                hx-target="#task-chat-messages"
                hx-swap="innerHTML"
                hx-headers='{"X-CSRF-TOKEN":"{{ csrf_token() }}"}'
              >
                <div class="relative w-full">
                  {{-- Hidden file input --}}
                  <input
                    id="chat-attachments"
                    type="file"
                    name="attachments[]"
                    class="hidden"
                    multiple
                  >

                  {{-- Paperclip icon (inside input) --}}
                  <label
                    for="chat-attachments"
                    class="absolute right-2 bottom-4 inline-flex items-center justify-center rounded-full cursor-pointer"
                    title="Bestand bijvoegen"
                  >
                    <i class="fa-solid fa-paperclip fa-sm opacity-50 hover:opacity-70 transition duration-200"></i>
                  </label>

                  {{-- Message input --}}
                  <textarea
                    id="chat-body"
                    name="body"
                    rows="1"
                    class="h-24 max-h-36 w-full bg-white border border-gray-200 px-4 -mb-1.5 rounded-xl text-xs text-[#191D38] font-medium outline-none pl-4 py-4 pr-12 py-0 overflow-hidden"
                    placeholder="Typ een bericht…"
                  ></textarea>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  // Scroll naar beneden na refresh van messages
  document.addEventListener('htmx:afterSwap', function (e) {
    var t = e.target;
    if (!t) return;
    if (t.id === 'task-chat-messages') {
      t.scrollTop = t.scrollHeight;
    }
  });

  // Reset composer na succesvol versturen
  document.addEventListener('htmx:afterRequest', function (e) {
    var el = e.target;
    if (!el) return;

    if (el.id === 'task-chat-form' && e.detail && e.detail.successful) {
      el.reset();
      var file = el.querySelector('#chat-attachments');
      if (file) file.value = '';
      var body = el.querySelector('#chat-body');
      if (body) body.focus();
    }
  });

  // Enter = submit, Shift+Enter = newline
  document.addEventListener('keydown', function (e) {
    var ta = e.target;
    if (!ta || ta.id !== 'chat-body') return;

    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();

      var form = document.getElementById('task-chat-form');
      if (!form) return;

      var text = (ta.value || '').trim();
      var fileInput = document.getElementById('chat-attachments');
      var hasFiles = !!(fileInput && fileInput.files && fileInput.files.length);

      // voorkom lege berichten zonder bestanden
      if (!text && !hasFiles) return;

      // trigger HTMX submit
      if (window.htmx) {
        window.htmx.trigger(form, 'submit');
      } else {
        form.requestSubmit();
      }
    }
  });
</script>
@endsection
