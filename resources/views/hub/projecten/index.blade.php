@extends('hub.layouts.app')

@section('content')
  <div class="col-span-1 w-full p-4 h-full bg-white rounded-xl"></div>

  <div class="col-span-4 grid grid-cols-4 gap-4 bg-white rounded-xl p-4 min-h-0 overflow-hidden"
      x-data="projectsDnD({
          csrf: '{{ csrf_token() }}',
          updateUrlTemplate: '{{ route('support.projecten.status.update', ['project' => '__ID__']) }}',
          labelsByValue: @js($statusByValue ?? []),
          statusCounts: @js($statusCounts ?? []),
          statusCountTexts: @js([
              'singular' => __('projecten.status_counts.singular'),
              'plural'   => __('projecten.status_counts.plural'),
          ]),
      })"
      x-init="init()">

    {{-- Linkerkolom: statussen --}}
    <div class="col-span-1 w-full h-full">
      <h3 class="text-lg text-[#215558] font-black leading-tight truncate opacity-50">
        {{ __('projecten.statuses_title') }}
      </h3>

      @include('hub.projecten.partials.statuses', [
          'statusMap'    => $statusMap ?? [],
          'statusCounts' => $statusCounts ?? [],
      ])
    </div>

    {{-- Rechterkolom: lijst projecten --}}
    <div class="col-span-3 w-full h-full flex flex-col min-h-0">
      <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg text-[#215558] font-black leading-tight truncate opacity-50">
          {{ __('projecten.page_title') }}
        </h3>
      </div>

      <div class="mt-3 flex-1 overflow-y-auto min-h-0 pr-2">
        @include('hub.projecten.partials.list-cards', [
          'projects'      => $projects ?? collect(),
          'statusByValue' => $statusByValue ?? [],
        ])

        @if(method_exists($projects, 'links'))
          <div class="mt-3 pb-2">
            {{ $projects->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Toast container rechtsonder (zelfde als bij aanvragen) --}}
  <div id="toast-container"
       class="fixed bottom-6 right-6 z-[9999] space-y-2 pointer-events-none">
  </div>

  {{-- Vertaalstrings voor projecten-JS --}}
  <script>
    window.PROJECT_STRINGS = {!! json_encode([
        'toast' => [
            'status_update_success' => __('projecten.toast.status_update_success'),
            'status_update_error'   => __('projecten.toast.status_update_error'),
        ],
    ]) !!};
  </script>

  @verbatim
  <script>
  /**
   * Eenvoudige toast (zelfde als bij potentiële klanten)
   */
  function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const el = document.createElement('div');
    el.className = [
      'pointer-events-auto',
      'w-72',
      'rounded-2xl',
      'border',
      'border-gray-200',
      'px-3',
      'py-2.5',
      'bg-white',
      'shadow-lg',
      'flex',
      'items-start',
      'gap-2',
      'transform-gpu',
      'translate-y-3',
      'opacity-0',
      'transition-all',
      'duration-300'
    ].join(' ');

    const isError = type === 'error';

    const iconHtml = isError
      ? `
        <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
          <i class="fa-solid fa-xmark text-[11px] text-red-500 mb-0.5"></i>
        </div>
      `
      : `
        <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
          <i class="fa-solid fa-check text-[11px] text-emerald-600 mb-0.5"></i>
        </div>
      `;

    el.innerHTML = `
      ${iconHtml}
      <div class="mt-0.5 text-xs font-semibold text-[#215558] leading-snug">
        ${message}
      </div>
    `;

    container.appendChild(el);

    requestAnimationFrame(() => {
      el.classList.remove('translate-y-3', 'opacity-0');
    });

    setTimeout(() => {
      el.classList.add('opacity-0', 'translate-y-3');
      el.addEventListener('transitionend', () => {
        el.remove();
      }, { once: true });
    }, 3000);
  }

  function projTrans(path, replacements) {
    const parts = path.split('.');
    let value = window.PROJECT_STRINGS || {};
    for (const p of parts) {
      value = value && value[p];
    }
    if (typeof value !== 'string') return path;

    replacements = replacements || {};
    return Object.keys(replacements).reduce((str, key) => {
      const re = new RegExp(':' + key, 'g');
      return str.replace(re, replacements[key]);
    }, value);
  }

  // Transparante drag image
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

  function callLog({ csrf, storeUrl, initialCalls }) {
    return {
      csrf,
      storeUrl,
      calls: initialCalls || [],
      openCallPanel: false,
      outcome: '',
      note: '',
      loading: false,
      labels: {
        geen_antwoord: 'Gebeld: Geen antwoord',
        gesproken: 'Gebeld: Gesproken',
      },

      async submit() {
        if (!this.outcome) return;

        this.loading = true;

        try {
          const res = await fetch(this.storeUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': this.csrf,
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
              outcome: this.outcome,
              note: this.note || '',
            }),
          });

          if (!res.ok) throw new Error('Failed');

          const data = await res.json().catch(() => null);
          if (!data || !data.success) throw new Error('Invalid response');

          this.calls.unshift({
            id: data.id,
            called_at: data.called_at,
            outcome: data.outcome,
            note: data.note,
            user_name: data.user_name,
          });

          this.outcome = '';
          this.note = '';
          this.openCallPanel = false;

          if (window.showToast) {
            window.showToast('Belmoment opgeslagen.', 'success');
          }
        } catch (e) {
          if (window.showToast) {
            window.showToast('Opslaan mislukt.', 'error');
          } else {
            alert('Opslaan mislukt.');
          }
        } finally {
          this.loading = false;
        }
      },
    };
  }

  function projectsDnD({ csrf, updateUrlTemplate, labelsByValue, statusCounts, statusCountTexts }) {
    return {
      draggingValue: null,
      draggingLabel: null,
      statusCounts: statusCounts || {},
      statusCountTexts: statusCountTexts || {
        singular: ':count project',
        plural: ':count projecten',
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
      },

      humanize(value) {
        if (!value) return '';
        return value.toString()
          .replace(/_/g, ' ')
          .trim()
          .replace(/\s+/g, ' ')
          .replace(/\b\w/g, c => c.toUpperCase());
      },

      formatStatusCount(value) {
        const count = this.statusCounts[value] || 0;
        const tpl = count === 1
          ? this.statusCountTexts.singular
          : this.statusCountTexts.plural;

        return tpl.replace(':count', count);
      },

      // DRAG FROM STATUS
      onStatusDragStart(value, label, el, event) {
        this.draggingValue = value;
        this.draggingLabel = label;

        if (event && event.dataTransfer) {
          event.dataTransfer.setDragImage(getTransparentDragImage(), 0, 0);
        }

        const rect = el.getBoundingClientRect();
        const ghost = el.cloneNode(true);
        ghost.id = '';
        ghost.classList.add(
          'pointer-events-none',
          'fixed',
          'z-[9998]',
          'shadow-xl',
          'rounded-xl'
        );
        ghost.style.width = rect.width + 'px';

        document.body.appendChild(ghost);
        this.dragGhost = ghost;

        this.dragGhostOffsetX = rect.width / 2;
        this.dragGhostOffsetY = rect.height / 2;
        this.lastGhostX = event?.clientX ?? null;

        this._updateGhostPosition(event.clientX, event.clientY, true);
      },

      onStatusDragEnd(el) {
        if (this.dragGhost) this.dragGhost.remove();
        this.dragGhost = null;
        this.lastGhostX = null;

        this.draggingValue = null;
        this.draggingLabel = null;
      },

      _updateGhostPosition(clientX, clientY, initial = false) {
        if (!this.dragGhost || clientX == null || clientY == null) return;

        if (initial || this.lastGhostX == null) {
          this.lastGhostX = clientX;
        }

        const x = clientX - this.dragGhostOffsetX;
        const y = clientY - this.dragGhostOffsetY;

        const dx = clientX - this.lastGhostX;
        const angle = Math.max(-10, Math.min(10, dx / 2));

        this.dragGhost.style.transform =
          `translate(${x}px, ${y}px) rotate(${angle}deg)`;

        this.lastGhostX = this.lastGhostX + dx * 0.2;
      },

      // CARD HOVER / DROP
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

      _getLabelFor(value) {
        if (labelsByValue && Object.prototype.hasOwnProperty.call(labelsByValue, value)) {
          return labelsByValue[value];
        }
        if (this.draggingValue === value && this.draggingLabel) {
          return this.draggingLabel;
        }
        return this.humanize(value);
      },

      _updateBadge(cardEl, newValue, extra = {}) {
        const badge = cardEl.querySelector('[data-status-badge]');
        if (!badge) return;

        cardEl.dataset.status = newValue;

        const label = this._getLabelFor(newValue);
        badge.dataset.statusValue = newValue;
        badge.textContent = label;

        const colorMap = {
          preview: ['bg-[#e0d4ff]', 'text-[#4c2a9b]'],
          waiting_customer: ['bg-[#b3e6ff]', 'text-[#0f6199]'],
          offerte: ['bg-[#ffdfb3]', 'text-[#a0570f]'],
        };

        const allColorClasses = [
          'bg-[#e0d4ff]', 'text-[#4c2a9b]',
          'bg-[#b3e6ff]', 'text-[#0f6199]',
          'bg-[#ffdfb3]', 'text-[#a0570f]',
          'bg-slate-100', 'text-slate-700',
        ];

        badge.classList.remove(...allColorClasses);

        const classes = colorMap[newValue] || ['bg-slate-100', 'text-slate-700'];
        badge.classList.add(...classes);

        badge.classList.add('bg-[#0F9B9F]/20');
        setTimeout(() => badge.classList.remove('bg-[#0F9B9F]/20'), 250);

        cardEl.dispatchEvent(new CustomEvent('project-status-updated', {
          detail: Object.assign({ status: newValue }, extra),
        }));
      },

      _updateCounts(oldValue, newValue) {
        if (!oldValue || oldValue === newValue) return;
        if (this.statusCounts[oldValue] == null) this.statusCounts[oldValue] = 0;
        if (this.statusCounts[newValue] == null) this.statusCounts[newValue] = 0;

        this.statusCounts[oldValue] = Math.max(0, (this.statusCounts[oldValue] || 0) - 1);
        this.statusCounts[newValue] = (this.statusCounts[newValue] || 0) + 1;
      },

      async onCardDrop(e) {
        e.preventDefault();
        const card = e.target.closest('[data-card-id]') || e.currentTarget;
        card.classList.remove('border-[#0F9B9F]');
        card.classList.add('border-gray-200');

        if (!this.draggingValue) return;

        const id  = card.dataset.cardId;
        const url = updateUrlTemplate.replace('__ID__', id);

        const badge    = card.querySelector('[data-status-badge]');
        const oldValue = badge ? badge.dataset.statusValue : null;
        const newValue = this.draggingValue;

        // niets te doen als status hetzelfde is
        if (oldValue === newValue) {
          this.draggingValue = null;
          this.draggingLabel = null;
          if (this.dragGhost) this.dragGhost.remove();
          this.dragGhost = null;
          this.lastGhostX = null;
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
            showToast(
              (data && data.message) || projTrans('toast.status_update_error'),
              'error'
            );
            return;
          }

          const finalValue = (data && data.status) || newValue;

          this._updateCounts(oldValue, finalValue);
          this._updateBadge(card, finalValue, {
            offerteTask: data && data.offerte_task ? data.offerte_task : null,
          });

          showToast(
            projTrans('toast.status_update_success'),
            'success'
          );
        } catch (err) {
          console.error(err);
          showToast(projTrans('toast.status_update_error'), 'error');
        } finally {
          if (this.dragGhost) this.dragGhost.remove();
          this.dragGhost = null;
          this.lastGhostX = null;

          this.draggingValue = null;
          this.draggingLabel = null;
        }
      },
    };
  }
  </script>
  @endverbatim
@endsection
