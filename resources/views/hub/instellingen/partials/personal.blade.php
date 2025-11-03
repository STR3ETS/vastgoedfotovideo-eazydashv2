@php
  $langs  = ['nl'=>'Nederlands','de'=>'Deutsch','fr'=>'Français','es'=>'Español','en'=>'English'];
  $currentLocale = old('locale', $user->locale ?? app()->getLocale());
  if (!array_key_exists($currentLocale, $langs)) { $currentLocale = 'nl'; }
@endphp

<div class="p-5 grid grid-cols-1 gap-3" id="settings-card">
  {{-- hidden target voor i18n-refresh (wordt ge-swapt door controller) --}}
  <div id="settings-flash" class="hidden"></div>

  <form id="settings-form"
        method="post"
        hx-post="{{ route('support.instellingen.update') }}"
        hx-target="#settings-flash"
        hx-swap="outerHTML transition:true"
        hx-headers='{"X-Requested-With":"XMLHttpRequest","Accept":"text/html, application/json"}'
        class="grid grid-cols-1 gap-3">
    @csrf
    @method('PATCH')

    <div>
      <label id="i18n-label-name" class="block text-xs text-[#215558] opacity-70 mb-1">
        {{ __('instellingen.fields.name') }}
      </label>
      <input name="name" type="text" value="{{ old('name', $user->name) }}" placeholder="{{ __('instellingen.fields.name') }}"
             class="settings-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
    </div>

    <div>
      <label id="i18n-label-email" class="block text-xs text-[#215558] opacity-70 mb-1">
        {{ __('instellingen.fields.email') }}
      </label>
      <input name="email" type="email" value="{{ old('email', $user->email) }}" placeholder="{{ __('instellingen.fields.email') }}"
             class="settings-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
    </div>

    {{-- LANGUAGE SELECT (custom dropdown + hidden select) --}}
    <div id="lang-select-wrap">
      <label id="i18n-label-lang" class="block text-xs text-[#215558] opacity-70 mb-1">
        {{ __('instellingen.fields.lang') }}
      </label>

      <div id="lang-dropdown" class="relative" data-open="false">
        <button type="button" id="lang-dropdown-button"
                class="w-full py-3 px-4 text-sm rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300
                       flex items-center justify-between gap-3"
                aria-haspopup="listbox" aria-expanded="false">
          <span class="flex items-center gap-1">
            <i class="min-w-[16px] fa-solid fa-globe text-[#215558]"></i>
            <span id="lang-btn-text" class="px-1 text-sm text-[#215558] font-semibold">
              {{ $langs[$currentLocale] }}
            </span>
          </span>
          <svg class="w-4 h-4 opacity-70 flex-shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </button>

        <ul id="lang-options"
            class="absolute z-40 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-1
                   max-h-56 flex flex-col gap-1 overflow-auto hidden"
            role="listbox">
          @foreach($langs as $code => $label)
            <li role="option"
                data-value="{{ $code }}"
                aria-selected="{{ $code === $currentLocale ? 'true' : 'false' }}"
                class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                       hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none {{ $code === $currentLocale ? 'bg-gray-200' : '' }}">
              <span class="text-[#215558] text-xs font-semibold uppercase w-8 text-center">{{ $code }}</span>
              <span class="px-1 text-sm text-[#215558] font-semibold">{{ $label }}</span>
            </li>
          @endforeach
        </ul>
      </div>

      <select id="lang-select" name="locale" class="sr-only" tabindex="-1" aria-hidden="true">
        @foreach($langs as $code => $label)
          <option value="{{ $code }}" {{ $code === $currentLocale ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div class="col-span-1 flex items-center justify-end mt-3">
      <button id="settings-save-btn" type="submit"
              class="relative w-full bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
        <span id="settings-spinner" class="absolute left-4 top-1/2 -translate-y-1/2 hidden opacity-0">
          <i class="fa-solid fa-spinner fa-spin"></i>
        </span>
        <span id="settings-check" class="absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 hidden">
          <i class="fa-solid fa-check text-white"></i>
        </span>
        <span id="settings-btn-label" data-i18n="instellingen.actions.save">
          {{ __('instellingen.actions.save') }}
        </span>
      </button>
    </div>
  </form>
</div>

<style>
@keyframes btn-wiggle {0%,100%{transform:translateX(0)}20%{transform:translateX(-2px)}40%{transform:translateX(2px)}60%{transform:translateX(-1px)}80%{transform:translateX(1px)}}
.btn-wiggle{animation:btn-wiggle .35s ease-in-out}
</style>

@verbatim
<script>
(function(){
  // Init voor één (sub)root – wordt aangeroepen bij initial load + elke HTMX swap
  function init(root){
    // --- Taal dropdown (scoped + guard) ---
    const dropdown = root.querySelector('#lang-dropdown');
    if (dropdown && !dropdown.dataset.bound){
      dropdown.dataset.bound = '1';

      const btn      = dropdown.querySelector('#lang-dropdown-button');
      const list     = dropdown.querySelector('#lang-options');
      const labelEl  = dropdown.querySelector('#lang-btn-text');
      const selectEl = root.querySelector('#lang-select');

      const openList  = ()=>{ list.classList.remove('hidden'); dropdown.dataset.open='true';  btn?.setAttribute('aria-expanded','true'); };
      const closeList = ()=>{ list.classList.add('hidden');    dropdown.dataset.open='false'; btn?.setAttribute('aria-expanded','false'); };

      btn?.addEventListener('click', (e)=>{ e.preventDefault(); (dropdown.dataset.open === 'true' ? closeList : openList)(); });

      list?.querySelectorAll('li[role="option"]').forEach((item)=>{
        item.addEventListener('click', ()=>{
          const value = item.getAttribute('data-value');
          const text  = item.querySelector('span:nth-child(2)')?.textContent || value;

          if (labelEl) labelEl.textContent = text;
          list.querySelectorAll('li[role="option"]').forEach(li=>{
            li.classList.remove('bg-gray-200'); li.setAttribute('aria-selected','false');
          });
          item.classList.add('bg-gray-200'); item.setAttribute('aria-selected','true');

          if (selectEl) selectEl.value = value;
          closeList();
        });
      });

      // Sluiten bij click buiten / Esc – netjes opruimen als de partial wordt vervangen
      function onDocClick(e){ if (!dropdown.isConnected) return document.removeEventListener('click', onDocClick); if (!dropdown.contains(e.target)) closeList(); }
      function onKey(e){ if (!dropdown.isConnected) return document.removeEventListener('keydown', onKey); if (e.key === 'Escape') closeList(); }
      document.addEventListener('click', onDocClick);
      document.addEventListener('keydown', onKey);
    }

    // --- Knop UX (spinner / ✔️ / error) – ook scoped + guard ---
    const form    = root.querySelector('#settings-form');
    const btn     = root.querySelector('#settings-save-btn');
    const spinner = root.querySelector('#settings-spinner');
    const check   = root.querySelector('#settings-check');
    const lbl     = root.querySelector('#settings-btn-label');

    if (form && btn && spinner && check && lbl && !form.dataset.bound){
      form.dataset.bound = '1';

      let i18nLabelText = lbl.textContent || '';
      let observerGuard = false;
      const lblObserver = new MutationObserver(()=>{ if (!observerGuard) i18nLabelText = lbl.textContent; });
      lblObserver.observe(lbl, { childList:true, characterData:true, subtree:true });

      function clearFieldErrors(){
        root.querySelectorAll('.settings-input').forEach(el=>{
          el.classList.remove('border-red-300','focus:border-red-400'); el.removeAttribute('aria-invalid');
        });
      }
      function setBase(fromError=false){
        btn.classList.remove('bg-green-500','hover:bg-green-600','bg-red-500','hover:bg-red-600','btn-wiggle');
        btn.classList.add('bg-[#0F9B9F]','hover:bg-[#215558]');
        spinner.classList.add('hidden'); check.classList.add('hidden'); lbl.classList.remove('opacity-0');
        if (fromError && i18nLabelText){ observerGuard = true; lbl.textContent = i18nLabelText; setTimeout(()=>{observerGuard=false;},0); }
        btn.disabled = false;
      }
      function setLoading(){
        clearFieldErrors();
        btn.disabled = true; spinner.classList.remove('hidden'); check.classList.add('hidden'); lbl.classList.remove('opacity-0');
        btn.classList.remove('bg-green-500','hover:bg-green-600','bg-red-500','hover:bg-red-600','btn-wiggle');
        btn.classList.add('bg-[#0F9B9F]','hover:bg-[#215558]');
      }
      function setSuccess(){
        spinner.classList.add('hidden');
        lbl.classList.add('opacity-0'); // alleen het vinkje zichtbaar
        btn.classList.remove('bg-[#0F9B9F]','hover:bg-[#215558]','bg-red-500','hover:bg-red-600','btn-wiggle');
        btn.classList.add('bg-green-500','hover:bg-green-600');
        check.classList.remove('hidden');
        setTimeout(()=> setBase(false), 3000); // label komt terug in nieuwe taal (via flash-partial)
      }
      function setError(txt){
        spinner.classList.add('hidden'); check.classList.add('hidden'); lbl.classList.remove('opacity-0');
        btn.disabled = false;
        btn.classList.remove('bg-[#0F9B9F]','hover:bg-[#215558]','bg-green-500','hover:bg-green-600');
        btn.classList.add('bg-red-500','hover:bg-red-600','btn-wiggle');
        observerGuard = true; lbl.textContent = txt || 'Opslaan mislukt'; setTimeout(()=>{observerGuard=false;},0);
        setTimeout(()=> setBase(true), 3000);
      }

      form.addEventListener('htmx:beforeRequest', ()=> setLoading());
      form.addEventListener('htmx:afterRequest', (e)=>{
        const xhr = e.detail?.xhr; if (!xhr){ setError('Netwerkfout'); return; }
        if (xhr.status === 200){ setSuccess(); }
        else if (xhr.status === 422){
          try{
            const json = JSON.parse(xhr.responseText);
            const errs = json?.errors || {}; const keys = Object.keys(errs);
            if (keys.length){
              const firstKey = keys[0];
              const msg = (errs[firstKey] && errs[firstKey][0]) ? errs[firstKey][0] : 'Controleer invoer';
              const field = form.querySelector(`[name="${firstKey}"]`);
              if (field){ field.classList.add('border-red-300','focus:border-red-400'); field.setAttribute('aria-invalid','true'); field.focus(); }
              setError(msg);
            } else setError('Controleer invoer');
          } catch(_){ setError('Controleer invoer'); }
        }
        else if (xhr.status === 403){ setError('Geen rechten om op te slaan'); }
        else setError('Opslaan mislukt');
      });
    }
  }

  // 1) Direct bij de eerste pageload
  init(document);

  // 2) Na elke HTMX-swap (ook wanneer jouw personal-partial opnieuw geladen wordt)
  if (window.htmx) {
    htmx.onLoad(function(el){ init(el); });
  }
})();
</script>
@endverbatim

