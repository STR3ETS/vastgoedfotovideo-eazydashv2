@php
  $companyName = data_get($state, 'company.name', '');
  $country     = data_get($state, 'company.country_code', 'NL');
  $invites     = data_get($state, 'invites', []);
  if (!is_array($invites)) $invites = [];
  // altijd minstens 1 input tonen
  if (empty($invites)) $invites = [''];
@endphp

<div data-onboarding-step="3" class="pb-2">
  <form
    hx-post="{{ route('onboarding.step3.store') }}"
    hx-target="#onboarding-step"
    hx-swap="innerHTML transition:true"
    hx-push-url="true"
    hx-headers='{"X-CSRF-TOKEN":"{{ csrf_token() }}","Accept":"text/html"}'
    class="flex flex-col gap-4" id="onb-step3-form"
  >
    <div>
      <label class="block text-xs text-[#215558]/70 mb-1">Company name</label>
      <input type="text" name="company_name"
             value="{{ old('company_name', $companyName) }}"
             class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
             placeholder="Company name" required>
    </div>

    <div>
      <label class="block text-xs text-[#215558]/70 mb-1">Country of residence</label>
      <select name="country_code"
              class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition duration-300"
              required>
        @php $countries = ['NL'=>'The Netherlands (NL)','BE'=>'Belgium (BE)','DE'=>'Germany (DE)','FR'=>'France (FR)', 'ES'=>'Spain (ES)']; @endphp
        @foreach($countries as $code => $label)
          <option value="{{ $code }}" @selected(($country ?: 'NL') === $code)>{{ $label }}</option>
        @endforeach
      </select>
    </div>

    <div class="flex items-center gap-3 mt-2">
      <button type="button"
              hx-get="{{ route('onboarding.step2') }}"
              hx-target="#onboarding-step"
              hx-swap="innerHTML transition:true"
              hx-push-url="true"
              class="bg-gray-200 hover:bg-gray-300 cursor-pointer text-center w-1/3 text-gray-600 text-base font-semibold px-6 py-3 rounded-full transition duration-300">
        Back
      </button>
      <button type="submit"
              class="relative w-full bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
        Finish
      </button>
    </div>
  </form>

  {{-- Optioneel: localStorage sync --}}
  @verbatim
  <script>
  (function(){
    const KEY = 'onb.step3';
    const form = document.getElementById('onb-step3-form');
    if(!form) return;

    // Prefill from localStorage only if empty (company/invites)
    try {
      const saved = JSON.parse(localStorage.getItem(KEY) || '{}');
      const cn = form.querySelector('[name="company_name"]');
      if (cn && !cn.value && saved.company_name) cn.value = saved.company_name;
      const cc = form.querySelector('[name="country_code"]');
      if (cc && !cc.value && saved.country_code) cc.value = saved.country_code;
      if (Array.isArray(saved.invites) && saved.invites.length) {
        const wrap = document.getElementById('invite-wrap');
        wrap.innerHTML = '';
        saved.invites.forEach(v=>{
          const i = document.createElement('input');
          i.type='email'; i.name='invites[]'; i.placeholder='collega@bedrijf.nl';
          i.className='w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300 mt-2';
          i.value = v || '';
          wrap.appendChild(i);
        });
      }
    } catch(e){}

    function save(){
      const data = {
        company_name: form.querySelector('[name="company_name"]')?.value || '',
        country_code: form.querySelector('[name="country_code"]')?.value || '',
        invites: Array.from(form.querySelectorAll('input[name="invites[]"]')).map(i=>i.value || '')
      };
      localStorage.setItem(KEY, JSON.stringify(data));
    }

    form.addEventListener('input', save);
  })();
  </script>
  @endverbatim
</div>
