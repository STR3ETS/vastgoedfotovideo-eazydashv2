@extends('hub.layouts.app')

@section('content')
  @php
    $activeRole = $activeRole ?? request()->query('rol', 'klant');
    $activeRoleLabel = $activeRoleLabel ?? ($activeRole);

    $q = $q ?? request()->query('q', '');
    $listUrl = route('support.gebruikers.lijst', ['rol' => $activeRole]);
  @endphp

  {{-- LINKS --}}
  <div class="col-span-1 p-4 h-full bg-white rounded-xl">
    <div class="mb-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <h1 class="text-xl text-[#215558] font-black">Gebruikers</h1>
      </div>

      @if((auth()->user()->rol ?? null) === 'admin')
        <div class="relative" id="add-user-menu">
          <button type="button"
                  id="open-create-user"
                  class="w-8 h-8 bg-gray-200 hover:bg-gray-300 transition duration-300 cursor-pointer rounded-full flex items-center justify-center relative group"
                  aria-haspopup="dialog" aria-expanded="false" aria-controls="create-user-panel">
            <i class="fa-solid fa-plus text-[#215558]"></i>

            <div
              class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] left-0
                      opacity-0 invisible translate-y-1 pointer-events-none
                      group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                      transition-all duration-300 ease-out z-10">
              <p class="text-[#215558] text-xs font-semibold whitespace-nowrap">Gebruiker toevoegen</p>
            </div>
          </button>

          {{-- Create panel --}}
          <div id="create-user-panel"
              class="absolute z-30 top-0 left-[135%] w-[380px] p-3 rounded-xl bg-white border border-gray-200 shadow-lg
                      transition duration-300 ease-out transform-gpu
                      opacity-0 translate-x-1 pointer-events-none">

            <div class="flex items-center justify-between mb-3">
              <p class="text-base text-[#215558] font-black">Nieuwe gebruiker</p>
              <button type="button" id="create-close"
                      class="w-8 h-8 rounded-full hover:bg-gray-100 transition duration-200 flex items-center justify-center">
                <i class="fa-solid fa-xmark text-[#215558]"></i>
              </button>
            </div>

            @php
              $roleOptions = [
                ['value' => 'admin',          'label' => 'Admin',         'icon' => 'fa-user-shield'],
                ['value' => 'klant',          'label' => 'Klant',         'icon' => 'fa-user'],
                ['value' => 'team-manager',   'label' => 'Team manager',  'icon' => 'fa-users-gear'],
                ['value' => 'client-manager', 'label' => 'Klant manager', 'icon' => 'fa-handshake'],
                ['value' => 'fotograaf',      'label' => 'Fotograaf',     'icon' => 'fa-camera'],
              ];
            @endphp

            <div id="create-errors" class="hidden mb-2 text-sm text-red-600"></div>

            <form
              id="create-user-form"
              method="post"
              hx-post="{{ route('support.gebruikers.store') }}"
              hx-target="#users-list"
              hx-swap="innerHTML transition:true"
              hx-headers='{"X-Requested-With":"XMLHttpRequest","Accept":"application/json"}'
            >
              @csrf

              {{-- Rol dropdown --}}
              <div class="mb-3">
                <label class="block text-xs font-semibold text-gray-500 mb-1">Rol</label>

                <input type="hidden" name="rol" id="create-role-hidden-form" value="{{ $activeRole }}">

                <div class="relative" id="create-role-dd" data-open="false">
                  <button type="button"
                          id="create-role-btn"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none
                                focus:ring-2 focus:ring-[#0F9B9F]/30 flex items-center justify-between gap-3">
                    <span class="flex items-center gap-2 min-w-0">
                      <i id="create-role-icon" class="fa-solid fa-user text-[#215558]"></i>
                      <span id="create-role-label" class="font-semibold text-[#215558] truncate"></span>
                    </span>
                    <i class="fa-solid fa-chevron-down text-gray-400"></i>
                  </button>

                  <div id="create-role-menu"
                      class="absolute left-0 right-0 mt-2 bg-white border border-gray-200 rounded-xl shadow-lg p-1 hidden z-40">
                    @foreach($roleOptions as $opt)
                      <button type="button"
                              class="w-full px-3 py-2 rounded-lg text-sm flex items-center gap-2 hover:bg-gray-100 transition"
                              data-role-value="{{ $opt['value'] }}"
                              data-role-label="{{ $opt['label'] }}"
                              data-role-icon="{{ $opt['icon'] }}">
                        <i class="fa-solid {{ $opt['icon'] }} text-[#215558] w-4"></i>
                        <span class="font-semibold text-[#215558]">{{ $opt['label'] }}</span>
                      </button>
                    @endforeach
                  </div>
                </div>
              </div>

              <div class="grid gap-2">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Naam</label>
                  <input name="name" type="text" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30">
                </div>

                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">E-mail</label>
                  <input name="email" type="email" required
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30">
                </div>

                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Adres</label>
                  <input name="address" type="text"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30">
                </div>

                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Postcode</label>
                    <input name="postal_code" type="text"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Stad</label>
                    <input name="city" type="text"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30">
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Provincie</label>
                    <input name="state_province" type="text"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30">
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Telefoonnummer</label>
                    <input name="phone" type="text"
                          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30">
                  </div>
                </div>

                <div class="flex items-center gap-2 mt-2">
                  <button type="submit" id="create-submit"
                          class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
                    Opslaan
                  </button>
                  <button type="button" id="create-cancel"
                          class="bg-gray-200 hover:bg-gray-300 cursor-pointer text-center w-full text-gray-600 text-base font-semibold px-6 py-3 rounded-full transition duration-300">
                    Annuleren
                  </button>
                </div>
              </div>
            </form>

          </div>
        </div>
      @endif
    </div>

    <div id="search-bar" class="mb-5 bg-gray-100 rounded-xl border border-gray-200 px-3 flex items-center">
      <div class="w-[16px]">
        <i class="fa-solid fa-magnifying-glass fa-sm text-gray-500"></i>
      </div>
      <input id="users-search" type="text" value="{{ $q }}" placeholder="Zoek op naam of e-mail"
            class="py-2 w-[calc(100%-16px)] text-sm text-gray-600 pl-3 font-medium outline-none bg-transparent">
    </div>
  </div>

  {{-- MIDDEN: LIJST --}}
  <div class="col-span-1 p-3 h-fit bg-white rounded-xl">
    <div id="users-list"
        hx-get="{{ $listUrl }}{{ $q !== '' ? ('?q=' . urlencode($q)) : '' }}"
        hx-trigger="load"
        hx-swap="innerHTML transition:true"
        data-list-url="{{ $listUrl }}"
        data-active-role="{{ $activeRole }}">
      <div class="w-full h-10 rounded-xl bg-gray-200 animate-pulse"></div>
    </div>
  </div>

  {{-- RECHTS: DETAIL --}}
  <div id="user-detail-card" class="hidden overflow-scroll col-span-2 bg-white rounded-xl h-full min-h-0 flex flex-col"></div>

  <script>
    (function () {
      const btn = document.getElementById('open-create-user');
      const panel = document.getElementById('create-user-panel');
      const closeBtn = document.getElementById('create-close');
      const cancelBtn = document.getElementById('create-cancel');
      const errors = document.getElementById('create-errors');

      const hiddenForm = document.getElementById('create-role-hidden-form');
      const ddRoot = document.getElementById('create-role-dd');
      const ddBtn  = document.getElementById('create-role-btn');
      const ddMenu = document.getElementById('create-role-menu');
      const ddLabel = document.getElementById('create-role-label');
      const ddIcon  = document.getElementById('create-role-icon');

      const ROLE_MAP = {
        'admin':          { label: 'Admin',         icon: 'fa-user-shield' },
        'klant':          { label: 'Klant',         icon: 'fa-user' },
        'team-manager':   { label: 'Team manager',  icon: 'fa-users-gear' },
        'client-manager': { label: 'Klant manager', icon: 'fa-handshake' },
        'fotograaf':      { label: 'Fotograaf',     icon: 'fa-camera' },
      };

      function setCreateRole(value) {
        if (!value) value = 'klant';
        if (hiddenForm) hiddenForm.value = value;

        const cfg = ROLE_MAP[value] || { label: value, icon: 'fa-user' };
        if (ddLabel) ddLabel.textContent = cfg.label;
        if (ddIcon) ddIcon.className = `fa-solid ${cfg.icon} text-[#215558]`;
      }

      function openRoleMenu() {
        if (!ddMenu || !ddRoot) return;
        ddMenu.classList.remove('hidden');
        ddRoot.dataset.open = 'true';
      }
      function closeRoleMenu() {
        if (!ddMenu || !ddRoot) return;
        ddMenu.classList.add('hidden');
        ddRoot.dataset.open = 'false';
      }

      ddBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        if ((ddRoot?.dataset.open || 'false') === 'true') closeRoleMenu();
        else openRoleMenu();
      });

      ddMenu?.addEventListener('click', (e) => {
        const opt = e.target.closest('button[data-role-value]');
        if (!opt) return;
        setCreateRole(opt.dataset.roleValue);
        closeRoleMenu();
      });

      document.addEventListener('click', (e) => {
        if (ddRoot && !ddRoot.contains(e.target)) closeRoleMenu();
      });

      function openPanel() {
        if (!panel) return;

        const active = document.getElementById('users-list')?.dataset.activeRole || 'klant';
        setCreateRole(active);

        panel.classList.remove('opacity-0','translate-x-1','pointer-events-none');
        panel.classList.add('opacity-100','translate-x-0');
        btn?.setAttribute('aria-expanded','true');

        errors?.classList.add('hidden');
        if (errors) errors.innerHTML = '';

        const name = panel.querySelector('input[name="name"]');
        setTimeout(() => name?.focus(), 50);
      }

      function closePanel() {
        if (!panel) return;
        panel.classList.add('opacity-0','translate-x-1','pointer-events-none');
        panel.classList.remove('opacity-100','translate-x-0');
        btn?.setAttribute('aria-expanded','false');
        closeRoleMenu();
      }

      btn?.addEventListener('click', openPanel);
      closeBtn?.addEventListener('click', closePanel);
      cancelBtn?.addEventListener('click', closePanel);

      document.body.addEventListener('htmx:afterRequest', (e) => {
        if (e.target?.id !== 'create-user-form') return;

        const xhr = e.detail.xhr;

        if (xhr.status === 200) {
          const createdRole = (hiddenForm?.value || '').trim() || 'klant';
          const currentRole = (document.getElementById('users-list')?.dataset.activeRole || 'klant').trim();

          closePanel();

          const detail = document.getElementById('user-detail-card');
          if (detail) { detail.classList.add('hidden'); detail.innerHTML = ''; }

          if (createdRole && createdRole !== currentRole) {
            const params = new URLSearchParams(window.location.search);
            params.set('rol', createdRole);
            params.delete('q');
            window.location.href = window.location.pathname + '?' + params.toString();
          }
        }

        if (xhr.status === 422) {
          try {
            const data = JSON.parse(xhr.responseText);
            const msgs = Object.values(data.errors || {}).flat();
            if (errors) {
              errors.innerHTML = msgs.join('<br>');
              errors.classList.remove('hidden');
            }
          } catch (_) {}
        }

        if (xhr.status === 403) {
          if (errors) {
            errors.innerHTML = 'Je hebt geen rechten om gebruikers aan te maken.';
            errors.classList.remove('hidden');
          }
        }
      });

      setCreateRole(document.getElementById('users-list')?.dataset.activeRole || 'klant');
      window.closeCreatePanel = closePanel;
    })();
  </script>
@endsection
