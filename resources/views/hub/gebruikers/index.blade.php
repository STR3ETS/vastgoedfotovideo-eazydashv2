@extends('layouts.app')

@section('content')
  <div class="col-span-1 p-4 h-full bg-white rounded-xl">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl text-[#215558] font-black">{{ __('gebruikers.page_title') }}</h1>

      {{-- Add menu (popover + create panel) --}}
      <div class="relative" id="add-user-menu">
        <button type="button"
                class="w-8 h-8 bg-gray-200 hover:bg-gray-300 transition duration-300 cursor-pointer rounded-full flex items-center justify-center relative group"
                aria-haspopup="menu" aria-expanded="false" aria-controls="add-user-panel">
          <i class="fa-solid fa-plus text-[#215558]"></i>
          <!-- Tooltip -->
          <div
          class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] left-0
                  opacity-0 invisible translate-y-1 pointer-events-none
                  group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                  transition-all duration-300 ease-out z-10">
              <p class="text-[#215558] text-xs font-semibold whitespace-nowrap">{{ __('gebruikers.add.tooltip') }}</p>
          </div>
        </button>

        {{-- 1) Keuze Klant / Medewerker --}}
        <div id="add-user-panel"
             class="absolute z-20 top-0 left-[135%] min-w-[300px] p-1 rounded-xl bg-white border border-gray-200 shadow-md
                    transition duration-300 ease-out transform-gpu
                    opacity-0 translate-x-1 pointer-events-none flex flex-col gap-1">
          <a href="#" id="open-create-klant" role="menuitem"
             class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
            <i class="min-w-[16px] fa-solid fa-user text-[#215558] fa-sm"></i>
            <p class="px-1 text-sm text-[#215558] font-semibold">{{ __('gebruikers.add.klant') }}</p>
          </a>
          <a href="#" id="open-create-medewerker" role="menuitem"
             class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
            <i class="min-w-[16px] fa-solid fa-building text-[#215558] fa-sm"></i>
            <p class="px-1 text-sm text-[#215558] font-semibold">{{ __('gebruikers.add.medewerker') }}</p>
          </a>
          <a href="#" id="open-create-bedrijf" role="menuitem"
            class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
            <i class="min-w-[16px] fa-solid fa-building-columns text-[#215558] fa-sm"></i>
            <p class="px-1 text-sm text-[#215558] font-semibold">{{ __('gebruikers.add.bedrijf') }}</p>
          </a>
        </div>

        {{-- 2) Create panel met formulier --}}
        <div id="create-user-panel"
             class="absolute z-30 top-0 left-[calc(135%+310px)] w-[360px] p-3 rounded-xl bg-white border border-gray-200 shadow-lg
                    transition duration-300 ease-out transform-gpu
                    opacity-0 translate-x-1 pointer-events-none">
          <div class="flex items-center justify-between mb-3">
            <p id="create-title" class="text-base text-[#215558] font-black">{{ __('gebruikers.create.title_generic') }}</p>
          </div>

          {{-- VALIDATIE FOUTEN (optioneel) --}}
          <div id="create-errors" class="hidden mb-2 text-sm text-red-600"></div>

          <form
            id="create-user-form"
            method="post"
            hx-post="{{ route('support.gebruikers.store') }}"
            hx-target="#users-list"
            hx-swap="innerHTML transition:true"
            hx-headers='{"X-Requested-With":"XMLHttpRequest"}'
            data-url-user="{{ route('support.gebruikers.store') }}"
            data-url-company="{{ route('support.gebruikers.bedrijven.store') }}"
            hx-on::after-request="
              const xhr = event.detail.xhr;
              if (xhr.status === 200) {
                const ctx = document.getElementById('create-context')?.value || 'klanten';
                const tabK = document.querySelector('.tab-klanten');
                const tabM = document.querySelector('.tab-medewerkers');

                function activateTab(link) {
                  const wrap = link?.closest('.flex.flex-col.gap-1');
                  if (!wrap) return;
                  wrap.querySelectorAll('a[role=menuitem][hx-get]').forEach(a=>{
                    a.classList.remove('bg-gray-200'); a.classList.add('hover:bg-gray-200'); a.removeAttribute('aria-current');
                  });
                  link.classList.add('bg-gray-200');
                  link.classList.remove('hover:bg-gray-200');
                  link.setAttribute('aria-current','page');
                }
                if (ctx === 'medewerkers' && tabM) activateTab(tabM);
                if (ctx === 'klanten' && tabK) activateTab(tabK);

                // detailpaneel verbergen NA create
                const detail = document.getElementById('user-detail-card');
                if (detail) { detail.classList.add('hidden'); detail.innerHTML = ''; }

                // beide overlays sluiten
                if (window.closeCreatePanel) window.closeCreatePanel();
                if (window.closeAddUserPanel) window.closeAddUserPanel();

                // errors resetten
                const err = document.getElementById('create-errors');
                if (err) { err.classList.add('hidden'); err.innerHTML=''; }
              } else if (xhr.status === 422) {
                try {
                  const json = JSON.parse(xhr.responseText);
                  const err = document.getElementById('create-errors');
                  if (err && json?.errors) {
                    err.innerHTML = Object.values(json.errors).flat().map(m=>`<div>• ${m}</div>`).join('');
                    err.classList.remove('hidden');
                  }
                } catch(e) {}
              } else if (xhr.status === 403) {
                const err = document.getElementById('create-errors');
                if (err) { err.textContent = 'Je hebt geen rechten om gebruikers aan te maken.'; err.classList.remove('hidden'); }
              }
            "
          >
            @csrf

            <input type="hidden" name="context" id="create-context" value="klanten">

            <div class="grid gap-3">
              <div id="field-name">
                <label class="block text-xs text-[#215558] opacity-70 mb-1">
                  {{ __('gebruikers.create.fields.name') }}
                </label>
                <input name="name" type="text" required
                      class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                      placeholder="{{ __('gebruikers.create.placeholder.name') }}">
              </div>

              <div id="field-email">
                <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('gebruikers.create.fields.email') }}</label>
                <input name="email" type="email" required
                       class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                       placeholder="{{ __('gebruikers.create.placeholder.email') }}">
              </div>

              {{-- BELANGRIJK: slechts één veld met name="rol" --}}
              <input type="hidden" name="rol" id="role-hidden" value="klant">

              {{-- Select zonder name; we syncen naar hidden --}}
              <div id="role-select-wrap" class="hidden">
                <label class="block text-xs text-[#215558] opacity-70 mb-1">Rol</label>

                <div id="role-dropdown" class="relative" data-open="false">
                  <button type="button" id="role-dropdown-button"
                          class="w-full py-3 px-4 text-sm rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300
                                 flex items-center justify-between gap-3"
                          aria-haspopup="listbox" aria-expanded="false" aria-labelledby="role-dropdown-label">
                    <span class="flex items-center gap-1">
                      <i id="role-btn-icon" class="min-w-[16px] fa-solid fa-building text-[#215558]"></i>
                      <span id="role-btn-text" class="px-1 text-sm text-[#215558] font-semibold">Medewerker</span>
                    </span>
                    <svg class="w-4 h-4 opacity-70 flex-shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <ul id="role-options"
                      class="absolute z-40 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-1
                             max-h-56 flex flex-col gap-1 overflow-auto hidden"
                      role="listbox" aria-labelledby="role-dropdown-label">
                    <li role="option" data-value="medewerker" aria-selected="true"
                        class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-1
                               hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none bg-gray-200">
                      <i class="min-w-[16px] fa-solid fa-building text-[#215558]"></i>
                      <span class="px-1 text-sm text-[#215558] font-semibold">Medewerker</span>
                    </li>
                    <li role="option" data-value="admin" aria-selected="false"
                        class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-1
                               hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none">
                      <i class="min-w-[16px] fa-solid fa-user-key text-[#215558] hidden"></i>
                      <span class="px-1 text-sm text-[#215558] font-semibold">Admin</span>
                    </li>
                  </ul>
                </div>

                {{-- Native select blijft bestaan als fallback (verborgen) --}}
                <select id="role-select" class="sr-only" tabindex="-1" aria-hidden="true">
                  <option value="medewerker">Medewerker</option>
                  <option value="admin">Admin</option>
                </select>
              </div>

              <div class="flex items-center gap-2 mt-3">
                <button type="submit" id="create-submit"
                        class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300
                               hx-indicator-parent relative">
                  <span class="inline-flex items-center gap-2">
                    <span class="htmx-indicator absolute -left-6 top-1/2 -translate-y-1/2 hidden">
                      <i class="fa-solid fa-spinner fa-spin"></i>
                    </span>
                    {{ __('gebruikers.create.save') }}
                  </span>
                </button>
                <button type="button" id="create-cancel"
                        class="bg-gray-200 hover:bg-gray-300 cursor-pointer text-center w-full text-gray-600 text-base font-semibold px-6 py-3 rounded-full transition duration-300">
                  {{ __('gebruikers.create.cancel') }}
                </button>
              </div>
            </div>
          </form>
        </div>
      </div> {{-- /#add-user-menu --}}
    </div>

    <div id="search-bar" class="mb-5 bg-gray-100 rounded-xl border border-gray-200 px-3 flex items-center">
      <div class="w-[16px]">
        <i class="fa-solid fa-magnifying-glass fa-sm text-gray-500"></i>
      </div>
      <input id="users-search" type="text" placeholder="{{ __('gebruikers.search.placeholder') }}"
            class="py-2 w-[calc(100%-16px)] text-sm text-gray-500 pl-3 font-medium outline-none">
    </div>

    {{-- Tabs --}}
    <div class="flex flex-col gap-1">
      <a role="menuitem"
        class="tab-klanten cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
        hx-get="{{ route('support.gebruikers.klanten') }}"
        hx-target="#users-list"
        hx-swap="innerHTML transition:true"
        hx-push-url="true"
        data-url="{{ route('support.gebruikers.klanten') }}">
        <i class="min-w-[16px] fa-solid fa-user text-[#215558] fa-sm"></i>
        <p class="px-1 text-sm text-[#215558] font-semibold">{{ __('gebruikers.tabs.klanten') }}</p>
      </a>

      @if ($user->rol === 'admin')
        <a  role="menuitem"
            class="tab-medewerkers cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
            hx-get="{{ route('support.gebruikers.medewerkers') }}"
            hx-target="#users-list"
            hx-swap="innerHTML transition:true"
            hx-push-url="true"
            data-url="{{ route('support.gebruikers.medewerkers') }}">
          <i class="min-w-[16px] fa-solid fa-building text-[#215558] fa-sm"></i>
          <p class="px-1 text-sm text-[#215558] font-semibold">{{ __('gebruikers.tabs.medewerkers') }}</p>
        </a>

        {{-- NIEUW: Bedrijven --}}
        <a  role="menuitem"
            class="tab-bedrijven cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
            hx-get="{{ route('support.gebruikers.bedrijven') }}"
            hx-target="#users-list"
            hx-swap="innerHTML transition:true"
            hx-push-url="true"
            data-url="{{ route('support.gebruikers.bedrijven') }}">
          <i class="min-w-[16px] fa-solid fa-building-columns text-[#215558] fa-sm"></i>
          <p class="px-1 text-sm text-[#215558] font-semibold">{{ __('gebruikers.tabs.bedrijven') }}</p>
        </a>
      @endif
    </div>
  </div>

  {{-- MIDDEN: LIJST --}}
  <div class="col-span-1 p-3 h-fit bg-white rounded-xl">
    <div id="users-list"
        @if(empty($bootstrap)) hx-get="{{ route('support.gebruikers.klanten') }}" hx-trigger="load" @endif
        hx-swap="innerHTML transition:true"
        data-klanten-url="{{ route('support.gebruikers.klanten') }}"
        data-medewerkers-url="{{ route('support.gebruikers.medewerkers') }}"
        data-bedrijven-url="{{ route('support.gebruikers.bedrijven') }}">
      <div class="w-full h-10 rounded-xl bg-gray-200 animate-pulse"></div>
    </div>
  </div>

  {{-- RECHTS: DETAIL (verborgen tot selectie) --}}
  <div id="user-detail-card" class="hidden col-span-1 bg-white rounded-xl h-full min-h-0 flex flex-col">
    {{-- wordt gevuld via HTMX --}}
  </div>

  {{-- CONFIRM DELETE MODAL (met animatie, gecentreerd) --}}
  <div id="confirm-delete-overlay"
      class="fixed inset-0 z-50 opacity-0 pointer-events-none transition-opacity duration-200 ease-out
              flex items-center justify-center px-4">
    {{-- Backdrop --}}
    <div id="confirm-delete-backdrop"
        class="absolute inset-0 bg-black/25 opacity-0 transition-opacity duration-200 ease-out"></div>

    {{-- Card --}}
    <div id="confirm-delete-card"
        class="relative z-10 w-[420px] max-w-[92vw] bg-white rounded-2xl shadow-xl border border-gray-200 p-4
                transform-gpu translate-y-2 opacity-0 transition-all duration-200 ease-out">
      <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
        </div>
        <div class="flex-1">
          <h2 class="text-base font-black text-[#215558]">{{ __('gebruikers.confirm.title') }}</h2>
          <p id="confirm-delete-text" class="mt-1 text-sm text-[#215558]">
            {{ __('gebruikers.confirm.description') }}
          </p>
        </div>
      </div>

      <div id="confirm-delete-error" class="mt-3 hidden text-sm text-red-600"></div>

      <div class="mt-4 flex items-center gap-2">
        <button type="button" id="confirm-delete-yes"
                class="bg-red-500 hover:bg-red-600 cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
          <span class="inline-flex items-center gap-2">
            <span class="confirm-spinner hidden"><i class="fa-solid fa-spinner fa-spin"></i></span>
            {{ __('gebruikers.confirm.yes') }}
          </span>
        </button>
        <button type="button" id="confirm-delete-cancel"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 cursor-pointer font-semibold px-6 py-3 rounded-full transition duration-300">
          {{ __('gebruikers.confirm.no') }}
        </button>
      </div>
    </div>
  </div>

  {{-- UI Scripts --}}
  <script>
    window.I18N = {
      confirm: {
        description: @json(__('gebruikers.confirm.description')),
      },
      create: {
        title_generic:    @json(__('gebruikers.create.title_generic')),
        title_klant:      @json(__('gebruikers.create.title_klant')),
        title_medewerker: @json(__('gebruikers.create.title_medewerker')),
        title_bedrijf:    @json(__('gebruikers.create.title_bedrijf')),

        fields: {
          name:         @json(__('gebruikers.create.fields.name')),
          email:        @json(__('gebruikers.create.fields.email')),
          company_name: @json(__('gebruikers.create.fields.company_name')),
        },
        placeholder: {
          name:         @json(__('gebruikers.create.placeholder.name')),
          email:        @json(__('gebruikers.create.placeholder.email')),
          company_name: @json(__('gebruikers.create.placeholder.company_name')),
        },
        roles: {
          medewerker: @json(__('gebruikers.create.roles.medewerker')),
          admin:      @json(__('gebruikers.create.roles.admin')),
        }
      }
    };
  </script>
  <script>
    window.__BOOTSTRAP__ = @json($bootstrap ?? null);
  </script>
  @verbatim
  <script>
    (function () {
      if (window.__BOOTSTRAP__) {
        const listEl = document.getElementById('users-list');
        if (listEl) {
          listEl.removeAttribute('hx-trigger');
          listEl.removeAttribute('hx-get');
        }
      }

      // =========================
      // Helpers & constants
      // =========================
      const MENU_SELECTOR = '.flex.flex-col.gap-1';

      function $(sel, root=document) { return root.querySelector(sel); }
      function $all(sel, root=document) { return Array.from(root.querySelectorAll(sel)); }

      document.body.addEventListener('htmx:configRequest', function (e) {
        if (String(e.detail.verb || '').toUpperCase() !== 'GET') {
          const token = document.querySelector('meta[name="csrf-token"]')?.content;
          if (token) e.detail.headers['X-CSRF-TOKEN'] = token;
        }
      });

      // --- Focus/selektie van het zoekveld bewaren & herstellen ---
      let __searchState = { hadFocus:false, pos:0, end:0 };
      let __searchInFlight = false;
      let __pendingSearchTerm = null;

      function getSearchEl(){ return document.getElementById('users-search'); }

      function snapshotSearch() {
        const el = getSearchEl();
        if (!el) return;
        __searchState = {
          hadFocus: document.activeElement === el,
          pos: typeof el.selectionStart === 'number' ? el.selectionStart : (el.value || '').length,
          end: typeof el.selectionEnd === 'number' ? el.selectionEnd : (el.value || '').length,
        };
      }

      function restoreSearch() {
        const el = getSearchEl();
        if (!el) return;
        if (__searchState.hadFocus) {
          el.focus({ preventScroll: true });
          try { el.setSelectionRange(__searchState.pos, __searchState.end); } catch(_) {}
        }
      }

      // =========================
      // Tabs & list selection
      // =========================
      function setActiveTab(link) {
        if (!link) return;
        const wrap = link.closest(MENU_SELECTOR);
        if (!wrap) return;
        wrap.querySelectorAll('a[role="menuitem"][hx-get]').forEach(a => {
          a.classList.remove('bg-gray-200');
          a.classList.add('hover:bg-gray-200');
          a.removeAttribute('aria-current');
        });
        link.classList.add('bg-gray-200');
        link.classList.remove('hover:bg-gray-200');
        link.setAttribute('aria-current', 'page');
      }

      function setActiveItem(el){
        document.querySelectorAll('#medewerkers-list .item-user, #klanten-list .item-user, #companies-list .item-company').forEach(a=>{
          a.classList.remove('bg-gray-200');
          a.removeAttribute('aria-current');
        });
        el.classList.add('bg-gray-200');
        el.setAttribute('aria-current','true');
      }

      // Delegate clicks for tabs/items
      document.addEventListener('click', (e) => {
        const tab = e.target.closest('a[role="menuitem"][hx-get]');
        if (tab) setActiveTab(tab);

        const item = e.target.closest('.item-user, .item-company');
        if (item && item.hasAttribute('hx-get')) setActiveItem(item);
      });

      // =========================
      // HTMX request lifecycles
      // =========================
      document.body.addEventListener('htmx:beforeRequest', (evt)=>{
        const path = evt.detail?.path || '';
        const isDetailReq = /\/(medewerkers|klanten|bedrijven)\/\d+(?:\?.*)?$/i.test(path);
        if (isDetailReq) {
          const card = document.getElementById('user-detail-card');
          if (card) {
            card.classList.remove('hidden');
            card.innerHTML = '<div class="p-3"><div class="w-full h-10 rounded-xl bg-gray-200 animate-pulse"></div></div>';
          }
        }
      });

      // -- Gecombineerde afterSwap: 1 centrale handler
      document.body.addEventListener('htmx:afterSwap', (evt) => {
        const id = evt.target?.id;

        if (id === 'users-list') {
          restoreSearch();
          setTimeout(restoreSearch, 0);

          // ✅ allow new searches after this swap
          __searchInFlight = false;
          __pendingSearchTerm = null;

          const onDetailUrl = /\/(medewerkers|klanten|bedrijven)\/\d+(?:\?.*)?$/i.test(location.pathname);
          if (!onDetailUrl) {
            const card = document.getElementById('user-detail-card');
            if (card) { card.classList.add('hidden'); card.innerHTML = ''; }
          }
        }

        if (id === 'user-detail-card') {
          const card = document.getElementById('user-detail-card');
          if (card) card.classList.remove('hidden');
        }
      });

      document.body.addEventListener('htmx:afterOnLoad', (evt)=>{
        if (evt.detail?.target && evt.detail.target.id === 'users-list') {
          // zorg dat nieuwe searches weer mogen
          __searchInFlight = false;

          // als er tijdens de vorige request is doorgetypt, voer die term nu uit
          if (__pendingSearchTerm !== null) {
            const el = getSearchEl();
            if (el) el.value = __pendingSearchTerm;
            const next = __pendingSearchTerm;
            __pendingSearchTerm = null;
            setTimeout(() => runSearch(false), 0);
          }
        }
      });

      // =========================
      // Add menu (paneel 1)
      // =========================
      const addMenuRoot  = document.getElementById('add-user-menu');
      const btn          = addMenuRoot?.querySelector('button[aria-controls="add-user-panel"]');
      const panel        = addMenuRoot?.querySelector('#add-user-panel');

      const openClasses   = ['opacity-100','translate-x-0'];
      const closedClasses = ['opacity-0','translate-x-1','pointer-events-none'];

      function openPanel() {
        panel.classList.remove(...closedClasses);
        panel.classList.add(...openClasses);
        btn.setAttribute('aria-expanded','true');
      }
      function closePanel() {
        panel.classList.remove(...openClasses);
        panel.classList.add(...closedClasses);
        btn.setAttribute('aria-expanded','false');
      }
      function togglePanel() {
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        expanded ? closePanel() : openPanel();
      }

      // Expose close so form submit can close it
      window.closeAddUserPanel = closePanel;

      // Active state in keuze-menu
      const linkCreateKlant      = document.getElementById('open-create-klant');
      const linkCreateMedewerker = document.getElementById('open-create-medewerker');
      const linkCreateBedrijf    = document.getElementById('open-create-bedrijf');

      function setAddMenuActive(type) {
        [linkCreateKlant, linkCreateMedewerker, linkCreateBedrijf].forEach((el) => {
          if (!el) return;
          el.classList.remove('bg-gray-200');
          el.classList.add('hover:bg-gray-200');
          el.removeAttribute('aria-current');
        });
        const map = { klant: linkCreateKlant, medewerker: linkCreateMedewerker, bedrijf: linkCreateBedrijf };
        const active = map[type];
        if (active) {
          active.classList.add('bg-gray-200');
          active.classList.remove('hover:bg-gray-200');
          active.setAttribute('aria-current', 'true');
        }
      }
      function clearAddMenuActive() {
        [linkCreateKlant, linkCreateMedewerker, linkCreateBedrijf].forEach((el) => {
          if (!el) return;
          el.classList.remove('bg-gray-200');
          el.classList.add('hover:bg-gray-200');
          el.removeAttribute('aria-current');
        });
      }

      btn?.addEventListener('click', (e) => {
        e.stopPropagation();
        togglePanel();
      });
      document.addEventListener('click', (e) => {
        if (!addMenuRoot.contains(e.target)) {
          closePanel();
          window.closeCreatePanel?.();
        }
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          closePanel();
          window.closeCreatePanel?.();
        }
      });

      // =========================
      // Create panel (paneel 2) + role custom dropdown
      // =========================
      const createPanel  = document.getElementById('create-user-panel');
      const createForm   = document.getElementById('create-user-form');
      const createTitle  = document.getElementById('create-title');
      const roleWrap     = document.getElementById('role-select-wrap');
      const roleHidden   = document.getElementById('role-hidden');
      const roleSelectEl = document.getElementById('role-select');
      const ctxInput     = document.getElementById('create-context');

      

      // Email wrap/input (voor bedrijf verbergen)
      const emailWrap   = document.getElementById('field-email');
      const emailInput  = emailWrap?.querySelector('input[type="email"]');
      const fieldNameWrap  = document.getElementById('field-name');
      const fieldNameLabel = fieldNameWrap?.querySelector('label');
      const fieldNameInput = fieldNameWrap?.querySelector('input[name="name"]');

      // — laat htmx het gewijzigde formulier opnieuw “zien”
      function setFormPost(url) {
        if (!createForm || !url) return;
        createForm.setAttribute('hx-post', url);
        createForm.setAttribute('action', url);
        if (window.htmx) htmx.process(createForm);
      }

      // — context meegeven (users) of juist NIET (companies)
      function setContextEnabled(enabled) {
        if (!ctxInput) return;
        if (enabled) ctxInput.setAttribute('name', 'context');
        else ctxInput.removeAttribute('name');
      }

      // Fancy dropdown
      const roleDdRoot  = document.getElementById('role-dropdown');
      const roleDdBtn   = document.getElementById('role-dropdown-button');
      const roleDdList  = document.getElementById('role-options');

      function openRoleDd() {
        if (!roleDdRoot) return;
        roleDdList.classList.remove('hidden');
        roleDdRoot.dataset.open = 'true';
        roleDdBtn.setAttribute('aria-expanded','true');
      }

      function closeRoleDd() {
        if (!roleDdRoot) return;
        roleDdList.classList.add('hidden');
        roleDdRoot.dataset.open = 'false';
        roleDdBtn.setAttribute('aria-expanded','false');
      }

      function setRoleValue(val) {
        // 1) hidden input (enige name="rol")
        roleHidden.value = val;

        // 2) native select (fallback / consistent)
        if (roleSelectEl) roleSelectEl.value = val;

        // 3) UI: label, actieve achtergrond + knopicon/tekst sync
        const items = roleDdList?.querySelectorAll('[role="option"]') || [];
        const btnIcon = document.getElementById('role-btn-icon');
        const btnText = document.getElementById('role-btn-text');

        items.forEach((li) => {
          const active = li.dataset.value === val;
          li.setAttribute('aria-selected', active ? 'true' : 'false');
          li.classList.toggle('bg-gray-200', active);

          if (active) {
            // Tekst uit li -> button
            const labelEl = li.querySelector('span');
            const labelTxt = (labelEl?.textContent || li.textContent || '').trim();
            if (btnText) btnText.textContent = labelTxt;

            // Icon uit li -> button
            const liIcon = li.querySelector('i');
            if (liIcon && btnIcon) {
              btnIcon.className = liIcon.className.replace(/\bhidden\b/g, '');
              if (!btnIcon.classList.contains('min-w-[16px]')) btnIcon.classList.add('min-w-[16px]');
              if (!btnIcon.classList.contains('text-[#215558]')) btnIcon.classList.add('text-[#215558]');
            }
          }
        });
      }

      function initRoleDdFromSelect() {
        const val = roleSelectEl?.value || 'medewerker';
        setRoleValue(val);
      }

      // Open/close handlers dropdown
      roleDdBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        const isOpen = roleDdRoot?.dataset.open === 'true';
        if (isOpen) closeRoleDd(); else openRoleDd();
      });

      // Option click
      roleDdList?.addEventListener('click', (e) => {
        const li = e.target.closest('[role="option"][data-value]');
        if (!li) return;
        setRoleValue(li.dataset.value);
        closeRoleDd();
      });

      // Keyboard support
      roleDdBtn?.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          e.preventDefault();
          openRoleDd();
          const focusable = roleDdList.querySelectorAll('[role="option"]');
          (focusable[0])?.focus();
        }
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const isOpen = roleDdRoot?.dataset.open === 'true';
          if (isOpen) closeRoleDd(); else openRoleDd();
        }
        if (e.key === 'Escape') closeRoleDd();
      });

      roleDdList?.addEventListener('keydown', (e) => {
        const options = Array.from(roleDdList.querySelectorAll('[role="option"]'));
        const currentIndex = options.indexOf(document.activeElement);
        if (e.key === 'ArrowDown') {
          e.preventDefault();
          const next = options[Math.min(options.length - 1, currentIndex + 1)] || options[0];
          next.focus();
        }
        if (e.key === 'ArrowUp') {
          e.preventDefault();
          const prev = options[Math.max(0, currentIndex - 1)] || options[options.length - 1];
          prev.focus();
        }
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          const li = document.activeElement;
          if (li?.dataset?.value) {
            setRoleValue(li.dataset.value);
            closeRoleDd();
            roleDdBtn?.focus();
          }
        }
        if (e.key === 'Escape') {
          closeRoleDd();
          roleDdBtn?.focus();
        }
      });

      // Click outside dropdown
      document.addEventListener('click', (e) => {
        if (roleDdRoot && !roleDdRoot.contains(e.target)) closeRoleDd();
      });

      // select -> hidden sync (enige name="rol")
      roleSelectEl?.addEventListener('change', () => setRoleValue(roleSelectEl.value));

      // Create panel open/close
      function openCreate(type) {
        // reset velden en foutmeldingen
        createForm.reset();
        const err = document.getElementById('create-errors'); if (err) { err.classList.add('hidden'); err.innerHTML=''; }

        // Standaard → user-creation defaults
        if (emailWrap)  emailWrap.classList.remove('hidden');
        if (emailInput) { emailInput.disabled = false; emailInput.setAttribute('name','email'); emailInput.required = true; }
        roleWrap.classList.add('hidden');
        roleHidden.value = 'klant';
        ctxInput.value = 'klanten';
        setFormPost(createForm.dataset.urlUser);

        // ⬇️ Nieuw: standaard label/placeholder voor “Naam”
        if (fieldNameLabel) fieldNameLabel.textContent = (window.I18N?.create?.fields?.name ?? 'Naam');
        if (fieldNameInput) fieldNameInput.placeholder = (window.I18N?.create?.placeholder?.name ?? 'Voor- en achternaam');

        if (type === 'klant') {
          createTitle.textContent = (window.I18N?.create?.title_klant || 'Nieuwe klant');
          setFormPost(createForm.dataset.urlUser);
          ctxInput.value = 'klanten';
          setContextEnabled(true);
        } else if (type === 'medewerker') {
          createTitle.textContent = (window.I18N?.create?.title_medewerker || 'Nieuwe medewerker');
          roleWrap.classList.remove('hidden');
          roleSelectEl.value = 'medewerker';
          roleHidden.value = roleSelectEl.value;
          ctxInput.value = 'medewerkers';
          initRoleDdFromSelect();
          setFormPost(createForm.dataset.urlUser);
          setContextEnabled(true);
        } else if (type === 'bedrijf') {
          createTitle.textContent = (window.I18N?.create?.title_bedrijf || 'Nieuw bedrijf');

          // Bedrijf: alleen NAAM, e-mail verbergen
          if (emailWrap)  emailWrap.classList.add('hidden');
          if (emailInput) { emailInput.disabled = true; emailInput.removeAttribute('name'); emailInput.required = false; }
          roleWrap.classList.add('hidden');

          // ⬇️ Nieuw: label + placeholder omzetten naar “Bedrijfsnaam”
          if (fieldNameLabel) fieldNameLabel.textContent = (window.I18N?.create?.fields?.company_name ?? 'Bedrijfsnaam');
          if (fieldNameInput) fieldNameInput.placeholder = (window.I18N?.create?.placeholder?.company_name ?? 'Bedrijfsnaam');

          // POST naar bedrijven endpoint
          setFormPost(createForm.dataset.urlCompany);
          ctxInput.value = 'bedrijven';

          setContextEnabled(false);
        }

        // active state + panelen openen (reeds aanwezig)
        setAddMenuActive(type);
        openPanel();
        createPanel.classList.remove(...closedClasses);
        createPanel.classList.add(...openClasses);
      }

      window.closeCreatePanel = function () {
        createPanel.classList.remove(...openClasses);
        createPanel.classList.add(...closedClasses);
        // active state verwijderen als create dicht is
        clearAddMenuActive();
      };

      document.getElementById('open-create-klant')?.addEventListener('click', (e)=>{
        e.preventDefault();
        openCreate('klant');
      });
      document.getElementById('open-create-medewerker')?.addEventListener('click', (e)=>{
        e.preventDefault();
        openCreate('medewerker');
      });
      document.getElementById('open-create-bedrijf')?.addEventListener('click', (e)=>{
        e.preventDefault();
        openCreate('bedrijf');
      });

      document.getElementById('create-cancel')?.addEventListener('click', (e)=>{
        e.preventDefault();
        window.closeCreatePanel();
      });
      document.getElementById('create-cancel-x')?.addEventListener('click', (e)=>{
        e.preventDefault();
        window.closeCreatePanel();
      });

      // =========================
      // Confirm Delete Modal (animated)
      // =========================
      const delOverlay  = document.getElementById('confirm-delete-overlay');
      const delBackdrop = document.getElementById('confirm-delete-backdrop');
      const delCard     = document.getElementById('confirm-delete-card');
      const delYes      = document.getElementById('confirm-delete-yes');
      const delCancel   = document.getElementById('confirm-delete-cancel');
      const delText     = document.getElementById('confirm-delete-text');
      const delError    = document.getElementById('confirm-delete-error');
      const spinner     = delYes?.querySelector('.confirm-spinner');
      const CSRF        = (document.querySelector('meta[name="csrf-token"]')||{}).content || '';

      let pendingDelete = { url: null, target: null };

      function showDeleteOverlay(name, url, targetSel) {
        delOverlay.classList.remove('hidden');
        pendingDelete.url    = url;
        pendingDelete.target = targetSel;
        if (delText) delText.textContent = (window.I18N?.confirm?.description || '');
        if (delError) { delError.classList.add('hidden'); delError.textContent = ''; }
        if (spinner) spinner.classList.add('hidden');
        delYes?.removeAttribute('disabled');

        // Scroll lock
        document.documentElement.classList.add('overflow-hidden');

        // Ensure starting animation state
        delOverlay.classList.remove('pointer-events-none');
        delOverlay.classList.add('opacity-0');
        delBackdrop.classList.add('opacity-0');
        delCard.classList.add('opacity-0','translate-y-2');

        // Animate in next frame
        requestAnimationFrame(() => {
          delOverlay.classList.remove('opacity-0');
          delBackdrop.classList.remove('opacity-0');
          delCard.classList.remove('opacity-0','translate-y-2');
        });
      }

      function hideDeleteOverlay() {
        // UI altijd resetten (voorkomt blijvende spinner)
        delYes?.removeAttribute('disabled');
        spinner?.classList.add('hidden');
        delError?.classList.add('hidden');

        // Animate out
        delOverlay.classList.add('opacity-0');
        delBackdrop.classList.add('opacity-0');
        delCard.classList.add('opacity-0','translate-y-2');

        // Cleanup + volledig verbergen (ook als transition niet triggert)
        const cleanup = () => {
          delOverlay.classList.add('pointer-events-none', 'hidden');
          // start state opruimen
          delOverlay.classList.remove('opacity-0');
          delBackdrop.classList.remove('opacity-0');
          delCard.classList.remove('opacity-0','translate-y-2');

          document.documentElement.classList.remove('overflow-hidden');
          pendingDelete = { url: null, target: null };

          delOverlay.removeEventListener('transitionend', onEnd);
          clearTimeout(fallback);
        };
        const onEnd = (e) => { if (e.target === delOverlay) cleanup(); };
        const fallback = setTimeout(cleanup, 300);
        delOverlay.addEventListener('transitionend', onEnd);
      }

      function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        if (meta?.content) return meta.content;
        const hidden = document.querySelector('input[name="_token"]');
        if (hidden?.value) return hidden.value;
        if (window.CSRF_TOKEN) return window.CSRF_TOKEN;
        return '';
      }

      // Expose for list-item delete buttons
      window.openDeleteConfirm = function(btn){
        const url  = btn.getAttribute('data-delete-url');
        const name = btn.getAttribute('data-user-name') || 'deze gebruiker';
        // zoek dichtstbijzijnde container
        const container = btn.closest('#medewerkers-list, #klanten-list, #companies-list, #users-list');
        const targetSel = container ? `#${container.id}` : '#users-list';
        showDeleteOverlay(name, url, targetSel);
      };

      // Execute DELETE (robust + debug + 3 fallbacks)
      delYes?.addEventListener('click', function () {
        if (!pendingDelete.url) return;

        console.debug('[DEL] start', { url: pendingDelete.url, target: pendingDelete.target });

        // UI: blokkeren + spinner
        delYes.setAttribute('disabled', 'true');
        spinner?.classList.remove('hidden');

        const targetEl = document.querySelector(pendingDelete.target);
        const isWrapper = targetEl && (targetEl.id === 'medewerkers-list' || targetEl.id === 'klanten-list' || targetEl.id === 'companies-list');
        const swapMode = isWrapper ? 'outerHTML transition:true' : 'innerHTML transition:true';

        // ---- (A) MutationObserver fallback: sluit zodra target wijzigt (swap) ----
        let observer;
        if (targetEl) {
          observer = new MutationObserver((muts) => {
            console.debug('[DEL] mutation observed on target -> closing overlay');
            try { observer.disconnect(); } catch(_) {}
            safeCloseOverlay();
          });
          observer.observe(targetEl, { childList: true, subtree: false });
        }

        // ---- helpers om 1x af te handelen, wat er ook gebeurt ----
        let done = false;
        const cleanupOnce = () => {
          if (done) return;
          done = true;
          console.debug('[DEL] cleanupOnce');
          try { observer?.disconnect(); } catch(_) {}
          spinner?.classList.add('hidden');
          delYes.removeAttribute('disabled');
          // luisteraars loskoppelen
          document.body.removeEventListener('htmx:afterOnLoad', onHtmxSuccess);
          document.body.removeEventListener('htmx:afterSwap', onHtmxSwap);
          document.body.removeEventListener('htmx:afterRequest', onHtmxAfterReq);
          document.body.removeEventListener('htmx:responseError', onHtmxError);
        };
        const safeCloseOverlay = () => {
          console.debug('[DEL] safeCloseOverlay()');
          hideDeleteOverlay();
          // extra safety: active states resetten + detailpaneel dicht
          document.querySelectorAll('#medewerkers-list .item-user, #klanten-list .item-user, #companies-list .item-company')
            .forEach(el => { el.classList.remove('bg-gray-200'); el.removeAttribute('aria-current'); });
          const detail = document.getElementById('user-detail-card');
          if (detail && !detail.classList.contains('hidden')) {
            detail.classList.add('hidden'); detail.innerHTML = '';
          }
          cleanupOnce();
        };
        const showError = (msg) => {
          console.debug('[DEL] error', msg);
          if (delError) { delError.textContent = msg; delError.classList.remove('hidden'); }
          cleanupOnce();
        };

        // ---- (B) Globale HTMX events (robust bij redirects/partials) ----
        const onHtmxSuccess = (e) => {
          // succesvolle response (na onload)
          const sameSource = e.detail?.elt === delYes; // we zetten 'source' hieronder
          const samePath   = e.detail?.path === pendingDelete.url;
          if (sameSource || samePath) {
            console.debug('[DEL] htmx:afterOnLoad matched -> closing overlay');
            safeCloseOverlay();
          }
        };
        const onHtmxSwap = (e) => {
          // wanneer de *target* is ge-swapt (extra safety)
          if (e.target && pendingDelete.target && ('#' + e.target.id) === pendingDelete.target) {
            console.debug('[DEL] htmx:afterSwap on target -> closing overlay');
            safeCloseOverlay();
          }
        };
        const onHtmxAfterReq = (e) => {
          // altijd na request; log status
          if (e.detail?.elt === delYes) {
            console.debug('[DEL] htmx:afterRequest', { status: e.detail?.xhr?.status });
          }
        };
        const onHtmxError = (e) => {
          if (e.detail?.elt !== delYes) return;
          let msg = 'Verwijderen is mislukt. Probeer het nogmaals.';
          const xhr = e.detail?.xhr;
          try {
            if (xhr?.status === 419) msg = 'Sessie verlopen (CSRF). Vernieuw de pagina en probeer opnieuw.';
            else if (xhr?.status === 403) msg = 'Je hebt geen rechten om te verwijderen.';
            else if (xhr?.response && xhr.response.message) msg = xhr.response.message;
            else if (xhr?.responseText) msg = xhr.responseText;
          } catch(_) {}
          showError(msg);
        };

        document.body.addEventListener('htmx:afterOnLoad', onHtmxSuccess);
        document.body.addEventListener('htmx:afterSwap', onHtmxSwap);
        document.body.addEventListener('htmx:afterRequest', onHtmxAfterReq);
        document.body.addEventListener('htmx:responseError', onHtmxError);

        // ---- (C) De eigenlijke DELETE call ----
        const req = htmx.ajax('DELETE', pendingDelete.url, {
          source: delYes,                 // <— belangrijk voor event matching
          target: pendingDelete.target,
          swap: swapMode,
          headers: {
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
          }
        });

        // XHR fallback (vuurt altijd)
        if (req && typeof req.addEventListener === 'function') {
          req.addEventListener('loadend', () => {
            console.debug('[DEL] xhr loadend', { status: req.status });
            if (req.status >= 200 && req.status < 300) {
              safeCloseOverlay();
            } else if (!done) {
              let msg = 'Verwijderen is mislukt. Probeer het nogmaals.';
              try {
                if (req.status === 419) msg = 'Sessie verlopen (CSRF). Vernieuw de pagina en probeer opnieuw.';
                else if (req.status === 403) msg = 'Je hebt geen rechten om te verwijderen.';
                else if (req.response && req.response.message) msg = req.response.message;
                else if (req.responseText) msg = req.responseText;
              } catch(_) {}
              showError(msg);
            }
          });
        } else {
          console.debug('[DEL] htmx.ajax returned no XHR (version diff?) — relying on HTMX events + MutationObserver');
        }

        // Ultimate failsafe: sluit sowieso na 1s als target is veranderd maar events niet oppikten
        setTimeout(() => {
          if (!done && targetEl && targetEl.isConnected) {
            const nowHasPulse = !!targetEl.querySelector('.animate-pulse');
            console.debug('[DEL] 1s failsafe fired. Overlay state:', { nowHasPulse, done });
            // we sluiten alleen als we géén fout zagen (done==false) — UX keuze
            safeCloseOverlay();
          }
        }, 1000);
      });


      // Close interactions
      delCancel?.addEventListener('click', hideDeleteOverlay);
      delOverlay?.addEventListener('click', (e)=>{
        // close if clicking outside the card
        if (delCard && !delCard.contains(e.target)) hideDeleteOverlay();
      });
      document.addEventListener('keydown', (e)=>{
        if (e.key === 'Escape' && !delOverlay.classList.contains('pointer-events-none')) hideDeleteOverlay();
      });

      // =========================
      // Person-picker (delegated; werkt ook na HTMX swaps)
      // =========================
      (function setupPersonPicker(){
        const HIDE = ['opacity-0','pointer-events-none','translate-y-1'];
        const OPEN_FLAG = '__open';

        function openPanel(panel){
          panel.classList.add(OPEN_FLAG);
          panel.classList.remove(...HIDE);

          const body = panel.querySelector('[data-person-picker-panel-body]');
          if (!body) return;

          // 1) Probeer htmx te (her)processen (registreert triggers zoals revealed/intersect)
          if (window.htmx) htmx.process(body);

          // 2) Fallback: als er nog geen inhoud is opgehaald, forceer een GET
          const hasResults = body.querySelector('[data-user-id], button[hx-post]'); // wat je lijst-items uniek maakt
          if (!hasResults && window.htmx) {
            const url = body.getAttribute('hx-get');
            if (url) {
              htmx.ajax('GET', url, {
                target: body,
                swap: 'innerHTML transition:true',
                headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }
              });
            }
          }
        }
        function closePanel(panel){
          panel.classList.remove(OPEN_FLAG);
          panel.classList.add(...HIDE);
        }
        function closeAnyOpen(){
          const open = document.querySelector('[data-person-picker-panel].' + OPEN_FLAG);
          if (open) closePanel(open);
        }

        // Toggle bij klik op trigger
        document.addEventListener('click', (e)=>{
          if (e.target.closest('[data-person-picker-panel]')) return; // ⬅️ belangrijk

          const trigger = e.target.closest('[data-person-picker-trigger]');
          if (!trigger) return;

          const panel = trigger.querySelector('[data-person-picker-panel]');
          if (!panel) return;

          e.preventDefault();
          e.stopPropagation();

          if (panel.classList.contains(OPEN_FLAG)) {
            closePanel(panel);
          } else {
            closeAnyOpen();
            openPanel(panel);
          }
        });

        // Klik buiten panel => sluiten
        document.addEventListener('click', (e)=>{
          const anyPanel = e.target.closest('[data-person-picker-panel]');
          if (anyPanel) return; // ⬅️ click binnen panel => laat open

          const open = document.querySelector('[data-person-picker-panel].' + OPEN_FLAG);
          if (!open) return;

          const trigger = open.closest('[data-person-picker-trigger]');
          if (trigger && trigger.contains(e.target)) return; // klik op trigger = handled door toggle

          closePanel(open);
        });

        // Escape => sluiten
        document.addEventListener('keydown', (e)=>{
          if (e.key !== 'Escape') return;
          closeAnyOpen();
        });
      })();

      // === Search (HTMX + debounced, tab-aware) ===
      const searchInput = document.querySelector('#users-search');

      // eenvoudige debounce
      function debounce(fn, ms=300){
        let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), ms); };
      }

      // actieve tab element ophalen
      function getActiveTab(){
        return document.querySelector('.tab-bedrijven[aria-current="page"], .tab-medewerkers[aria-current="page"], .tab-klanten[aria-current="page"]')
            || document.querySelector('.tab-klanten') // fallback = klanten
            || document.querySelector('a[role="menuitem"][hx-get]');
      }

      // basis URL (van de tab)
      function getBaseListUrl(tabEl=getActiveTab()){
        if (!tabEl) return '';
        return tabEl.getAttribute('data-url') || tabEl.getAttribute('hx-get') || '';
      }

      // uitvoeren van de zoekactie
      function runSearch(force=false){
        const searchInput = getSearchEl();
        if (!searchInput) return;

        const term = (searchInput.value || '').trim();

        // Als er al een request loopt, queue de laatste term en stop.
        if (__searchInFlight) {
          __pendingSearchTerm = term;
          return;
        }

        // Snapshot vóór we de request starten
        snapshotSearch();

        const tab  = getActiveTab();
        const base = getBaseListUrl(tab);
        if (!base) return;

        let url = base;
        if (term.length || force) {
          const sep = url.includes('?') ? '&' : '?';
          url = term.length ? `${url}${sep}q=${encodeURIComponent(term)}` : base;
        }

        __searchInFlight = true;

        // Extra: direct focus behouden nog vóór netwerk (minimal flicker)
        restoreSearch();

        htmx.ajax('GET', url, {
          target: '#users-list',
          swap: 'innerHTML transition:true',
          headers: { 'HX-Request':'true', 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }

        });
      }

      // debounced typen
      searchInput?.addEventListener('input', debounce(()=>{
        const v = (getSearchEl()?.value || '').trim();
        if (!v) return runSearch(true);   // ⬅️ leeg? forceer reset naar volledige lijst
        runSearch(false);
      }, 300));

      // Enter = direct zoeken, Escape = clear + reset
      searchInput?.addEventListener('keydown', (e)=>{
        if (e.key === 'Enter') { e.preventDefault(); runSearch(true); }
        if (e.key === 'Escape') {
          if (searchInput.value) {
            searchInput.value = '';
            runSearch(true);
          }
        }
      });

      // Tab-click overschrijven als er een zoekterm staat: meteen gefilterd ophalen voor die tab
      document.addEventListener('click', (e)=>{
        const tab = e.target.closest('a[role="menuitem"][hx-get]');
        if (!tab) return;

        const term = (document.querySelector('#users-search')?.value || '').trim();
        if (!term) return; // geen zoekterm -> laat HTMX normale tab-load + hx-push-url doen

        e.preventDefault();

        if (typeof setActiveTab === 'function') setActiveTab(tab);

        const base = tab.getAttribute('data-url') || tab.getAttribute('hx-get') || '';
        const sep  = base.includes('?') ? '&' : '?';
        const url  = `${base}${sep}q=${encodeURIComponent(term)}`;

        // ✨ Belangrijk: update de adresbalk zonder reload
        window.history.pushState({}, '', url);

        snapshotSearch();

        htmx.ajax('GET', url, {
          target: '#users-list',
          swap: 'innerHTML transition:true',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html'
          }
        });
      });

      // ===== Bootstrap bij hard refresh op diepe URL =====
      if (window.__BOOTSTRAP__) {
        const { activeTab, listUrl, prefetchDetail } = window.__BOOTSTRAP__;

        // 1) Activeer de juiste tab (bv. "bedrijven")
        if (activeTab) {
          const tabEl = document.querySelector(`.tab-${activeTab}[hx-get]`);
          if (tabEl && typeof setActiveTab === 'function') setActiveTab(tabEl);
        }

        // 2) Haal de lijst van die tab op
        if (listUrl) {
          htmx.ajax('GET', listUrl, {
            target: '#users-list',
            swap: 'innerHTML transition:true',
            headers: { 'HX-Request':'true', 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }

          });
        }

        // 3) Toon + laad het detailpaneel
        if (prefetchDetail) {
          const card = document.getElementById('user-detail-card');
          if (card) {
            card.classList.remove('hidden');
            htmx.ajax('GET', prefetchDetail, {
              target: '#user-detail-card',
              swap: 'innerHTML transition:true',
              headers: { 'HX-Request':'true', 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }

            });
          }
        }
      }

      // --- Fallback bootstrap op basis van de huidige URL ---
      function computeFromUrl() {
        const listEl = document.getElementById('users-list');
        const urls = {
          klanten:     listEl?.dataset.klantenUrl     || '',
          medewerkers: listEl?.dataset.medewerkersUrl || '',
          bedrijven:   listEl?.dataset.bedrijvenUrl   || ''
        };

        const path = location.pathname;
        const qs   = new URLSearchParams(location.search);
        const q    = qs.get('q') || '';

        let activeTab = 'klanten';
        let prefetchDetail = null;

        const mDetail = path.match(/\/(klanten|medewerkers|bedrijven)\/\d+$/i);
        if (mDetail) {
          activeTab = mDetail[1].toLowerCase();
          prefetchDetail = path + location.search; // detail exact uit URL
        } else if (/\/medewerkers\/?$/i.test(path)) {
          activeTab = 'medewerkers';
        } else if (/\/bedrijven\/?$/i.test(path)) {
          activeTab = 'bedrijven';
        }

        let listUrl = urls[activeTab];
        if (q) listUrl += (listUrl.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(q);

        return { activeTab, listUrl, prefetchDetail };
      }

      // Als server geen bootstrap gaf, doen we het client-side
      if (!window.__BOOTSTRAP__) {
        const boot = computeFromUrl();

        // Tab visueel activeren
        const tabEl = document.querySelector(`.tab-${boot.activeTab}[hx-get]`);
        if (tabEl && typeof setActiveTab === 'function') setActiveTab(tabEl);

        // Lijst ophalen die bij de URL hoort
        if (boot.listUrl && window.htmx) {
          htmx.ajax('GET', boot.listUrl, {
            target: '#users-list',
            swap: 'innerHTML transition:true',
            headers: { 'HX-Request':'true', 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }

          });
        }

        // Eventueel detail meteen inladen
        if (boot.prefetchDetail && window.htmx) {
          const card = document.getElementById('user-detail-card');
          if (card) {
            card.classList.remove('hidden');
            htmx.ajax('GET', boot.prefetchDetail, {
              target: '#user-detail-card',
              swap: 'innerHTML transition:true',
              headers: { 'HX-Request':'true', 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }

            });
          }
        }
      }
    })();
  </script>
  @endverbatim
@endsection