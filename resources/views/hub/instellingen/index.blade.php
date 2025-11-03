@extends('layouts.app')

@section('content')
  {{-- LINKER KOLOM (tabs + search) --}}
  <div class="col-span-1 p-4 h-full bg-white rounded-xl">
    <h1 id="i18n-settings-title" class="text-xl text-[#215558] font-black mb-4">
      {{ __('instellingen.title') }}
    </h1>

    <div id="search-bar" class="mb-5 bg-gray-100 rounded-xl border border-gray-200 px-3 flex items-center">
      <div class="w-4">
        <i class="fa-solid fa-magnifying-glass fa-sm text-gray-500"></i>
      </div>
      <input
        id="users-search"
        type="text"
        placeholder="{{ __('instellingen.search_placeholder') }}"
        class="py-2 w-[calc(100%-16px)] text-sm text-gray-500 pl-3 font-medium outline-none"
      >
    </div>

    {{-- Tabs (zelfde gedrag/klassennamen als bij Gebruikers) --}}
    <div class="flex flex-col gap-1">
      <a  role="menuitem"
          class="tab-personal cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 bg-gray-200 transition duration-300"
          hx-get="{{ route('support.instellingen.personal') }}"
          hx-target="#settings-pane"
          hx-swap="innerHTML transition:true"
          hx-push-url="true"
          data-url="{{ route('support.instellingen.personal') }}"
          aria-current="page">
        <i class="min-w-[16px] fa-solid fa-user text-[#215558] fa-sm"></i>
        <p id="i18n-tab-personal" class="px-1 text-sm text-[#215558] font-semibold">
          {{ __('instellingen.tabs.personal') }}
        </p>
      </a>

      @if ($user->rol === 'klant' && $user->is_company_admin)        
        <a  role="menuitem"
            class="tab-company cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
            hx-get="{{ route('support.instellingen.company') }}"
            hx-target="#settings-pane"
            hx-swap="innerHTML transition:true"
            hx-push-url="true"
            data-url="{{ route('support.instellingen.company') }}">
          <i class="min-w-[16px] fa-solid fa-building text-[#215558] fa-sm"></i>
          <p id="i18n-tab-company" class="px-1 text-sm text-[#215558] font-semibold">
            {{ __('instellingen.tabs.company') }}
          </p>
        </a>

        <a  role="menuitem"
            class="tab-team cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
            hx-get="{{ route('support.instellingen.team') }}"
            hx-target="#settings-pane"
            hx-swap="innerHTML transition:true"
            hx-push-url="true"
            data-url="{{ route('support.instellingen.team') }}">
          <i class="min-w-[16px] fa-solid fa-user-plus text-[#215558] fa-sm"></i>
          <p id="i18n-tab-team" class="px-1 text-sm text-[#215558] font-semibold">
            {{ __('instellingen.tabs.team') }}
          </p>
        </a>

        <a  role="menuitem"
            class="tab-billing cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
            hx-get="{{ route('support.instellingen.billing') }}"
            hx-target="#settings-pane"
            hx-swap="innerHTML transition:true"
            hx-push-url="true"
            data-url="{{ route('support.instellingen.billing') }}">
          <i class="min-w-[16px] fa-solid fa-credit-card text-[#215558] fa-sm"></i>
          <p id="i18n-tab-billing" class="px-1 text-sm text-[#215558] font-semibold">
            {{ __('instellingen.tabs.billing') }}
          </p>
        </a>

        <a  role="menuitem"
            class="tab-docs cursor-pointer w-full p-3 rounded-xl flex items-center gap-1 hover:bg-gray-200 transition duration-300"
            hx-get="{{ route('support.instellingen.documents') }}"
            hx-target="#settings-pane"
            hx-swap="innerHTML transition:true"
            hx-push-url="true"
            data-url="{{ route('support.instellingen.documents') }}">
          <i class="min-w-[16px] fa-solid fa-file text-[#215558] fa-sm"></i>
          <p id="i18n-tab-docs" class="px-1 text-sm text-[#215558] font-semibold">
            {{ __('instellingen.tabs.documents') }}
          </p>
        </a>
      @endif
    </div>
  </div>

  {{-- RECHTER PANEEL: PARTIALS CONTAINER --}}
  <div class="col-span-2 p-0 bg-white rounded-xl h-fit">
    <div id="settings-pane"
         hx-get="{{ route('support.instellingen.personal') }}"
         hx-trigger="load"
         hx-swap="innerHTML transition:true">
      {{-- skeleton loader --}}
      <div class="p-4">
        <div class="w-full h-10 rounded-xl bg-gray-200 animate-pulse mb-3"></div>
        <div class="w-2/3 h-10 rounded-xl bg-gray-200 animate-pulse mb-3"></div>
        <div class="w-1/2 h-10 rounded-xl bg-gray-200 animate-pulse"></div>
      </div>
    </div>
  </div>

  {{-- JS: exact zelfde tab/zoek-gedrag als bij Gebruikers --}}
  @verbatim
  <script>
    (function () {
      // Zelfde MENU_SELECTOR als in Gebruikers
      const MENU_SELECTOR = '.flex.flex-col.gap-1';

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

      // Delegate clicks voor tabs
      document.addEventListener('click', (e) => {
        const tab = e.target.closest('a[role="menuitem"][hx-get]');
        if (tab) setActiveTab(tab);
      });

      // Search: zelfde aanpak als gebruikers (stuurt ?q= naar de actieve tab-URL)
      const searchInput = document.querySelector('#users-search');

      function debounce(fn, ms=300){
        let t; return (...args)=>{ clearTimeout(t); t=setTimeout(()=>fn(...args), ms); };
      }

      function getActiveTab(){
        return document.querySelector(
          '.tab-company[aria-current="page"], .tab-team[aria-current="page"], .tab-billing[aria-current="page"], .tab-docs[aria-current="page"], .tab-personal[aria-current="page"]'
        ) || document.querySelector('.tab-personal') || document.querySelector('a[role="menuitem"][hx-get]');
      }

      function getBaseUrl(tabEl=getActiveTab()){
        if (!tabEl) return '';
        return tabEl.getAttribute('data-url') || tabEl.getAttribute('hx-get') || '';
        }

      function runSearch(force=false){
        if (!searchInput) return;
        const term = (searchInput.value || '').trim();
        const tab  = getActiveTab();
        const base = getBaseUrl(tab);
        if (!base) return;

        let url = base;
        if (term.length || force) {
          const sep = url.includes('?') ? '&' : '?';
          url = term.length ? `${url}${sep}q=${encodeURIComponent(term)}` : base; // leeg = reset
        }

        htmx.ajax('GET', url, {
          target: '#settings-pane',
          swap: 'innerHTML transition:true',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        });
      }

      searchInput?.addEventListener('input', debounce(()=> runSearch(false), 300));
      searchInput?.addEventListener('keydown', (e)=>{
        if (e.key === 'Enter') { e.preventDefault(); runSearch(true); }
        if (e.key === 'Escape') {
          if (searchInput.value) {
            searchInput.value = '';
            runSearch(true);
          }
        }
      });

      // Als er wél een zoekterm staat en je klikt op een tab: laad meteen gefilterd
      document.addEventListener('click', (e)=>{
        const tab = e.target.closest('a[role="menuitem"][hx-get]');
        if (!tab) return;

        const term = (searchInput?.value || '').trim();
        if (!term) return; // geen zoekterm -> HTMX doet normaal

        e.preventDefault();
        setActiveTab(tab);

        const base = tab.getAttribute('data-url') || tab.getAttribute('hx-get') || '';
        const sep  = base.includes('?') ? '&' : '?';
        const url  = `${base}${sep}q=${encodeURIComponent(term)}`;

        htmx.ajax('GET', url, {
          target: '#settings-pane',
          swap: 'innerHTML transition:true',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        });
      });
    })();
  </script>
  @endverbatim
@endsection
