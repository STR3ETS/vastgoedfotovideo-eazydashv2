@extends('hub.layouts.app')

@section('content')
  <div class="col-span-1 w-full p-4 h-full bg-white rounded-xl"></div>

  <div class="col-span-4 grid grid-cols-4 gap-4 bg-white rounded-xl p-4"
      x-data="statusDnD({
          csrf: '{{ csrf_token() }}',
          updateUrlTemplate: '{{ route('support.potentiele-klanten.status.update', ['aanvraag' => '__ID__']) }}',
          labelsByValue: @js($statusByValue ?? []),
          statusCounts: @js($statusCounts ?? []),
      })"
      x-init="init()">

    <div class="col-span-1 w-full h-full">
      <h3 class="text-lg text-[#215558] font-black leading-tight truncate opacity-50">Statussen</h3>
      @include('hub.potentiele-klanten.partials.statuses', [
          'statusMap'    => $statusMap ?? [],
          'statusCounts' => $statusCounts ?? [],
      ])
    </div>

    <div class="col-span-3 w-full h-full flex flex-col min-h-0">
      {{-- Titel + filterbuttons --}}
      <div class="flex items-center justify-between gap-3">
        <h3 class="text-lg text-[#215558] font-black leading-tight truncate opacity-50">
          Potentiële klanten
        </h3>
      </div>

      {{-- Alleen deze inhoud krijgt een eigen scrollbar --}}
      <div class="mt-3 flex-1 overflow-y-auto min-h-0 pr-2">
        {{-- Status-filters --}}
        <div class="flex flex-wrap items-center gap-2">
          {{-- Prospect --}}
          <button type="button"
                  class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                  :class="activeFilters.includes('prospect')
                    ? 'bg-[#b3e6ff] border-[#0f6199] text-[#0f6199]'
                    : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                  @click="toggleFilter('prospect')">
            <span class="w-1.5 h-1.5 rounded-full bg-[#0f6199]"></span>
            <span>Prospect</span>
          </button>

          {{-- Contact --}}
          <button type="button"
                  class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                  :class="activeFilters.includes('contact')
                    ? 'bg-[#C2F0D5] border-[#20603a] text-[#20603a]'
                    : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                  @click="toggleFilter('contact')">
            <span class="w-1.5 h-1.5 rounded-full bg-[#20603a]"></span>
            <span>Contact</span>
          </button>

          {{-- Intake --}}
          <button type="button"
                  class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                  :class="activeFilters.includes('intake')
                    ? 'bg-[#ffdfb3] border-[#a0570f] text-[#a0570f]'
                    : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                  @click="toggleFilter('intake')">
            <span class="w-1.5 h-1.5 rounded-full bg-[#a0570f]"></span>
            <span>Intake</span>
          </button>

          {{-- Lead --}}
          <button type="button"
                  class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                  :class="activeFilters.includes('lead')
                    ? 'bg-[#e0d4ff] border-[#4c2a9b] text-[#4c2a9b]'
                    : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                  @click="toggleFilter('lead')">
            <span class="w-1.5 h-1.5 rounded-full bg-[#4c2a9b]"></span>
            <span>Lead</span>
          </button>

          {{-- Dead --}}
          <button type="button"
                  class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer"
                  :class="activeFilters.includes('dead')
                    ? 'bg-[#ffb3b3] border-[#8a2a2d] text-[#8a2a2d]'
                    : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'"
                  @click="toggleFilter('dead')">
            <span class="w-1.5 h-1.5 rounded-full bg-[#8a2a2d]"></span>
            <span>Dead</span>
          </button>
        </div>

        @include('hub.potentiele-klanten.partials.list-cards', [
          'aanvragen'      => $aanvragen ?? collect(),
          'statusByValue'  => $statusByValue ?? [],
        ])

        @if(method_exists($aanvragen, 'links'))
          <div class="mt-3 pb-2">
            {{ $aanvragen->links() }}
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Toast container rechtsonder --}}
  <div id="toast-container"
       class="fixed bottom-6 right-6 z-[9999] space-y-2 pointer-events-none">
  </div>

@verbatim
<script>
/**
 * Eenvoudige toast rechtsonder
 * type: 'success' | 'error'
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

// transparante drag image
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

function statusDnD({ csrf, updateUrlTemplate, labelsByValue, statusCounts }) {
  return {
    draggingValue: null,
    draggingLabel: null,
    statusCounts: statusCounts || {},

    // filters
    activeFilters: [],
    hasVisibleCards: true,

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

      // direct bij load cards filteren (default: alles zichtbaar)
      this.filterCards();
    },

    humanize(value) {
      if (!value) return '';
      return value.toString()
        .replace(/_/g, ' ')
        .trim()
        .replace(/\s+/g, ' ')
        .replace(/\b\w/g, c => c.toUpperCase());
    },

    // FILTERS
    toggleFilter(status) {
      if (this.activeFilters.includes(status)) {
        this.activeFilters = this.activeFilters.filter(s => s !== status);
      } else {
        this.activeFilters.push(status);
      }
      this.filterCards();
    },

    filterCards() {
      const cards = document.querySelectorAll('[data-card-id]');
      let visible = 0;

      if (!this.activeFilters.length) {
        cards.forEach(card => {
          card.classList.remove('hidden');
          visible++;
        });
        this.hasVisibleCards = visible > 0;
        return;
      }

      cards.forEach(card => {
        const status = card.dataset.status;
        const show = this.activeFilters.includes(status);
        card.classList.toggle('hidden', !show);
        if (show) visible++;
      });

      this.hasVisibleCards = visible > 0;
    },

    // DRAG
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

    _updateBadge(cardEl, newValue) {
      const badge = cardEl.querySelector('[data-status-badge]');
      if (!badge) return;

      // 👉 HIER: data-status van de card updaten
      cardEl.dataset.status = newValue;

      const label = this._getLabelFor(newValue);
      badge.dataset.statusValue = newValue;
      badge.textContent = label;

      const colorMap = {
        prospect: ['bg-[#b3e6ff]',   'text-[#0f6199]'],
        contact:  ['bg-[#C2F0D5]',   'text-[#20603a]'],
        intake:   ['bg-[#ffdfb3]',   'text-[#a0570f]'],
        dead:     ['bg-[#ffb3b3]',   'text-[#8a2a2d]'],
        lead:     ['bg-[#e0d4ff]',   'text-[#4c2a9b]'],
      };

      const allColorClasses = [
        'bg-[#b3e6ff]','text-[#0f6199]',
        'bg-[#C2F0D5]','text-[#20603a]',
        'bg-[#ffdfb3]','text-[#a0570f]',
        'bg-[#ffb3b3]','text-[#8a2a2d]',
        'bg-[#e0d4ff]','text-[#4c2a9b]',
      ];

      badge.classList.remove(...allColorClasses);

      const classes = colorMap[newValue] || ['bg-slate-200', 'text-slate-600'];
      badge.classList.add(...classes);

      badge.classList.add('bg-[#0F9B9F]/20');
      setTimeout(() => badge.classList.remove('bg-[#0F9B9F]/20'), 250);
    },

    _updateCounts(oldValue, newValue) {
      if (!oldValue || oldValue === newValue) return;
      if (this.statusCounts[oldValue] == null) this.statusCounts[oldValue] = 0;
      if (this.statusCounts[newValue] == null) this.statusCounts[newValue] = 0;

      this.statusCounts[oldValue] = Math.max(0, (this.statusCounts[oldValue] || 0) - 1);
      this.statusCounts[newValue] = (this.statusCounts[newValue] || 0) + 1;
    },

    _appendStatusLog(cardEl, log) {
      if (!log || !log.html) return;

      const list  = cardEl.querySelector('[data-status-log-list]');
      const empty = cardEl.querySelector('[data-status-log-empty]');
      if (!list) return;

      if (empty) {
        empty.style.display = 'none';
      }

      const wrapper = document.createElement('div');
      wrapper.innerHTML = log.html.trim();
      const li = wrapper.firstElementChild;
      if (!li) return;

      list.prepend(li);
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

      if (oldValue === newValue) {
        const label = this._getLabelFor(newValue);
        showToast(`Mislukt! Deze aanvraag heeft al de status ${label}.`, 'error');
        this.draggingValue = null;
        this.draggingLabel = null;
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
          body: JSON.stringify({ status: newValue })
        });

        const data = await res.json().catch(() => null);
        if (!res.ok) throw new Error('Status update mislukt');

        const finalValue = (data && data.status) || newValue;

        this._updateCounts(oldValue, finalValue);
        this._updateBadge(card, finalValue);

        // ⬅️ statuslogboek direct bijwerken als backend een log terugstuurt
        if (data && data.log) {
          this._appendStatusLog(card, data.log);
        }

        const label = this._getLabelFor(finalValue);
        showToast(`Gelukt! Status van de aanvraag succesvol gewijzigd naar ${label}.`, 'success');
      } catch (err) {
        console.error(err);
        showToast('Kon status niet bijwerken.', 'error');
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

function taskAnswers({ csrf, updateUrlTemplate }) {
  return {
    async saveAnswer(id, value) {
      try {
        const url = updateUrlTemplate.replace('__ID__', id);

        const res = await fetch(url, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ answer: value }),
        });

        if (!res.ok) {
          console.error('Answer save failed', res.status);
          showToast('Kon antwoord niet opslaan.', 'error');
        } else {
          showToast('Antwoorden succesvol opgeslagen.', 'success');
        }
      } catch (e) {
        console.error(e);
        showToast('Kon antwoord niet opslaan.', 'error');
      }
    },
  };
}

function callLog({ csrf, storeUrl, initialCalls }) {
  return {
    openCallPanel: false,
    outcome: '',
    note: '',
    loading: false,
    calls: initialCalls || [],
    labels: {
      geen_antwoord: 'Gebeld: Geen antwoord',
      gesproken: 'Gebeld: Gesproken',
    },

    async submit() {
      if (!this.outcome) {
        alert('Kies eerst een resultaat.');
        return;
      }

      this.loading = true;
      try {
        const res = await fetch(storeUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: JSON.stringify({
            outcome: this.outcome,
            note: this.note,
          }),
        });

        const data = await res.json().catch(() => null);
        if (!res.ok || !data || !data.success) {
          throw new Error('Belmoment opslaan mislukt');
        }

        this.calls.unshift(data.call);
        this.outcome = '';
        this.note = '';
        this.openCallPanel = false;

        showToast('Belmoment succesvol opgeslagen.', 'success');
      } catch (e) {
        console.error(e);
        showToast('Kon belmoment niet opslaan.', 'error');
      } finally {
        this.loading = false;
      }
    },
  };
}
</script>
@endverbatim

@endsection