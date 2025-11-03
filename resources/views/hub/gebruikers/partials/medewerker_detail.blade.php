<div class="w-full h-fit p-5 relative" id="medewerker-detail-card" data-current-id="{{ $medewerker->id }}">
  <form
    hx-patch="{{ route('support.gebruikers.medewerkers.update', $medewerker) }}"
    hx-target="#medewerker-detail-card"
    hx-swap="outerHTML transition:true"
    class="grid gap-4"
  >
    @csrf
    @method('PATCH')

    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-full bg-[#3b8b8f] flex items-center justify-center">
        <span class="text-sm font-semibold text-[#215558]">
          @php
            $naam = trim((string) ($medewerker->name ?? ''));
            $init = '?';
            if ($naam !== '') {
              $parts = preg_split('/\s+/u', $naam, -1, PREG_SPLIT_NO_EMPTY);
              $tussenvoegsels = ['van','de','der','den','het',"'t",'te','ten','ter','op','aan','bij',"van't",'van','van de','van der','van den'];
              $filtered = array_values(array_filter($parts, fn($p) => !in_array(mb_strtolower($p), $tussenvoegsels, true)));
              $first = $filtered[0] ?? ($parts[0] ?? '');
              $last  = $filtered[count($filtered)-1] ?? '';
              $init = mb_strtoupper($last === $first ? mb_substr($first,0,2) : (mb_substr($first,0,1).mb_substr($last,0,1)));
            }
          @endphp
          {{ $init }}
        </span>
      </div>
      <div class="min-w-0">
        <p class="text-lg text-[#215558] font-bold leading-tight truncate">{{ $medewerker->name }}</p>
        <p class="text-sm text-[#215558] opacity-70 truncate">{{ $medewerker->email }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 gap-3 mb-2">
      <div>
        <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('gebruikers.detail.fields.name') }}</label>
        <input name="name" type="text" value="{{ old('name', $medewerker->name) }}"
               class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('gebruikers.detail.fields.email') }}</label>
        <input name="email" type="email" value="{{ old('email', $medewerker->email) }}"
               class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>

      <div>
        <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('gebruikers.detail.fields.rol') }}</label>
        <select name="rol" class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
          <option value="medewerker" @selected(old('rol',$medewerker->rol)==='medewerker')>Medewerker</option>
          <option value="admin" @selected(old('rol',$medewerker->rol)==='admin')>Admin</option>
        </select>
        @error('rol') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
      </div>
    </div>

    <div class="flex items-center gap-2">
      <button type="submit" class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">{{ __('gebruikers.detail.save') }}</button>
    </div>
  </form>

  {{-- Floating actions (rechts gecentreerd) --}}
  <div id="detail-fab"
      class="absolute top-0 left-full ml-2 z-40 flex flex-col items-center gap-2">
    <button id="fab-close"
            class="min-w-6 min-h-6 max-w-6 max-h-6 rounded-full bg-white hover:bg-gray-200 transition duration-300 flex items-center justify-center cursor-pointer"
            aria-label="Sluiten">
      <i class="fa-solid fa-xmark fa-xs text-[#215558]"></i>
    </button>
    <button id="fab-prev"
            class="min-w-6 min-h-6 max-w-6 max-h-6 rounded-full bg-white hover:bg-gray-200 transition duration-300 flex items-center justify-center cursor-pointer"
            aria-label="Vorige">
      <i class="fa-solid fa-chevron-up fa-xs text-[#215558]"></i>
    </button>
    <button id="fab-next"
            class="min-w-6 min-h-6 max-w-6 max-h-6 rounded-full bg-white hover:bg-gray-200 transition duration-300 flex items-center justify-center cursor-pointer"
            aria-label="Volgende">
      <i class="fa-solid fa-chevron-down fa-xs text-[#215558]"></i>
    </button>
  </div>
</div>

<script>
(function(){
  // --- "Opgeslagen" feedback die je al had ---
  document.body.addEventListener('htmx:afterSwap', function (e) {
    if (!(e.target && e.target.id === 'medewerker-detail-card')) return;
    const status = e.detail && e.detail.xhr && e.detail.xhr.status;
    if (!(status >= 200 && status < 300)) return;

    const btn = document.querySelector('#medewerker-detail-card button[type="submit"]');
    if (!btn) return;

    if (!btn.dataset.origClass) {
      btn.dataset.origClass = btn.className;
      btn.dataset.origText  = btn.textContent.trim();
    }
    btn.innerHTML = '<i class="fa-solid fa-check text-white"></i>';
    btn.className = 'bg-green-500 cursor-default text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300';
    setTimeout(() => {
      if (btn.dataset.origClass) btn.className = btn.dataset.origClass;
      if (btn.dataset.origText)  btn.textContent = btn.dataset.origText;
    }, 3000);
  });

  // --- FAB logic (prev/next/close) ---
  const detailCard = document.getElementById('medewerker-detail-card');
  const currentId  = detailCard?.getAttribute('data-current-id');

  function getList() {
    return document.getElementById('medewerkers-list') || document.getElementById('users-list');
  }
  function getItems() {
    const list = getList();
    return list ? Array.from(list.querySelectorAll('.item-user')) : [];
  }
  function findCurrentItem() {
    const items = getItems();
    // probeer direct via data-id
    let el = items.find(x => x.getAttribute('data-id') === String(currentId));
    if (el) return el;
    // fallback: aria-current
    return items.find(x => x.getAttribute('aria-current') === 'true') || null;
  }
  function navigate(delta) {
    const items = getItems();
    if (!items.length) return;

    const current = findCurrentItem();
    let idx = current ? items.indexOf(current) : -1;

    if (idx === -1) {
      // geen match -> pak eerste of laatste afhankelijk van richting
      idx = (delta > 0) ? -1 : 0;
    }
    // wrap-around
    let nextIdx = idx + delta;
    if (nextIdx < 0) nextIdx = items.length - 1;
    if (nextIdx >= items.length) nextIdx = 0;

    const target = items[nextIdx];
    if (target) {
      // klik om HTMX-detail te laden + active state
      target.click();
      // scroll smooth in view
      target.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }
  function clearActiveListItem() {
    // haal active state weg in beide lijsten (voor het geval je klanten/medewerkers wisselt)
    document.querySelectorAll('#medewerkers-list .item-user, #klanten-list .item-user').forEach(el => {
      el.classList.remove('bg-gray-200');
      el.removeAttribute('aria-current');
    });
  }

  function closeDetail() {
    // 1) detail verbergen + leegmaken
    const panel = document.getElementById('user-detail-card');
    if (panel) {
      panel.classList.add('hidden');
      panel.innerHTML = '';
    }

    // 2) actieve item in de lijst resetten
    clearActiveListItem();

    // 3) floating buttons weghalen (optioneel, netjes opruimen)
    document.getElementById('detail-fab')?.remove();

    // 4) URL terugzetten naar medewerkers (of klanten) tab zonder reload
    const tabM = document.querySelector('.tab-medewerkers');
    const tabK = document.querySelector('.tab-klanten');
    const url  = (tabM?.getAttribute('data-url')) || (tabK?.getAttribute('data-url'));
    if (url) history.pushState({}, '', url);
  }

  document.getElementById('fab-prev')?.addEventListener('click', (e)=>{ e.preventDefault(); navigate(-1); });
  document.getElementById('fab-next')?.addEventListener('click', (e)=>{ e.preventDefault(); navigate(+1); });
  document.getElementById('fab-close')?.addEventListener('click', (e)=>{ e.preventDefault(); closeDetail(); });

  // Verberg FABs als detail verdwijnt (veiligheidsnet bij swaps)
  document.body.addEventListener('htmx:afterSwap', (evt)=>{
    if (evt.target && evt.target.id === 'users-list') {
      const onDetailUrl = /\/(medewerkers|klanten)\/\d+$/i.test(location.pathname);
      if (!onDetailUrl) {
        document.getElementById('detail-fab')?.remove();
      }
    }
  });
})();
</script>