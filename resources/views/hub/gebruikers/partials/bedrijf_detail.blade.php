
<style>
@keyframes btn-wiggle {0%,100%{transform:translateX(0)}20%{transform:translateX(-2px)}40%{transform:translateX(2px)}60%{transform:translateX(-1px)}80%{transform:translateX(1px)}}
.btn-wiggle{animation:btn-wiggle .35s ease-in-out}
</style>
<div class="w-full h-full min-h-0 p-5 relative">
  <div class="flex flex-col gap-5 h-full min-h-0">
    <div class="min-w-0 shrink-0">
      <h3 class="text-lg text-[#215558] font-black leading-tight truncate">{{ $company->name }}</h3>
    </div>

    <hr class="border-gray-200 shrink-0">

    <div class="w-full shrink-0">
      {{-- Trigger (klikbaar) --}}
      <div data-person-picker-trigger
          class="w-full relative text-[#215558]/50 text-xs font-semibold text-center">
        <p class="w-full bg-gray-100 hover:bg-gray-200 transition duration-300 cursor-pointer rounded-xl p-3">{{ __('gebruikers.actions.assign_user_button') }}</p>

        {{-- Popover/panel (wordt via HTMX gevuld) --}}
        <div data-person-picker-panel
            class="p-1 rounded-xl bg-white border border-gray-200 shadow-md absolute z-100 w-full top-0 left-[103.5%]
                    opacity-0 translate-y-1 pointer-events-none transition duration-200 ease-out">
          @php $uid = 'cmp-'.$company->id; @endphp
          <div id="person-picker-panel-body-{{ $uid }}" data-person-picker-panel-body
              hx-get="{{ route('support.gebruikers.bedrijven.personen', $company) }}"
              hx-trigger="load"
              hx-swap="innerHTML transition:true">
            {{-- skeleton bij eerste open --}}
            <div class="mb-2 bg-gray-100 rounded-xl border border-gray-200 px-3 py-2 animate-pulse h-[38px]"></div>
            <div class="space-y-1">
              @for($i=0;$i<4;$i++)
                <div class="h-10 bg-gray-100 rounded-xl animate-pulse"></div>
              @endfor
            </div>
          </div>
        </div>
      </div>

      {{-- Gekoppelde personen (append-only weergave) --}}
      <div id="company-persons-{{ $uid }}" class="flex flex-col gap-1 mt-1 shrink-0">
        @php
          $__gekoppeld = \App\Models\User::where('rol','klant')
            ->where('company_id', $company->id)
            ->orderBy('name')
            ->get(['id','name','email', 'is_company_admin']);

          // Tel één keer per render
          $companyAdminCount  = \App\Models\User::where('company_id', $company->id)
            ->where('is_company_admin', true)
            ->count();

          $companyMemberCount = \App\Models\User::where('company_id', $company->id)
            ->count();
        @endphp

        @forelse($__gekoppeld as $u)
          @include('hub.gebruikers.partials._bedrijf_persoon_row', [
            'u'                  => $u,
            'company'            => $company,
            // In "Gebruikers → Bedrijven" wil je de rij wél klikbaar houden:
            // 'asLink' default = true, dus niet meegeven of expliciet true
            'companyAdminCount'  => $companyAdminCount,
            'companyMemberCount' => $companyMemberCount,
          ])
        @empty
        @endforelse
      </div>
    </div>

    <hr class="border-gray-200">

    @php
      /** @var \App\Models\Company $company */
      $valU = fn(string $k) => old($k, data_get($company, $k, ''));
      $countriesU = ['NL'=>'Netherlands','BE'=>'Belgium','DE'=>'Germany','FR'=>'France','ES'=>'Spain'];
      $ccU = $valU('country_code');
      $ccLabelU = $ccU && isset($countriesU[$ccU]) ? "{$countriesU[$ccU]} ({$ccU})" : __('instellingen.company.placeholders.country');

      // Unieke suffix om ID-conflict te voorkomen wanneer dezelfde UI elders ook bestaat
      $uid = 'cmp-'.$company->id;
    @endphp
    <div id="company-card-{{ $uid }}" class="mt-2 flex-1 min-h-0 overflow-y-auto">
      {{-- Flash target voor server responses (OOB mogelijk) --}}
      <div id="company-flash-{{ $uid }}" class="hidden"></div>

      <form id="company-form-{{ $uid }}"
            method="post"
            hx-post="{{ route('support.instellingen.company.update') }}"
            hx-vals='{"company_id":"{{ $company->id }}","flash_id":"company-flash-{{ $uid }}"}'
            hx-target="#company-flash-{{ $uid }}"
            hx-swap="outerHTML transition:true"
            hx-headers='{"X-Requested-With":"XMLHttpRequest","Accept":"text/html, application/json"}'
            class="grid grid-cols-1 gap-3 relative">
        @csrf
        @method('PATCH')

        {{-- Bedrijfsgegevens --}}
        <div>
          <div class="grid grid-cols-1 gap-3">
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.name') }}</label>
              <input name="name" type="text" value="{{ $valU('name') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                    required>
            </div>

            {{-- Land (custom dropdown) --}}
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.country_code') }}</label>

              <div id="country-dropdown-{{ $uid }}" class="relative" data-open="false">
                <button type="button" id="country-dropdown-button-{{ $uid }}"
                        class="w-full py-3 px-4 text-sm rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300
                              flex items-center justify-between gap-3"
                        aria-haspopup="listbox" aria-expanded="false">
                  <span class="flex items-center gap-1">
                    <i class="min-w-[16px] fa-solid fa-earth-europe text-[#215558]"></i>
                    <span id="country-btn-text-{{ $uid }}" class="px-1 text-sm text-[#215558] font-semibold">
                      {{ $ccLabelU }}
                    </span>
                  </span>
                  <svg class="w-4 h-4 opacity-70 flex-shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg>
                </button>

                <ul id="country-options-{{ $uid }}"
                    class="absolute z-40 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-xl shadow-lg p-1
                          flex flex-col gap-1 overflow-auto hidden"
                    role="listbox">
                  <li role="option" data-value=""
                      aria-selected="{{ $ccU ? 'false' : 'true' }}"
                      class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                            hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none {{ $ccU ? '' : 'bg-gray-200' }}">
                    <span class="px-1 text-sm text-[#215558] font-semibold">{{ __('instellingen.company.placeholders.country') }}</span>
                  </li>
                  @foreach($countriesU as $code => $label)
                    <li role="option" data-value="{{ $code }}"
                        aria-selected="{{ $ccU === $code ? 'true' : 'false' }}"
                        class="px-3 py-2 text-sm rounded-lg cursor-pointer flex items-center gap-2
                              hover:bg-gray-200 focus:bg-gray-200 transition duration-300 outline-none {{ $ccU === $code ? 'bg-gray-200' : '' }}">
                      <span class="text-[#215558] text-xs font-semibold uppercase w-10 text-left">{{ $code }}</span>
                      <span class="px-1 text-sm text-[#215558] font-semibold">{{ $label }} ({{ $code }})</span>
                    </li>
                  @endforeach
                </ul>
              </div>

              {{-- native select voor submit value --}}
              <select id="country-select-{{ $uid }}" name="country_code" class="sr-only" tabindex="-1" aria-hidden="true">
                <option value="">{{ __('instellingen.company.placeholders.country') }}</option>
                @foreach($countriesU as $code => $label)
                  <option value="{{ $code }}" @selected($ccU === $code)>{{ $label }} ({{ $code }})</option>
                @endforeach
              </select>
            </div>

            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.website') }}</label>
              <input name="website" type="url" value="{{ $valU('website') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.email') }}</label>
              <input name="email" type="email" value="{{ $valU('email') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.phone') }}</label>
              <input name="phone" type="text" value="{{ $valU('phone') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>
          </div>
        </div>

        {{-- Adres --}}
        <div>
          <div class="grid grid-cols-1 gap-3">
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.street') }}</label>
              <input name="street" type="text" value="{{ $valU('street') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.house_number') }}</label>
              <input name="house_number" type="text" value="{{ $valU('house_number') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.postal_code') }}</label>
              <input name="postal_code" type="text" value="{{ $valU('postal_code') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.city') }}</label>
              <input name="city" type="text" value="{{ $valU('city') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>
          </div>
        </div>

        {{-- Registraties --}}
        <div>
          <div class="grid grid-cols-1 gap-3">
            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.trade_name') }}</label>
              <input name="trade_name" type="text" value="{{ $valU('trade_name') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>

            {{-- KVK (NL only) --}}
            <div id="kvk-field-wrap-{{ $uid }}">
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.kvk_number') }}</label>
              <input name="kvk_number" type="text" value="{{ $valU('kvk_number') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>

            <div>
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.vat_number') }}</label>
              <input name="vat_number" type="text" value="{{ $valU('vat_number') }}"
                    class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
            </div>

            {{-- Rechtsvorm (verbergen in DE/FR/ES) --}}
            <div id="legal-form-wrap-{{ $uid }}">
              <label class="block text-xs text-[#215558] opacity-70 mb-1">{{ __('instellingen.company.fields.legal_form') }}</label>
              @php
                $formsU = ['BV','NV','Eenmanszaak','VOF','CV','Stichting','Vereniging','Coöperatie','Maatschap','LLC','GmbH'];
                $formCurU = $valU('legal_form');
              @endphp
              <select name="legal_form"
                      class="company-input-{{ $uid }} w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition duration-300">
                <option value="">{{ __('instellingen.company.placeholders.legal_form') }}</option>
                @foreach($formsU as $f)
                  <option value="{{ $f }}" @selected($formCurU === $f)>{{ $f }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        {{-- Opslaan --}}
        <div class="w-full flex items-center justify-end mt-3">
          <button id="company-save-btn-{{ $uid }}" type="submit"
                  class="relative w-full bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
            <span id="company-spinner-{{ $uid }}" class="absolute -left-6 top-1/2 -translate-y-1/2 hidden">
              <i class="fa-solid fa-spinner fa-spin"></i>
            </span>
            <span id="company-check-{{ $uid }}" class="absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 hidden">
              <i class="fa-solid fa-check text-white"></i>
            </span>
            <span id="company-btn-label-{{ $uid }}">
              {{ __('instellingen.actions.save') }}
            </span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
(function(){
  const uid = @json($uid); // <-- Blade runt nu wel
  const root    = document.getElementById('company-card-' + uid);
  if (!root) return;

  // --- Country dropdown ---
  const dd      = root.querySelector('#country-dropdown-' + uid);
  if (dd && !dd.dataset.bound){
    dd.dataset.bound = '1';
    const btn    = root.querySelector('#country-dropdown-button-' + uid);
    const list   = root.querySelector('#country-options-' + uid);
    const text   = root.querySelector('#country-btn-text-' + uid);
    const select = root.querySelector('#country-select-' + uid);
    const open  = ()=>{ list.classList.remove('hidden'); dd.dataset.open='true'; btn?.setAttribute('aria-expanded','true'); };
    const close = ()=>{ list.classList.add('hidden');    dd.dataset.open='false'; btn?.setAttribute('aria-expanded','false'); };
    function updateFieldVisibility(code){
      const cc = (code || '').toUpperCase();
      const kvkWrap   = root.querySelector('#kvk-field-wrap-' + uid);
      const legalWrap = root.querySelector('#legal-form-wrap-' + uid);
      const showKvk   = cc === 'NL';
      const hideLegal = ['DE','FR','ES'].includes(cc);
      if (kvkWrap)   kvkWrap.classList.toggle('hidden', !showKvk);
      if (legalWrap) legalWrap.classList.toggle('hidden', hideLegal);
      kvkWrap?.querySelector('[name="kvk_number"]')?.removeAttribute('required');
      if (hideLegal) legalWrap?.querySelector('[name="legal_form"]')?.removeAttribute('required');
    }
    btn?.addEventListener('click', (e)=>{ e.preventDefault(); (dd.dataset.open==='true'?close:open)(); });
    list?.querySelectorAll('li[role="option"]').forEach((li)=>{
      li.addEventListener('click', ()=>{
        const val = li.getAttribute('data-value') || '';
        list.querySelectorAll('li[role="option"]').forEach(x=>{ x.classList.remove('bg-gray-200'); x.setAttribute('aria-selected','false'); });
        li.classList.add('bg-gray-200'); li.setAttribute('aria-selected','true');
        const label = li.querySelector('span:nth-child(2)')?.textContent || li.querySelector('span')?.textContent || '';
        if (text) text.textContent = label;
        if (select) select.value = val;
        updateFieldVisibility(val);
        close();
      });
    });
    document.addEventListener('click', function onDoc(e){ if (!dd.isConnected) return document.removeEventListener('click', onDoc); if (!dd.contains(e.target)) close(); });
    document.addEventListener('keydown', function onKey(e){ if (!dd.isConnected) return document.removeEventListener('keydown', onKey); if (e.key === 'Escape') close(); });
    updateFieldVisibility(select?.value || '');
  }

  // --- Save button UX ---
  const form    = root.querySelector('#company-form-' + uid);
  const btn     = root.querySelector('#company-save-btn-' + uid);
  const spinner = root.querySelector('#company-spinner-' + uid);
  const check   = root.querySelector('#company-check-' + uid);
  const label   = root.querySelector('#company-btn-label-' + uid);

  if (form && btn && spinner && check && label && !form.dataset.bound){
    form.dataset.bound = '1';
    let original = label.textContent || '';
    function clearErrors(){ root.querySelectorAll('.company-input-' + uid).forEach(el=>{ el.classList.remove('border-red-300','focus:border-red-400'); el.removeAttribute('aria-invalid'); }); }
    function setBase(fromError=false){
      btn.classList.remove('bg-green-500','bg-red-500','hover:bg-red-600','btn-wiggle');
      btn.classList.add('bg-[#0F9B9F]','hover:bg-[#215558]');
      spinner.classList.add('hidden'); check.classList.add('hidden'); label.classList.remove('opacity-0');
      if (fromError && original){ label.textContent = original; }
      btn.disabled = false;
    }
    function setLoading(){
      clearErrors();
      btn.disabled = true; spinner.classList.remove('hidden'); check.classList.add('hidden'); label.classList.remove('opacity-0');
      btn.classList.remove('bg-green-500','bg-red-500','hover:bg-red-600','btn-wiggle');
      btn.classList.add('bg-[#0F9B9F]','hover:bg-[#215558]');
    }
    function setSuccess(){
      spinner.classList.add('hidden');
      label.classList.add('opacity-0');
      btn.classList.remove('bg-[#0F9B9F]','hover:bg-[#215558]','bg-red-500','hover:bg-red-600','btn-wiggle');
      btn.classList.add('bg-green-500');
      check.classList.remove('hidden');
      setTimeout(()=> setBase(false), 3000);
    }
    function setError(msg){
      spinner.classList.add('hidden'); check.classList.add('hidden'); label.classList.remove('opacity-0');
      btn.disabled = false;
      btn.classList.remove('bg-[#0F9B9F]','hover:bg-[#215558]','bg-green-500','hover:bg-green-600');
      btn.classList.add('bg-red-500','btn-wiggle');
      label.textContent = msg || 'Opslaan mislukt';
      setTimeout(()=> setBase(true), 3000);
    }

    form.addEventListener('htmx:beforeRequest', ()=> setLoading());
    form.addEventListener('htmx:afterRequest', (e)=>{
      const xhr = e.detail?.xhr;
      if (!xhr) { setError('Netwerkfout'); return; }
      if (xhr.status === 200) setSuccess();
      else if (xhr.status === 422) {
        try {
          const json = JSON.parse(xhr.responseText);
          const errs = json?.errors || {};
          const first = Object.keys(errs)[0];
          if (first){
            const field = form.querySelector(`[name="${first}"]`);
            if (field){ field.classList.add('border-red-300','focus:border-red-400'); field.setAttribute('aria-invalid','true'); field.focus(); }
            setError(errs[first]?.[0] || 'Controleer invoer');
          } else setError('Controleer invoer');
        } catch { setError('Controleer invoer'); }
      } else if (xhr.status === 403) setError('Geen rechten om op te slaan');
      else setError('Opslaan mislukt');
    });
  }
})();
</script>
