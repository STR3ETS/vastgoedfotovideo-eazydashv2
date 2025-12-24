@extends('hub.layouts.app')

@section('content')
  @php
    use App\Models\User;
    use Illuminate\Support\Str;
    use Illuminate\Pagination\AbstractPaginator;
    use Illuminate\Support\Collection;

    // --------- Project collection (paginator-safe) ----------
    $projectsCollection = $projects ?? collect();

    $projectsItems = $projectsCollection instanceof AbstractPaginator
      ? $projectsCollection->getCollection()
      : ($projectsCollection instanceof Collection ? $projectsCollection : collect($projectsCollection));

    $firstProjectId = optional($projectsItems->first())->id ?? null;

    // --------- Medewerkers (zoals Potentiële klanten) ----------
    $assignees = User::query()
      ->whereNull('company_id')
      ->orderBy('name')
      ->get();

    $assigneesById = $assignees->mapWithKeys(function ($user) {
      $avatarKey = $user->memoji_key ?? Str::lower(Str::before($user->name, ' '));

      return [
        $user->id => [
          'name'   => $user->name,
          'avatar' => "/assets/eazyonline/memojis/{$avatarKey}.webp",
        ],
      ];
    })->all();

    // Project -> assignee_id map (voor directe UI rendering)
    $projectAssignees = $projectsItems->mapWithKeys(function ($p) {
      return [(int) $p->id => ($p->assignee_id ?? null)];
    })->all();

    // Labels (voor badges/filters)
    $labelsByValue = $statusByValue ?? [
      'preview'          => 'Preview',
      'waiting_customer' => 'Wachten op klant',
      'preview_approved' => 'goedgekeurd',
      'offerte'          => 'Offerte',
    ];

    // Filter UI (zelfde pill-style als PotK)
    $filterMeta = [
      'preview' => [
        'bg' => 'bg-[#e0d4ff]',
        'border' => 'border-[#4c2a9b]',
        'text' => 'text-[#4c2a9b]',
        'dot' => 'bg-[#4c2a9b]',
      ],
      'waiting_customer' => [
        'bg' => 'bg-[#b3e6ff]',
        'border' => 'border-[#0f6199]',
        'text' => 'text-[#0f6199]',
        'dot' => 'bg-[#0f6199]',
      ],
      'preview_approved' => [
        'bg' => 'bg-[#d1fae5]',
        'border' => 'border-[#10b981]',
        'text' => 'text-[#065f46]',
        'dot' => 'bg-[#10b981]',
      ],
      'offerte' => [
        'bg' => 'bg-[#ffdfb3]',
        'border' => 'border-[#a0570f]',
        'text' => 'text-[#a0570f]',
        'dot' => 'bg-[#a0570f]',
      ],
    ];
  @endphp

  <div
    class="col-span-5 grid grid-cols-4 w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0 overflow-hidden"
    x-data="projectsDnD({
      csrf: '{{ csrf_token() }}',
      updateUrlTemplate: '{{ route('support.projecten.status.update', ['project' => '__ID__']) }}',
      assigneeUpdateUrlTemplate: '{{ route('support.projecten.assignee.update', ['project' => '__ID__']) }}',
      assigneesById: @js($assigneesById),
      projectAssignees: @js($projectAssignees),
      labelsByValue: @js($labelsByValue),
      statusCounts: @js($statusCounts ?? []),
      statusCountTexts: @js([
        'singular' => __('projecten.status_counts.singular'),
        'plural'   => __('projecten.status_counts.plural'),
      ]),
      initialActiveId: @js($selectedProjectId ?? $firstProjectId), {{-- ✅ toevoegen --}}
    })"
    x-init="init()"
  >
    <div class="col-span-4 w-full h-full flex flex-col min-h-0">

      {{-- Statustegels (draggable) --}}
      @include('hub.projecten.partials.statuses', [
        'statusMap'    => $statusMap ?? [],
        'statusCounts' => $statusCounts ?? [],
      ])

      {{-- ✅ Assignee pills (zoals Potentiële klanten) --}}
      <div class="mt-3 flex gap-4 items-center">
        <div class="w-fit flex items-center gap-2 p-2 rounded-full bg-[#f3f8f8] overflow-x-auto">

          {{-- Niet toegewezen pill --}}
          <div
            class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab"
            draggable="true"
            @dragstart="onAssigneeDragStart(null, 'Niet toegewezen', null, $event)"
            @dragend="onAssigneeDragEnd()"
          >
            <i class="fa-solid fa-minus text-[10px] text-[#215558]/60"></i>
            <p class="text-xs font-semibold text-[#215558] ml-2 whitespace-nowrap">Niet toegewezen</p>
          </div>

          @foreach($assignees as $user)
            @php
              $avatar = $assigneesById[$user->id]['avatar'] ?? '/assets/eazyonline/memojis/default.webp';
            @endphp

            <div
              class="px-2 h-8 bg-white rounded-full border border-[#215558]/25 hover:border-[#0F9B9F] transition duration-300 flex items-center justify-center cursor-grab"
              draggable="true"
              @dragstart="onAssigneeDragStart({{ $user->id }}, @js($user->name), @js($avatar), $event)"
              @dragend="onAssigneeDragEnd()"
            >
              <img src="{{ $avatar }}" class="max-h-[80%]">
              <p class="text-xs font-semibold text-[#215558] ml-1 whitespace-nowrap">{{ $user->name }}</p>
            </div>
          @endforeach
        </div>
      </div>

      {{-- ✅ Scroll / content wrapper (zoals PotK) --}}
      <div class="mt-3 flex-1 min-h-0 overflow-hidden flex flex-col">

        {{-- Filters (zelfde styling als PotK) --}}
        <div class="flex flex-wrap items-center gap-2 mt-6">
          @foreach(['preview','waiting_customer','preview_approved','offerte'] as $s)
            @php
              $m = $filterMeta[$s] ?? null;
              $label = $labelsByValue[$s] ?? ucfirst(str_replace('_',' ', $s));
            @endphp

            <button
              type="button"
              class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
              :class="activeFilters.includes(@js($s))
                ? '{{ $m ? ($m['bg'].' '.$m['border'].' '.$m['text']) : 'bg-gray-100 border-gray-300 text-gray-700' }}'
                : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
              @click="toggleFilter(@js($s))"
            >
              <span class="w-1.5 h-1.5 rounded-full {{ $m['dot'] ?? 'bg-gray-400' }}"></span>
              <span>{{ $label }}</span>
            </button>
          @endforeach
        </div>

        {{-- MASTER / DETAIL --}}
        <div class="mt-6 grid grid-cols-4 gap-6 flex-1 min-h-0">
          {{-- LEFT LIST --}}
          <div class="space-y-2 bg-[#f3f8f8] rounded-4xl p-8 h-full min-h-0 overflow-y-auto">
            @forelse($projectsCollection as $project)
              @php
                $status = $project->status ?? 'preview';
                $statusLabel = $labelsByValue[$status] ?? ucfirst(str_replace('_',' ', $status));

                $badgeMap = [
                  'preview'          => 'bg-[#e0d4ff] text-[#4c2a9b]',
                  'waiting_customer' => 'bg-[#b3e6ff] text-[#0f6199]',
                  'preview_approved' => 'bg-[#d1fae5] text-[#065f46]',
                  'offerte'          => 'bg-[#ffdfb3] text-[#a0570f]',
                ];
                $badgeClass = $badgeMap[$status] ?? 'bg-gray-100 text-gray-600';
              @endphp

              <button
                type="button"
                data-card-id="{{ $project->id }}"
                data-status="{{ $status }}"
                data-assignee-id="{{ $project->assignee_id ?? '' }}"
                @dragover.prevent="onCardDragOver($event)"
                @dragleave="onCardDragLeave($event)"
                @drop="onCardDrop($event)"
                class="w-full text-left pl-5 pr-2 py-4 transition duration-300 border-l-4 rounded-tr-4xl rounded-br-4xl cursor-pointer"
                :class="activeId === {{ $project->id }}
                  ? 'bg-white border-l-[#0F9B9F]'
                  : 'bg-white border-l-[#215558]/20 hover:bg-gray-50'"
                @click="activeId = Number({{ (int) $project->id }})"
              >
                <div class="flex items-start justify-between gap-4">
                  <div class="w-full flex items-center justify-between">
                    <div class="w-full">
                      <p class="text-base font-bold text-[#215558] truncate">
                        {{ $project->company ?: __('projecten.unknown_company') }}
                      </p>
                      <span
                        class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold whitespace-nowrap {{ $badgeClass }}"
                        data-status-badge
                        data-status-value="{{ $status }}"
                      >
                        {{ $statusLabel }}
                      </span>
                    </div>
                  </div>
                </div>
              </button>
            @empty
              <div>
                <p class="text-xs font-semibold text-[#215558]/50">
                  Nog geen projecten gevonden.
                </p>
              </div>
            @endforelse

            {{-- Pagination (als je paginate) --}}
            @if(method_exists($projectsCollection, 'links'))
              <div class="pt-4">
                {{ $projectsCollection->links() }}
              </div>
            @endif
          </div>

          {{-- RIGHT DETAIL --}}
          <div class="col-span-3 min-h-0 bg-[#f3f8f8] rounded-4xl overflow-hidden flex flex-col">
            <div class="p-8 flex-1 min-h-0 overflow-y-auto">
              @forelse($projectsCollection as $project)
                <div x-show="activeId === {{ $project->id }}" x-cloak>
                  @include('hub.projecten.partials.card', [
                    'project' => $project,
                    'statusByValue' => $labelsByValue,
                    'isDetailView' => true,
                  ])
                </div>
              @empty
                <div class="flex items-center gap-4">
                  <span class="text-4xl">👈</span>
                  <p class="text-base font-bold text-[#215558]/80 mt-1">
                    Selecteer een project om te beginnen.
                  </p>
                </div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Toast container rechtsonder --}}
  <div id="toast-container" class="fixed bottom-6 right-6 z-[9999] space-y-2 pointer-events-none"></div>

  <script>
    window.PROJECT_STRINGS = {!! json_encode([
      'toast' => [
        'status_update_success'   => __('projecten.toast.status_update_success'),
        'status_update_error'     => __('projecten.toast.status_update_error'),
        'status_already'          => __('projecten.toast.status_already') ?? 'Status is al zo',

        'assignee_update_success' => __('projecten.toast.assignee_update_success') ?? 'Medewerker bijgewerkt',
        'assignee_update_error'   => __('projecten.toast.assignee_update_error') ?? 'Medewerker bijwerken mislukt',
        'assignee_already'        => __('projecten.toast.assignee_already') ?? 'Medewerker is al zo',
      ],
    ]) !!};
  </script>

  @verbatim
  <script>
    function showToast(message, type = 'success') {
      const container = document.getElementById('toast-container');
      if (!container) return;

      const el = document.createElement('div');
      el.className = [
        'pointer-events-auto','w-72','rounded-2xl','border','border-gray-200',
        'px-3','py-2.5','bg-white','shadow-lg','flex','items-start','gap-2',
        'transform-gpu','translate-y-3','opacity-0','transition-all','duration-300'
      ].join(' ');

      const isError = type === 'error';
      const iconHtml = isError
        ? `<div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
             <i class="fa-solid fa-xmark text-[11px] text-red-500 mb-0.5"></i>
           </div>`
        : `<div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
             <i class="fa-solid fa-check text-[11px] text-emerald-600 mb-0.5"></i>
           </div>`;

      el.innerHTML = `
        ${iconHtml}
        <div class="mt-0.5 text-xs font-semibold text-[#215558] leading-snug">${message}</div>
      `;

      container.appendChild(el);
      requestAnimationFrame(() => el.classList.remove('translate-y-3','opacity-0'));

      setTimeout(() => {
        el.classList.add('opacity-0','translate-y-3');
        el.addEventListener('transitionend', () => el.remove(), { once: true });
      }, 3000);
    }

    function projectTasks({ projectId, csrf, updateUrl, statusUpdateUrl, initial, initialStatus }) {
      return {
        projectId: String(projectId || ''),
        csrf,
        updateUrl,
        statusUpdateUrl,

        tasks: initial || {},
        currentStatus: String(initialStatus || 'preview').toLowerCase(),
        loadingTypes: {},

        init() {
          window.addEventListener('project-card-status-changed', (e) => {
            const d = e.detail || {};
            if (!d.id || String(d.id) !== this.projectId) return;
            if (d.newValue) this.currentStatus = String(d.newValue).toLowerCase();
          });
        },

        // ✅ Welke taken toon je in welke status
        isVisible(type) {
          // ✅ UI-only status actie: pas tonen als preview-taak klaar is
          if (type === 'status_to_waiting_customer') {
            return this.currentStatus === 'preview' && !!this.tasks.create_preview; // ✅ was send_preview
          }

          // ✅ Echte taken
          if (type === 'create_preview') {
            return this.currentStatus === 'preview';
          }

          // (optioneel) als je send_preview helemaal niet meer gebruikt:
          if (type === 'send_preview') return false;

          if (type === 'process_feedback') return this.currentStatus === 'waiting_customer';
          if (type === 'call_customer' || type === 'send_offerte') {
            return ['preview_approved','offerte'].includes(this.currentStatus);
          }

          // custom DB taken
          return true;
        },

        canCheck(type) {
          // UI-only types hebben geen checkbox logica
          if (type === 'status_to_waiting_customer' || type === 'status_to_offerte') return false;

          // afvinken alleen in juiste fase (custom taken: true)
          if (type === 'create_preview' || type === 'send_preview') return this.currentStatus === 'preview';
          if (type === 'process_feedback') return this.currentStatus === 'waiting_customer';
          if (type === 'call_customer' || type === 'send_offerte') {
            return ['preview_approved','offerte'].includes(this.currentStatus);
          }

          return true;
        },

        async toggle(type, checked, evt) {
          if (this.loadingTypes[type]) return;

          if (!this.canCheck(type)) {
            if (evt?.target) evt.target.checked = !!this.tasks[type];
            return;
          }

          const prev = !!this.tasks[type];
          this.tasks[type] = checked;
          this.loadingTypes[type] = true;

          const newStatus = checked ? 'done' : 'open';

          try {
            const res = await fetch(this.updateUrl, {
              method: 'PATCH',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrf,
                'X-Requested-With': 'XMLHttpRequest',
              },
              credentials: 'same-origin',
              body: JSON.stringify({ type, status: newStatus }),
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data?.message || 'Taak update mislukt');

            const serverStatus = String(data?.status || newStatus).toLowerCase();
            this.tasks[type] = serverStatus === 'done';

            showToast(
              this.tasks[type]
                ? 'Gelukt! De taak is gemarkeerd als voltooid'
                : 'Gelukt! De taak is opnieuw geopend',
              'success'
            );
          } catch (e) {
            this.tasks[type] = prev;
            if (evt?.target) evt.target.checked = prev;
            showToast('Oeps! De taak kon niet worden bijgewerkt.', 'error');
          } finally {
            this.loadingTypes[type] = false;
          }
        },

        async setStatus(newStatus) {
          if (!this.statusUpdateUrl) {
            showToast('Status URL ontbreekt in tasksPayload.', 'error');
            return;
          }

          try {
            const res = await fetch(this.statusUpdateUrl, {
              method: 'PATCH',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrf,
                'X-Requested-With': 'XMLHttpRequest',
              },
              credentials: 'same-origin',
              body: JSON.stringify({ status: newStatus }),
            });

            const data = await res.json().catch(() => null);
            if (!res.ok) {
              showToast(data?.message || 'Status kon niet worden bijgewerkt.', 'error');
              return;
            }

            const oldValue = this.currentStatus;
            const finalValue = String((data && data.status) || newStatus).toLowerCase();
            this.currentStatus = finalValue;

            // 1) update alles (counts + badges + filters) via projectsDnD listener
            window.dispatchEvent(new CustomEvent('project-card-status-changed', {
              detail: { id: this.projectId, oldValue, newValue: finalValue, data: data || {} }
            }));

            // 2) update de detail-card Alpine state (statusValue etc.) zonder reload
            this.$root.dispatchEvent(new CustomEvent('project-status-updated', {
              detail: { status: finalValue, label: data?.label || finalValue },
              bubbles: true
            }));

            showToast(`Gelukt! Status aangepast naar ${data?.label || finalValue}.`, 'success');
          } catch (e) {
            console.error(e);
            showToast('Status kon niet worden bijgewerkt.', 'error');
          }
        },
      };
    }

    function projTrans(path, replacements) {
      const parts = path.split('.');
      let value = window.PROJECT_STRINGS || {};
      for (const p of parts) value = value && value[p];
      if (typeof value !== 'string') return path;

      replacements = replacements || {};
      return Object.keys(replacements).reduce((str, key) => {
        const re = new RegExp(':' + key, 'g');
        return str.replace(re, replacements[key]);
      }, value);
    }

    let __transparentDragImg = null;
    function getTransparentDragImage() {
      if (__transparentDragImg) return __transparentDragImg;
      const canvas = document.createElement('canvas');
      canvas.width = canvas.height = 1;
      const img = new Image();
      img.src = canvas.toDataURL();
      __transparentDragImg = img;
      return img;
    }

    function projectsDnD({
      csrf,
      updateUrlTemplate,
      assigneeUpdateUrlTemplate,
      assigneesById,
      projectAssignees,
      labelsByValue,
      statusCounts,
      statusCountTexts,
      initialActiveId
    }) {
      return {
        activeId: (initialActiveId !== null && initialActiveId !== undefined && initialActiveId !== '')
          ? Number(initialActiveId)
          : null,

        draggingValue: null,
        draggingLabel: null,

        statusCounts: statusCounts || {},
        statusCountTexts: statusCountTexts || { singular: ':count project', plural: ':count projecten' },

        // filters
        activeFilters: [],

        // assignee data
        assigneesById: assigneesById || {},
        projectAssignees: projectAssignees || {},

        // assignee drag state (zoals PotK)
        draggingAssigneeSet: false,
        draggingAssigneeId: null,
        draggingAssigneeName: null,
        draggingAssigneeAvatar: null,

        onAssigneeDragStart(id, name, avatar, event) {
          this.draggingAssigneeSet = true;
          this.draggingAssigneeId = (id === null || id === undefined || id === '') ? null : id;
          this.draggingAssigneeName = name || null;
          this.draggingAssigneeAvatar = avatar || null;

          if (event && event.dataTransfer) {
            event.dataTransfer.setDragImage(getTransparentDragImage(), 0, 0);
          }
        },

        onAssigneeDragEnd() {
          this.draggingAssigneeSet = false;
          this.draggingAssigneeId = null;
          this.draggingAssigneeName = null;
          this.draggingAssigneeAvatar = null;
        },

        // drag ghost
        dragGhost: null,
        dragGhostOffsetX: 0,
        dragGhostOffsetY: 0,
        lastGhostX: null,

        init() {
          window.addEventListener('dragover', (e) => {
            if (!this.dragGhost) return;
            this._updateGhostPosition(e.clientX, e.clientY);
          });

          // default: alles zichtbaar
          this.filterCards();

          window.addEventListener('project-card-status-changed', (e) => {
            const d = e.detail || {};
            if (!d.id || !d.newValue) return;

            const id = String(d.id);
            const newValue = String(d.newValue).toLowerCase();

            // oldValue meegekregen? top. Anders fallback: lees uit DOM.
            let oldValue = d.oldValue ? String(d.oldValue).toLowerCase() : null;
            if (!oldValue) {
              const el = document.querySelector(`[data-card-id="${id}"]`);
              oldValue = el?.dataset?.status ? String(el.dataset.status).toLowerCase() : null;
            }

            if (oldValue && oldValue !== newValue) {
              this._updateCounts(oldValue, newValue);
            }

            this._updateAllInstances(id, newValue);
          });
        },

        toggleFilter(status) {
          if (this.activeFilters.includes(status)) {
            this.activeFilters = this.activeFilters.filter(s => s !== status);
          } else {
            this.activeFilters.push(status);
          }
          this.filterCards();
        },

        filterCards() {
          const cards = this.$el.querySelectorAll('[data-card-id]');

          // show/hide
          if (!this.activeFilters.length) {
            cards.forEach(c => c.classList.remove('hidden'));
          } else {
            cards.forEach(card => {
              const s = card.dataset.status;
              card.classList.toggle('hidden', !this.activeFilters.includes(s));
            });
          }

          // ✅ als huidige selectie niet (meer) zichtbaar is: pak eerste zichtbare
          const activeEl = this.activeId
            ? this.$el.querySelector(`[data-card-id="${this.activeId}"]:not(.hidden)`)
            : null;

          if (!activeEl) {
            const firstVisible = Array.from(cards).find(c => !c.classList.contains('hidden'));
            this.activeId = firstVisible ? Number(firstVisible.dataset.cardId) : null;
          }
        },

        humanize(value) {
          if (!value) return '';
          return value.toString()
            .replace(/_/g, ' ')
            .trim()
            .replace(/\s+/g, ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
        },

        _getLabelFor(value) {
          if (labelsByValue && Object.prototype.hasOwnProperty.call(labelsByValue, value)) {
            return labelsByValue[value];
          }
          if (this.draggingValue === value && this.draggingLabel) {
            return this.draggingLabel;
          }
          return this.humanize(value);
        },

        formatStatusCount(value) {
          const count = this.statusCounts[value] || 0;
          const tpl = count === 1 ? this.statusCountTexts.singular : this.statusCountTexts.plural;
          return tpl.replace(':count', count);
        },

        onStatusDragStart(value, label, el, event) {
          this.draggingValue = value;
          this.draggingLabel = label;

          if (event && event.dataTransfer) {
            event.dataTransfer.setDragImage(getTransparentDragImage(), 0, 0);
          }

          const rect = el.getBoundingClientRect();
          const ghost = el.cloneNode(true);
          ghost.id = '';
          ghost.classList.add('pointer-events-none','fixed','z-[9998]','shadow-xl','rounded-xl');
          ghost.style.width = rect.width + 'px';

          document.body.appendChild(ghost);
          this.dragGhost = ghost;

          this.dragGhostOffsetX = rect.width / 2;
          this.dragGhostOffsetY = rect.height / 2;
          this.lastGhostX = event?.clientX ?? null;

          this._updateGhostPosition(event.clientX, event.clientY, true);
        },

        onStatusDragEnd() {
          if (this.dragGhost) this.dragGhost.remove();
          this.dragGhost = null;
          this.lastGhostX = null;

          this.draggingValue = null;
          this.draggingLabel = null;
        },

        _updateGhostPosition(clientX, clientY, initial = false) {
          if (!this.dragGhost || clientX == null || clientY == null) return;

          if (initial || this.lastGhostX == null) this.lastGhostX = clientX;

          const x = clientX - this.dragGhostOffsetX;
          const y = clientY - this.dragGhostOffsetY;

          const dx = clientX - this.lastGhostX;
          const angle = Math.max(-10, Math.min(10, dx / 2));

          this.dragGhost.style.transform = `translate(${x}px, ${y}px) rotate(${angle}deg)`;
          this.lastGhostX = this.lastGhostX + dx * 0.2;
        },

        onCardDragOver(e) {
          e.preventDefault();
          const card = e.target.closest('[data-card-id]') || e.currentTarget;
          card.classList.add('border-[#0F9B9F]');
          card.classList.remove('border-gray-200');
        },

        onCardDragLeave(e) {
          const card = e.target.closest('[data-card-id]') || e.currentTarget;
          card.classList.remove('border-[#0F9B9F]');
          card.classList.add('border-gray-200');
        },

        _updateCounts(oldValue, newValue) {
          if (!oldValue || oldValue === newValue) return;
          if (this.statusCounts[oldValue] == null) this.statusCounts[oldValue] = 0;
          if (this.statusCounts[newValue] == null) this.statusCounts[newValue] = 0;

          this.statusCounts[oldValue] = Math.max(0, (this.statusCounts[oldValue] || 0) - 1);
          this.statusCounts[newValue] = (this.statusCounts[newValue] || 0) + 1;
        },

        _updateBadge(cardEl, newValue) {
          cardEl.dataset.status = newValue;

          const badge = cardEl.querySelector('[data-status-badge]');
          if (!badge) return;

          const label = this._getLabelFor(newValue);
          badge.dataset.statusValue = newValue;
          badge.textContent = label;

          const colorMap = {
            preview: ['bg-[#e0d4ff]', 'text-[#4c2a9b]'],
            waiting_customer: ['bg-[#b3e6ff]', 'text-[#0f6199]'],
            preview_approved: ['bg-[#d1fae5]', 'text-[#065f46]'],
            offerte: ['bg-[#ffdfb3]', 'text-[#a0570f]'],
          };

          const allColorClasses = [
            'bg-[#e0d4ff]','text-[#4c2a9b]',
            'bg-[#b3e6ff]','text-[#0f6199]',
            'bg-[#d1fae5]','text-[#065f46]',
            'bg-[#ffdfb3]','text-[#a0570f]',
            'bg-slate-100','text-slate-700'
          ];

          badge.classList.remove(...allColorClasses);
          const classes = colorMap[newValue] || ['bg-gray-100','text-gray-600'];
          badge.classList.add(...classes);

          badge.classList.add('bg-[#0F9B9F]/20');
          setTimeout(() => badge.classList.remove('bg-[#0F9B9F]/20'), 250);
        },

        _updateAllInstances(id, newValue) {
          const els = document.querySelectorAll(`[data-card-id="${id}"]`);
          if (!els.length) return;

          els.forEach(el => this._updateBadge(el, newValue));

          // filters opnieuw toepassen
          this.filterCards();
        },

        async onCardDrop(e) {
          e.preventDefault();

          const card = e.target.closest('[data-card-id]') || e.currentTarget;
          card.classList.remove('border-[#0F9B9F]');
          card.classList.add('border-gray-200');

          const id = card.dataset.cardId;

          // ✅ 1) ASSIGNEE DROP heeft voorrang (zoals PotK)
          if (this.draggingAssigneeSet) {
            const current = this.projectAssignees[id] ?? null;
            const next = this.draggingAssigneeId ?? null;

            if ((current ?? null) === (next ?? null)) {
              showToast(projTrans('toast.assignee_already'), 'error');
              this.onAssigneeDragEnd();
              return;
            }

            const url = assigneeUpdateUrlTemplate.replace('__ID__', id);

            try {
              const res = await fetch(url, {
                method: 'PATCH',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': csrf,
                  'X-Requested-With': 'XMLHttpRequest',
                  'Accept': 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ assignee_id: next })
              });

              const data = await res.json().catch(() => null);
              if (!res.ok) {
                showToast((data && data.message) || projTrans('toast.assignee_update_error'), 'error');
                return;
              }

              const finalId = (data && (data.assignee_id ?? data.assigneeId)) ?? next;

              this.projectAssignees[id] = finalId;
              card.dataset.assigneeId = finalId || '';

              showToast(projTrans('toast.assignee_update_success'), 'success');
            } catch (err) {
              console.error(err);
              showToast(projTrans('toast.assignee_update_error'), 'error');
            } finally {
              this.onAssigneeDragEnd();
            }

            return; // ⛔️ stop status-logica
          }

          // ✅ 2) STATUS DROP
          if (!this.draggingValue) return;

          const url = updateUrlTemplate.replace('__ID__', id);

          const badge = card.querySelector('[data-status-badge]');
          const oldValue =
            (badge && badge.dataset.statusValue) ? badge.dataset.statusValue : (card.dataset.status || null);

          const newValue = this.draggingValue;

          if (oldValue === newValue) {
            showToast(projTrans('toast.status_already'), 'error');
            this.onStatusDragEnd();
            return;
          }

          try {
            const res = await fetch(url, {
              method: 'PATCH',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
              },
              credentials: 'same-origin',
              body: JSON.stringify({ status: newValue })
            });

            const data = await res.json().catch(() => null);
            if (!res.ok) {
              showToast((data && data.message) || projTrans('toast.status_update_error'), 'error');
              return;
            }

            const finalValue = (data && data.status) || newValue;

            this._updateCounts(oldValue, finalValue);
            this._updateAllInstances(id, finalValue);

            showToast(projTrans('toast.status_update_success'), 'success');
          } catch (err) {
            console.error(err);
            showToast(projTrans('toast.status_update_error'), 'error');
          } finally {
            this.onStatusDragEnd();
          }
        },
      };
    }
  </script>
  @endverbatim
@endsection