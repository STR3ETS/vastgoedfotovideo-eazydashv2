@extends('hub.layouts.app')

@section('content')
  {{-- LINKS: NAV + CREATE --}}
  <div class="col-span-1 p-4 h-full bg-white rounded-xl">
    <div class="mb-4 flex items-center justify-between">
      <h1 class="text-xl text-[#215558] font-black">Gebruikers</h1>

      {{-- Add menu (popover + create panel) --}}
      <div class="relative" id="add-user-menu">
        <button
          type="button"
          class="w-8 h-8 bg-gray-200 hover:bg-gray-300 transition duration-300 cursor-pointer rounded-full flex items-center justify-center relative group"
          aria-haspopup="menu"
          aria-expanded="false"
          aria-controls="add-user-panel"
        >
          <i class="fa-solid fa-plus text-[#215558]"></i>

          {{-- Tooltip --}}
          <div
            class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] left-0
                   opacity-0 invisible translate-y-1 pointer-events-none
                   group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                   transition-all duration-300 ease-out z-10"
          >
            <p class="text-[#215558] text-xs font-semibold whitespace-nowrap">Gebruiker toevoegen</p>
          </div>
        </button>

        {{-- 1) Keuze Klant / Medewerker --}}
        <div
          id="add-user-panel"
          class="absolute z-20 top-0 left-[135%] min-w-[300px] p-1 rounded-xl bg-white border border-gray-200 shadow-md
                 transition duration-300 ease-out transform-gpu
                 opacity-0 translate-x-1 pointer-events-none flex flex-col gap-1"
        >
          <a href="#" id="open-create-klant" role="menuitem"
             class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
            <i class="min-w-[16px] fa-solid fa-user text-[#215558] fa-sm"></i>
            <p class="px-1 text-sm text-[#215558] font-semibold">Klant</p>
          </a>

          <a href="#" id="open-create-medewerker" role="menuitem"
             class="w-full p-2 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300">
            <i class="min-w-[16px] fa-solid fa-building-user text-[#215558] fa-sm"></i>
            <p class="px-1 text-sm text-[#215558] font-semibold">Medewerker</p>
          </a>
        </div>

        {{-- 2) Create panel met formulier --}}
        <div
          id="create-user-panel"
          class="absolute z-30 top-0 left-[calc(135%+310px)] w-[360px] p-3 rounded-xl bg-white border border-gray-200 shadow-lg
                 transition duration-300 ease-out transform-gpu
                 opacity-0 translate-x-1 pointer-events-none"
        >
          <div class="flex items-center justify-between mb-3">
            <p id="create-title" class="text-base text-[#215558] font-black">Nieuwe gebruiker</p>
            <button type="button" id="create-cancel-x"
              class="w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 transition duration-300 flex items-center justify-center">
              <i class="fa-solid fa-xmark text-[#215558]"></i>
            </button>
          </div>

          {{-- VALIDATIE FOUTEN --}}
          <div id="create-errors" class="hidden mb-2 text-sm text-red-600"></div>

          <form
            id="create-user-form"
            method="post"
            hx-post="{{ route('support.gebruikers.store') }}"
            hx-swap="none"
            hx-headers='{"X-Requested-With":"XMLHttpRequest"}'
            data-store-url="{{ route('support.gebruikers.store') }}"
            data-list-url="{{ route('support.gebruikers.klanten') }}"
            hx-on::after-request="
              const xhr = event.detail.xhr;
              const err = document.getElementById('create-errors');

              // reset errors
              if (err) { err.classList.add('hidden'); err.innerHTML=''; }

              // validation errors
              if (xhr.status === 422) {
                try {
                  const json = JSON.parse(xhr.responseText);
                  if (err && json?.errors) {
                    err.innerHTML = Object.values(json.errors).flat().map(m=>`<div>• ${m}</div>`).join('');
                    err.classList.remove('hidden');
                  }
                } catch(e) {}
                return;
              }

              // forbidden
              if (xhr.status === 403) {
                if (err) { err.textContent = 'Je hebt geen rechten om gebruikers aan te maken.'; err.classList.remove('hidden'); }
                return;
              }

              // success
              if (xhr.status >= 200 && xhr.status < 300) {
                // close panels
                if (window.closeCreatePanel) window.closeCreatePanel();
                if (window.closeAddUserPanel) window.closeAddUserPanel();

                // hide detail
                const detail = document.getElementById('user-detail-card');
                if (detail) { detail.classList.add('hidden'); detail.innerHTML = ''; }

                // refresh list (respect current search term)
                const listEl = document.getElementById('users-list');
                const base = document.getElementById('create-user-form')?.dataset?.listUrl || '';
                const term = (document.getElementById('users-search')?.value || '').trim();
                let url = base;
                if (term) url += (url.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(term);

                if (window.htmx && url) {
                  htmx.ajax('GET', url, {
                    target: '#users-list',
                    swap: 'innerHTML transition:true',
                    headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }
                  });
                }

                // reset form
                document.getElementById('create-user-form')?.reset();
              }
            "
          >
            @csrf

            {{-- Dit veld zorgt dat je app.blade menu logic meteen klopt --}}
            <input type="hidden" name="rol" id="role-hidden" value="klant">

            <div class="grid gap-3">
              <div id="field-name">
                <label class="block text-xs text-[#215558] opacity-70 mb-1">Naam</label>
                <input
                  name="name"
                  type="text"
                  required
                  autocomplete="name"
                  class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                  placeholder="Voor- en achternaam"
                >
              </div>

              <div id="field-email">
                <label class="block text-xs text-[#215558] opacity-70 mb-1">E-mail</label>
                <input
                  name="email"
                  type="email"
                  required
                  autocomplete="email"
                  class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                  placeholder="naam@bedrijf.nl"
                >
              </div>

              {{-- Extra rol-keuze alleen zichtbaar bij medewerker --}}
              <div id="role-select-wrap" class="hidden">
                <label class="block text-xs text-[#215558] opacity-70 mb-1">Rol</label>

                <div id="role-dropdown" class="relative" data-open="false">
                  <button
                    type="button"
                    id="role-dropdown-button"
                    class="w-full py-3 px-4 text-sm rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300
                           flex items-center justify-between gap-3"
                    aria-haspopup="listbox"
                    aria-expanded="false"
                  >
                    <span class="flex items-center gap-2">
                      <i id="role-btn-icon" class="min-w-[16px] fa-solid fa-building-user text-[#215558]"></i>
                      <span id="role-btn-text" class="text-sm text-[#215558] font-semibold">Medewerker</span>
                    </span>
                    <svg class="w-4 h-4 opacity-70 flex-shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </button>

                  <ul
                    id="role-options"
                    class="absolute z-40 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-1
                           max-h-56 flex flex-col gap-1 overflow-auto hidden"
                    role="listbox"
                  >
                    <li role="option" data-value="medewerker" aria-selected="true"
                        class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                               hover:bg-gray-200 transition duration-300 outline-none bg-gray-200">
                      <i class="min-w-[16px] fa-solid fa-building-user text-[#215558]"></i>
                      <span class="text-sm text-[#215558] font-semibold">Medewerker</span>
                    </li>

                    <li role="option" data-value="admin" aria-selected="false"
                        class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                               hover:bg-gray-200 transition duration-300 outline-none">
                      <i class="min-w-[16px] fa-solid fa-user-shield text-[#215558]"></i>
                      <span class="text-sm text-[#215558] font-semibold">Admin</span>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="flex items-center gap-2 mt-3">
                <button
                  type="submit"
                  id="create-submit"
                  class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
                >
                  Opslaan
                </button>

                <button
                  type="button"
                  id="create-cancel"
                  class="bg-gray-200 hover:bg-gray-300 cursor-pointer text-center w-full text-gray-600 text-base font-semibold px-6 py-3 rounded-full transition duration-300"
                >
                  Annuleren
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    {{-- Search --}}
    <div id="search-bar" class="mb-5 bg-gray-100 rounded-xl border border-gray-200 px-3 flex items-center">
      <div class="w-[16px]">
        <i class="fa-solid fa-magnifying-glass fa-sm text-gray-500"></i>
      </div>
      <input
        id="users-search"
        type="text"
        placeholder="Zoeken op naam of e-mail"
        class="py-2 w-[calc(100%-16px)] text-sm text-gray-500 pl-3 font-medium outline-none"
      >
    </div>

    {{-- Tabs (alleen 1) --}}
    <div class="flex flex-col gap-1">
      <a
        role="menuitem"
        class="tab-gebruikers cursor-pointer w-full p-3 rounded-xl flex items-center gap-2 bg-gray-200 transition duration-300"
        hx-get="{{ route('support.gebruikers.klanten') }}"
        hx-target="#users-list"
        hx-swap="innerHTML transition:true"
        hx-push-url="true"
        data-url="{{ route('support.gebruikers.klanten') }}"
        aria-current="page"
      >
        <i class="min-w-[16px] fa-solid fa-users text-[#215558] fa-sm"></i>
        <p class="text-sm text-[#215558] font-semibold">Gebruikers</p>
      </a>
    </div>
  </div>

  {{-- MIDDEN: LIJST --}}
  <div class="col-span-1 p-3 h-fit bg-white rounded-xl">
    <div
      id="users-list"
      hx-get="{{ route('support.gebruikers.klanten') }}"
      hx-trigger="load"
      hx-swap="innerHTML transition:true"
    >
      <div class="w-full h-10 rounded-xl bg-gray-200 animate-pulse"></div>
    </div>
  </div>

  {{-- RECHTS: DETAIL --}}
  <div id="user-detail-card" class="hidden col-span-1 bg-white rounded-xl h-full min-h-0 flex flex-col"></div>

  {{-- CONFIRM DELETE MODAL --}}
  <div
    id="confirm-delete-overlay"
    class="fixed inset-0 z-50 opacity-0 pointer-events-none transition-opacity duration-200 ease-out
           flex items-center justify-center px-4 hidden"
  >
    <div id="confirm-delete-backdrop" class="absolute inset-0 bg-black/25 opacity-0 transition-opacity duration-200 ease-out"></div>

    <div
      id="confirm-delete-card"
      class="relative z-10 w-[420px] max-w-[92vw] bg-white rounded-2xl shadow-xl border border-gray-200 p-4
             transform-gpu translate-y-2 opacity-0 transition-all duration-200 ease-out"
    >
      <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-triangle-exclamation text-red-500"></i>
        </div>
        <div class="flex-1">
          <h2 class="text-base font-black text-[#215558]">Gebruiker verwijderen?</h2>
          <p id="confirm-delete-text" class="mt-1 text-sm text-[#215558]">
            Weet je zeker dat je deze gebruiker wilt verwijderen?
          </p>
        </div>
      </div>

      <div id="confirm-delete-error" class="mt-3 hidden text-sm text-red-600"></div>

      <div class="mt-4 flex items-center gap-2">
        <button
          type="button"
          id="confirm-delete-yes"
          class="bg-red-500 hover:bg-red-600 cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
        >
          <span class="inline-flex items-center gap-2">
            <span class="confirm-spinner hidden"><i class="fa-solid fa-spinner fa-spin"></i></span>
            Verwijderen
          </span>
        </button>

        <button
          type="button"
          id="confirm-delete-cancel"
          class="bg-gray-200 hover:bg-gray-300 text-gray-700 cursor-pointer font-semibold px-6 py-3 rounded-full transition duration-300"
        >
          Annuleren
        </button>
      </div>
    </div>
  </div>

  <script>
    (function () {
      function $(sel, root=document) { return root.querySelector(sel); }

      // CSRF voor non-GET
      document.body.addEventListener('htmx:configRequest', function (e) {
        if (String(e.detail.verb || '').toUpperCase() !== 'GET') {
          const token = document.querySelector('meta[name="csrf-token"]')?.content;
          if (token) e.detail.headers['X-CSRF-TOKEN'] = token;
        }
      });

      // Panels
      const addMenuRoot = $('#add-user-menu');
      const btn = addMenuRoot?.querySelector('button[aria-controls="add-user-panel"]');
      const panel = $('#add-user-panel');
      const createPanel = $('#create-user-panel');

      const openClasses = ['opacity-100','translate-x-0'];
      const closedClasses = ['opacity-0','translate-x-1','pointer-events-none'];

      function openAddPanel() {
        panel?.classList.remove(...closedClasses);
        panel?.classList.add(...openClasses);
        btn?.setAttribute('aria-expanded','true');
      }
      function closeAddPanel() {
        panel?.classList.remove(...openClasses);
        panel?.classList.add(...closedClasses);
        btn?.setAttribute('aria-expanded','false');
      }
      function openCreatePanel() {
        createPanel?.classList.remove(...closedClasses);
        createPanel?.classList.add(...openClasses);
      }
      function closeCreatePanel() {
        createPanel?.classList.remove(...openClasses);
        createPanel?.classList.add(...closedClasses);
      }

      window.closeAddUserPanel = closeAddPanel;
      window.closeCreatePanel = closeCreatePanel;

      btn?.addEventListener('click', (e) => {
        e.stopPropagation();
        const expanded = btn.getAttribute('aria-expanded') === 'true';
        if (expanded) closeAddPanel(); else openAddPanel();
      });

      document.addEventListener('click', (e) => {
        if (addMenuRoot && !addMenuRoot.contains(e.target)) {
          closeAddPanel();
          closeCreatePanel();
        }
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          closeAddPanel();
          closeCreatePanel();
        }
      });

      // Create logic
      const form = $('#create-user-form');
      const title = $('#create-title');
      const roleHidden = $('#role-hidden');
      const roleWrap = $('#role-select-wrap');

      const roleDdRoot = $('#role-dropdown');
      const roleDdBtn = $('#role-dropdown-button');
      const roleDdList = $('#role-options');

      function openRoleDd() {
        roleDdList?.classList.remove('hidden');
        if (roleDdRoot) roleDdRoot.dataset.open = 'true';
        roleDdBtn?.setAttribute('aria-expanded','true');
      }
      function closeRoleDd() {
        roleDdList?.classList.add('hidden');
        if (roleDdRoot) roleDdRoot.dataset.open = 'false';
        roleDdBtn?.setAttribute('aria-expanded','false');
      }
      function setRoleValue(val) {
        if (roleHidden) roleHidden.value = val;

        const btnText = $('#role-btn-text');
        const btnIcon = $('#role-btn-icon');

        const items = roleDdList?.querySelectorAll('[role="option"][data-value]') || [];
        items.forEach(li => {
          const active = li.dataset.value === val;
          li.setAttribute('aria-selected', active ? 'true' : 'false');
          li.classList.toggle('bg-gray-200', active);
          if (active) {
            const label = (li.querySelector('span')?.textContent || '').trim();
            if (btnText) btnText.textContent = label || 'Rol';
            const liIcon = li.querySelector('i');
            if (liIcon && btnIcon) {
              btnIcon.className = liIcon.className;
              if (!btnIcon.classList.contains('min-w-[16px]')) btnIcon.classList.add('min-w-[16px]');
              if (!btnIcon.classList.contains('text-[#215558]')) btnIcon.classList.add('text-[#215558]');
            }
          }
        });
      }

      roleDdBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        const isOpen = roleDdRoot?.dataset.open === 'true';
        if (isOpen) closeRoleDd(); else openRoleDd();
      });

      roleDdList?.addEventListener('click', (e) => {
        const li = e.target.closest('[role="option"][data-value]');
        if (!li) return;
        setRoleValue(li.dataset.value);
        closeRoleDd();
      });

      document.addEventListener('click', (e) => {
        if (roleDdRoot && !roleDdRoot.contains(e.target)) closeRoleDd();
      });

      function openCreate(type) {
        // reset form + errors
        form?.reset();
        const err = $('#create-errors');
        if (err) { err.classList.add('hidden'); err.innerHTML=''; }

        openAddPanel();
        openCreatePanel();

        if (type === 'klant') {
          if (title) title.textContent = 'Nieuwe klant';
          if (roleWrap) roleWrap.classList.add('hidden');
          setRoleValue('klant');
        }

        if (type === 'medewerker') {
          if (title) title.textContent = 'Nieuwe medewerker';
          if (roleWrap) roleWrap.classList.remove('hidden');
          setRoleValue('medewerker'); // default
        }
      }

      $('#open-create-klant')?.addEventListener('click', (e) => {
        e.preventDefault();
        openCreate('klant');
      });

      $('#open-create-medewerker')?.addEventListener('click', (e) => {
        e.preventDefault();
        openCreate('medewerker');
      });

      $('#create-cancel')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeCreatePanel();
      });

      $('#create-cancel-x')?.addEventListener('click', (e) => {
        e.preventDefault();
        closeCreatePanel();
      });

      // List selection -> detail load feel
      document.body.addEventListener('htmx:beforeRequest', (evt) => {
        const path = evt.detail?.path || '';
        const isDetailReq = /\/(gebruikers|klanten|medewerkers)\/\d+(?:\?.*)?$/i.test(path);
        if (isDetailReq) {
          const card = $('#user-detail-card');
          if (card) {
            card.classList.remove('hidden');
            card.innerHTML = '<div class="p-3"><div class="w-full h-10 rounded-xl bg-gray-200 animate-pulse"></div></div>';
          }
        }
      });

      document.body.addEventListener('htmx:afterSwap', (evt) => {
        if (evt.target?.id === 'users-list') {
          const onDetailUrl = /\/(gebruikers|klanten|medewerkers)\/\d+(?:\?.*)?$/i.test(location.pathname);
          if (!onDetailUrl) {
            const card = $('#user-detail-card');
            if (card) { card.classList.add('hidden'); card.innerHTML = ''; }
          }
        }
        if (evt.target?.id === 'user-detail-card') {
          $('#user-detail-card')?.classList.remove('hidden');
        }
      });

      // Search (debounced)
      const searchInput = $('#users-search');
      let t = null;

      function runSearch(force=false) {
        const term = (searchInput?.value || '').trim();
        const base = document.querySelector('.tab-gebruikers')?.getAttribute('data-url') || '';
        if (!base || !window.htmx) return;

        let url = base;
        if (term.length || force) {
          url = term.length ? (base + (base.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(term)) : base;
        }

        htmx.ajax('GET', url, {
          target: '#users-list',
          swap: 'innerHTML transition:true',
          headers: { 'X-Requested-With':'XMLHttpRequest', 'Accept':'text/html' }
        });
      }

      searchInput?.addEventListener('input', () => {
        clearTimeout(t);
        t = setTimeout(() => {
          const v = (searchInput.value || '').trim();
          if (!v) return runSearch(true);
          runSearch(false);
        }, 300);
      });

      searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); runSearch(true); }
        if (e.key === 'Escape') {
          if (searchInput.value) {
            searchInput.value = '';
            runSearch(true);
          }
        }
      });

      // Delete modal (exposed for partials)
      const delOverlay  = $('#confirm-delete-overlay');
      const delBackdrop = $('#confirm-delete-backdrop');
      const delCard     = $('#confirm-delete-card');
      const delYes      = $('#confirm-delete-yes');
      const delCancel   = $('#confirm-delete-cancel');
      const delError    = $('#confirm-delete-error');
      const spinner     = delYes?.querySelector('.confirm-spinner');

      let pendingDeleteUrl = null;

      function showDeleteOverlay(url) {
        pendingDeleteUrl = url;

        delOverlay?.classList.remove('hidden');
        delOverlay?.classList.remove('pointer-events-none');
        delOverlay?.classList.add('opacity-0');
        delBackdrop?.classList.add('opacity-0');
        delCard?.classList.add('opacity-0','translate-y-2');

        if (delError) { delError.classList.add('hidden'); delError.textContent = ''; }
        spinner?.classList.add('hidden');
        delYes?.removeAttribute('disabled');

        requestAnimationFrame(() => {
          delOverlay?.classList.remove('opacity-0');
          delBackdrop?.classList.remove('opacity-0');
          delCard?.classList.remove('opacity-0','translate-y-2');
        });
      }

      function hideDeleteOverlay() {
        delOverlay?.classList.add('opacity-0');
        delBackdrop?.classList.add('opacity-0');
        delCard?.classList.add('opacity-0','translate-y-2');

        setTimeout(() => {
          delOverlay?.classList.add('hidden');
          delOverlay?.classList.add('pointer-events-none');
          delOverlay?.classList.remove('opacity-0');
          delBackdrop?.classList.remove('opacity-0');
          delCard?.classList.remove('opacity-0','translate-y-2');
          pendingDeleteUrl = null;
        }, 220);
      }

      window.openDeleteConfirm = function(btn) {
        const url = btn.getAttribute('data-delete-url');
        if (!url) return;
        showDeleteOverlay(url);
      };

      delYes?.addEventListener('click', () => {
        if (!pendingDeleteUrl || !window.htmx) return;

        delYes.setAttribute('disabled','true');
        spinner?.classList.remove('hidden');

        htmx.ajax('DELETE', pendingDeleteUrl, {
          target: '#users-list',
          swap: 'innerHTML transition:true',
          headers: {
            'X-Requested-With':'XMLHttpRequest',
            'Accept':'text/html'
          }
        }).addEventListener('loadend', (xhr) => {
          spinner?.classList.add('hidden');
          delYes.removeAttribute('disabled');

          if (xhr.status >= 200 && xhr.status < 300) {
            hideDeleteOverlay();
            const detail = $('#user-detail-card');
            if (detail) { detail.classList.add('hidden'); detail.innerHTML=''; }
          } else {
            if (delError) {
              delError.textContent = 'Verwijderen is mislukt. Probeer het nogmaals.';
              delError.classList.remove('hidden');
            }
          }
        });
      });

      delCancel?.addEventListener('click', hideDeleteOverlay);
      delOverlay?.addEventListener('click', (e) => {
        if (delCard && !delCard.contains(e.target)) hideDeleteOverlay();
      });
    })();
  </script>
@endsection
