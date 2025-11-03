@php
  /** @var \App\Models\User $user */
  $company = $user->company;
  $val = fn(string $k) => old($k, data_get($company, $k, ''));
  // Eenvoudige landenlijst (ISO-3166 alpha-2)
  $countries = [
    'NL' => 'Netherlands', 'BE' => 'Belgium', 'DE' => 'Germany', 'FR' => 'France', 'ES' => 'Spain',
  ];
  $currentCountry = $val('country_code');
  $countryLabel = $currentCountry && isset($countries[$currentCountry])
      ? "{$countries[$currentCountry]} ({$currentCountry})"
      : __('instellingen.company.placeholders.country');

  // Rechtsvormen + label
  $forms = ['BV','NV','Eenmanszaak','VOF','CV','Stichting','Vereniging','Coöperatie','Maatschap','LLC','GmbH'];
  $currentForm = $val('legal_form');
  $legalLabel = $currentForm && in_array($currentForm, $forms, true)
      ? $currentForm
      : __('instellingen.company.placeholders.legal_form');
@endphp

<div class="p-5" id="company-card">

  {{-- Hidden target (optioneel): controller kan hier een flash/partial in swappen --}}
  <div id="company-flash" class="hidden"></div>

  <form id="company-form"
        method="post"
        hx-post="{{ route('support.instellingen.company.update') }}"
        hx-target="#company-flash"
        hx-swap="outerHTML transition:true"
        hx-headers='{"X-Requested-With":"XMLHttpRequest","Accept":"text/html, application/json"}'
        class="grid grid-cols-1 lg:grid-cols-2 gap-3">
    @csrf
    @method('PATCH')

    {{-- Bedrijfsgegevens --}}
    <div class="lg:col-span-2">
      <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2">
          <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.name') }}</label>
          <input name="name" type="text" value="{{ $val('name') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                 required>
        </div>

        {{-- Vestigingsland — CUSTOM DROPDOWN + hidden select --}}
        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.country_code') }}</label>

          <div id="country-dropdown" class="relative" data-open="false">
            <button type="button" id="country-dropdown-button"
                    class="w-full py-3 px-4 text-sm rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300
                           flex items-center justify-between gap-3"
                    aria-haspopup="listbox" aria-expanded="false">
              <span class="flex items-center gap-1">
                <i class="min-w-[16px] fa-solid fa-earth-europe text-[#215558]"></i>
                <span id="country-btn-text" class="px-1 text-sm text-[#215558] font-semibold">
                  {{ $countryLabel }}
                </span>
              </span>
              <svg class="w-4 h-4 opacity-70 flex-shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <ul id="country-options"
                class="absolute z-40 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-1
                       flex flex-col gap-1 overflow-auto hidden"
                role="listbox">
              <li role="option"
                  data-value=""
                  aria-selected="{{ $currentCountry ? 'false' : 'true' }}"
                  class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                         hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none {{ $currentCountry ? '' : 'bg-gray-200' }}">
                <span class="px-1 text-sm text-[#215558] font-semibold">{{ __('instellingen.company.placeholders.country') }}</span>
              </li>
              @foreach($countries as $code => $label)
                <li role="option"
                    data-value="{{ $code }}"
                    aria-selected="{{ $currentCountry === $code ? 'true' : 'false' }}"
                    class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                           hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none {{ $currentCountry === $code ? 'bg-gray-200' : '' }}">
                  <span class="text-[#215558] text-xs font-semibold uppercase w-10 text-left">{{ $code }}</span>
                  <span class="px-1 text-sm text-[#215558] font-semibold">{{ $label }} ({{ $code }})</span>
                </li>
              @endforeach
            </ul>
          </div>

          {{-- native select als submit value (verborgen) --}}
          <select id="country-select" name="country_code" class="sr-only" tabindex="-1" aria-hidden="true">
            <option value="">{{ __('instellingen.company.placeholders.country') }}</option>
            @foreach($countries as $code => $label)
              <option value="{{ $code }}" @selected($currentCountry === $code)>{{ $label }} ({{ $code }})</option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">Website</label>
          <input name="website" type="url" value="{{ $val('website') }}"
                 placeholder="https://..."
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>
        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">E-mailadres</label>
          <input name="email" type="email" value="{{ $val('email') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>
        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">Telefoonnummer</label>
          <input name="phone" type="text" value="{{ $val('phone') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>
      </div>
    </div>

    <hr class="lg:col-span-2 border-gray-200 my-1">

    {{-- Adres --}}
    <div class="lg:col-span-2">
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">Straatnaam</label>
          <input name="street" type="text" value="{{ $val('street') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>
        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">Nr.</label>
          <input name="house_number" type="text" value="{{ $val('house_number') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>
        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">Postcode</label>
          <input name="postal_code" type="text" value="{{ $val('postal_code') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>
        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">Stad</label>
          <input name="city" type="text" value="{{ $val('city') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>
      </div>
    </div>

    <hr class="lg:col-span-2 border-gray-200 my-1">

    {{-- Registraties --}}
    <div class="lg:col-span-2">
      <div class="grid grid-cols-2 gap-3">
        <div class="col-span-2">
          <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.trade_name') }}</label>
          <input name="trade_name" type="text" value="{{ $val('trade_name') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>

        {{-- ▼ KVK-nummer (NL-only) --}}
        <div id="kvk-field-wrap">
          <label class="block text-xs text-[#215558] opacity-70 mb-1">KVK-nummer</label>
          <input name="kvk_number" type="text" value="{{ $val('kvk_number') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>

        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">BTW-nummer</label>
          <input name="vat_number" type="text" value="{{ $val('vat_number') }}"
                 class="company-input w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>

        {{-- ▼ Rechtsvorm (custom dropdown; niet in DE/FR/ES) --}}
        <div id="legal-form-wrap">
          <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.legal_form') }}</label>

          {{-- Custom dropdown --}}
          <div id="legal-dropdown" class="relative" data-open="false">
            <button type="button" id="legal-dropdown-button"
                    class="w-full py-3 px-4 text-sm rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300
                           flex items-center justify-between gap-3"
                    aria-haspopup="listbox" aria-expanded="false">
              <span class="flex items-center gap-1">
                <i class="min-w-[16px] fa-solid fa-scale-balanced text-[#215558]"></i>
                <span id="legal-btn-text" class="px-1 text-sm text-[#215558] font-semibold">
                  {{ $legalLabel }}
                </span>
              </span>
              <svg class="w-4 h-4 opacity-70 flex-shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>

            <ul id="legal-options"
                class="absolute z-40 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-1
                       flex flex-col gap-1 overflow-auto hidden"
                role="listbox">
              <li role="option"
                  data-value=""
                  aria-selected="{{ $currentForm ? 'false' : 'true' }}"
                  class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                         hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none {{ $currentForm ? '' : 'bg-gray-200' }}">
                <span class="px-1 text-sm text-[#215558] font-semibold">{{ __('instellingen.company.placeholders.legal_form') }}</span>
              </li>
              @foreach($forms as $f)
                <li role="option"
                    data-value="{{ $f }}"
                    aria-selected="{{ $currentForm === $f ? 'true' : 'false' }}"
                    class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                           hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none {{ $currentForm === $f ? 'bg-gray-200' : '' }}">
                  <span class="px-1 text-sm text-[#215558] font-semibold">{{ $f }}</span>
                </li>
              @endforeach
            </ul>
          </div>

          {{-- Verborgen native select om de waarde te posten --}}
          <select id="legal-select" name="legal_form" class="sr-only" tabindex="-1" aria-hidden="true">
            <option value="">{{ __('instellingen.company.placeholders.legal_form') }}</option>
            @foreach($forms as $f)
              <option value="{{ $f }}" @selected($currentForm === $f)>{{ $f }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>

    {{-- Opslaan --}}
    <div class="lg:col-span-2 w-full flex items-center justify-end mt-3">
      <button id="company-save-btn" type="submit"
              class="relative min-w-[200px] bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
        <span id="company-spinner" class="absolute -left-6 top-1/2 -translate-y-1/2 hidden">
          <i class="fa-solid fa-spinner fa-spin"></i>
        </span>
        <span id="company-check" class="absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 hidden">
          <i class="fa-solid fa-check text-white"></i>
        </span>
        <span id="company-btn-label">
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
  function init(root){
    // --- Country dropdown (scoped + guard) ---
    const dd = root.querySelector('#country-dropdown');
    if (dd && !dd.dataset.bound){
      dd.dataset.bound = '1';
      const btn    = dd.querySelector('#country-dropdown-button');
      const list   = dd.querySelector('#country-options');
      const text   = dd.querySelector('#country-btn-text');
      const select = root.querySelector('#country-select');

      const open  = ()=>{ list.classList.remove('hidden'); dd.dataset.open='true'; btn?.setAttribute('aria-expanded','true'); };
      const close = ()=>{ list.classList.add('hidden');    dd.dataset.open='false'; btn?.setAttribute('aria-expanded','false'); };

      // ▼ helper: toggle velden obv land
      function updateCompanyFieldVisibility(code){
        const cc = (code || '').toUpperCase();
        const kvkWrap      = root.querySelector('#kvk-field-wrap');
        const legalWrap    = root.querySelector('#legal-form-wrap');
        const legalSelect  = root.querySelector('#legal-select');
        const legalBtnText = root.querySelector('#legal-btn-text');

        // NL: KVK aan; BE: KVK uit; DE/FR/ES: KVK uit + Rechtsvorm uit; andere/geen: KVK uit, Rechtsvorm aan
        const showKvk   = cc === 'NL';
        const hideLegal = ['DE','FR','ES'].includes(cc);

        if (kvkWrap)   kvkWrap.classList.toggle('hidden', !showKvk);
        if (legalWrap) legalWrap.classList.toggle('hidden', hideLegal);

        // Zorg dat verborgen velden niet per ongeluk 'required' hebben
        kvkWrap?.querySelector('[name="kvk_number"]')?.removeAttribute('required');
        if (hideLegal) {
          legalWrap?.querySelector('[name="legal_form"]')?.removeAttribute('required');

          // Reset legal form als het veld verborgen wordt
          if (legalSelect) legalSelect.value = '';
          // Reset button label en highlight naar placeholder
          const placeholder = legalWrap?.querySelector('#legal-options li[data-value=""] span')?.textContent || 'Selecteer rechtsvorm';
          if (legalBtnText) legalBtnText.textContent = placeholder;
          const lOptions = legalWrap?.querySelectorAll('#legal-options li[role="option"]') || [];
          lOptions.forEach(x => { x.classList.remove('bg-gray-200'); x.setAttribute('aria-selected','false'); });
          const emptyOpt = legalWrap?.querySelector('#legal-options li[data-value=""]');
          if (emptyOpt){ emptyOpt.classList.add('bg-gray-200'); emptyOpt.setAttribute('aria-selected','true'); }
        }
      }

      btn?.addEventListener('click', (e)=>{ e.preventDefault(); (dd.dataset.open==='true'?close:open)(); });

      list?.querySelectorAll('li[role="option"]').forEach((li)=>{
        li.addEventListener('click', ()=>{
          const val = li.getAttribute('data-value') || '';
          // UI states
          list.querySelectorAll('li[role="option"]').forEach(x=>{
            x.classList.remove('bg-gray-200'); x.setAttribute('aria-selected','false');
          });
          li.classList.add('bg-gray-200'); li.setAttribute('aria-selected','true');

          // Button label
          const label = li.querySelector('span:nth-child(2)')?.textContent
                        || li.querySelector('span')?.textContent
                        || '';
          if (text) text.textContent = label;

          // Real value
          if (select) select.value = val;

          // ▼ update visibility op keuze
          updateCompanyFieldVisibility(val);

          close();
        });
      });

      // Close on outside click / ESC with auto-cleanup when node is removed
      function onDocClick(e){
        if (!dd.isConnected) return document.removeEventListener('click', onDocClick);
        if (!dd.contains(e.target)) close();
      }
      function onKey(e){
        if (!dd.isConnected) return document.removeEventListener('keydown', onKey);
        if (e.key === 'Escape') close();
      }
      document.addEventListener('click', onDocClick);
      document.addEventListener('keydown', onKey);

      // ▼ initiale state op basis van huidige waarde
      updateCompanyFieldVisibility(select?.value || '');
    }

    // --- Legal form dropdown (scoped + guard) ---
    const ldd = root.querySelector('#legal-dropdown');
    if (ldd && !ldd.dataset.bound){
      ldd.dataset.bound = '1';
      const lbtn    = ldd.querySelector('#legal-dropdown-button');
      const llist   = ldd.querySelector('#legal-options');
      const ltext   = ldd.querySelector('#legal-btn-text');
      const lselect = root.querySelector('#legal-select');

      const lopen  = ()=>{ llist.classList.remove('hidden'); ldd.dataset.open='true'; lbtn?.setAttribute('aria-expanded','true'); };
      const lclose = ()=>{ llist.classList.add('hidden');    ldd.dataset.open='false'; lbtn?.setAttribute('aria-expanded','false'); };

      lbtn?.addEventListener('click', (e)=>{ e.preventDefault(); (ldd.dataset.open==='true'?lclose:lopen)(); });

      llist?.querySelectorAll('li[role="option"]').forEach((li)=>{
        li.addEventListener('click', ()=>{
          const val = li.getAttribute('data-value') || '';

          // UI states
          llist.querySelectorAll('li[role="option"]').forEach(x=>{
            x.classList.remove('bg-gray-200'); x.setAttribute('aria-selected','false');
          });
          li.classList.add('bg-gray-200'); li.setAttribute('aria-selected','true');

          // Button label
          const label = li.querySelector('span')?.textContent || '';
          if (ltext) ltext.textContent = label;

          // Real value
          if (lselect) lselect.value = val;

          lclose();
        });
      });

      // Close on outside click / ESC with auto-cleanup when node is removed
      function lOnDocClick(e){
        if (!ldd.isConnected) return document.removeEventListener('click', lOnDocClick);
        if (!ldd.contains(e.target)) lclose();
      }
      function lOnKey(e){
        if (!ldd.isConnected) return document.removeEventListener('keydown', lOnKey);
        if (e.key === 'Escape') lclose();
      }
      document.addEventListener('click', lOnDocClick);
      document.addEventListener('keydown', lOnKey);
    }

    // --- Save button UX (spinner ✔️/error) ---
    const form    = root.querySelector('#company-form');
    const btn     = root.querySelector('#company-save-btn');
    const spinner = root.querySelector('#company-spinner');
    const check   = root.querySelector('#company-check');
    const label   = root.querySelector('#company-btn-label');

    if (form && btn && spinner && check && label && !form.dataset.bound){
      form.dataset.bound = '1';

      let originalLabel = label.textContent || '';
      let guard = false;
      const obs = new MutationObserver(()=>{ if (!guard) originalLabel = label.textContent; });
      obs.observe(label, { childList:true, characterData:true, subtree:true });

      function clearErrors(){
        root.querySelectorAll('.company-input').forEach(el=>{
          el.classList.remove('border-red-300','focus:border-red-400');
          el.removeAttribute('aria-invalid');
        });
      }
      function setBase(fromError=false){
        btn.classList.remove('bg-green-500','hover:bg-green-600','bg-red-500','hover:bg-red-600','btn-wiggle');
        btn.classList.add('bg-[#0F9B9F]','hover:bg-[#215558]');
        spinner.classList.add('hidden'); check.classList.add('hidden'); label.classList.remove('opacity-0');
        if (fromError && originalLabel){ guard = true; label.textContent = originalLabel; setTimeout(()=>guard=false,0); }
        btn.disabled = false;
      }
      function setLoading(){
        clearErrors();
        btn.disabled = true; spinner.classList.remove('hidden'); check.classList.add('hidden'); label.classList.remove('opacity-0');
        btn.classList.remove('bg-green-500','hover:bg-green-600','bg-red-500','hover:bg-red-600','btn-wiggle');
        btn.classList.add('bg-[#0F9B9F]','hover:bg-[#215558]');
      }
      function setSuccess(){
        spinner.classList.add('hidden');
        label.classList.add('opacity-0');
        btn.classList.remove('bg-[#0F9B9F]','hover:bg-[#215558]','bg-red-500','hover:bg-red-600','btn-wiggle');
        btn.classList.add('bg-green-500','hover:bg-green-600');
        check.classList.remove('hidden');
        setTimeout(()=> setBase(false), 3000);
      }
      function setError(msg){
        spinner.classList.add('hidden'); check.classList.add('hidden'); label.classList.remove('opacity-0');
        btn.disabled = false;
        btn.classList.remove('bg-[#0F9B9F]','hover:bg-[#215558]','bg-green-500','hover:bg-green-600');
        btn.classList.add('bg-red-500','hover:bg-red-600','btn-wiggle');
        guard = true; label.textContent = msg || 'Opslaan mislukt'; setTimeout(()=>guard=false,0);
        setTimeout(()=> setBase(true), 3000);
      }

      form.addEventListener('htmx:beforeRequest', ()=> setLoading());
      form.addEventListener('htmx:afterRequest', (e)=>{
        const xhr = e.detail?.xhr;
        if (!xhr) { setError('Netwerkfout'); return; }

        if (xhr.status === 200) {
          setSuccess();
        } else if (xhr.status === 422) {
          try {
            const json = JSON.parse(xhr.responseText);
            const errs = json?.errors || {};
            const keys = Object.keys(errs);
            if (keys.length) {
              const first = keys[0];
              const msg = (errs[first] && errs[first][0]) ? errs[first][0] : 'Controleer invoer';
              const field = form.querySelector(`[name="${first}"]`);
              if (field) {
                field.classList.add('border-red-300','focus:border-red-400');
                field.setAttribute('aria-invalid','true');
                field.focus();
              }
              setError(msg);
            } else {
              setError('Controleer invoer');
            }
          } catch(_) {
            setError('Controleer invoer');
          }
        } else if (xhr.status === 403) {
          setError('Geen rechten om op te slaan');
        } else {
          setError('Opslaan mislukt');
        }
      });
    }
  }

  // initial bind
  init(document);

  // re-bind after each HTMX swap (important for partial loads)
  if (window.htmx) {
    htmx.onLoad((el)=> init(el));
  }
})();
</script>
@endverbatim