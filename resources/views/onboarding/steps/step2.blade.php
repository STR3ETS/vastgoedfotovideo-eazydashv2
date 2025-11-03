@php
  $role  = data_get($state, 'profile.role', '');
  $goals = data_get($state, 'profile.goals', []);
  if (!is_array($goals)) $goals = [];
@endphp

<div data-onboarding-step="2" class="pb-2">
  <form
    hx-post="{{ route('onboarding.step2.store') }}"
    hx-target="#onboarding-step"
    hx-swap="innerHTML transition:true"
    hx-push-url="true"
    hx-headers='{"X-CSRF-TOKEN":"{{ csrf_token() }}","Accept":"text/html"}'
    class="flex flex-col gap-4" id="onb-step2-form"
  >
    <div>
      <label class="block text-xs text-[#215558]/70 mb-2">What best describes your role?</label>
      <select name="role"
              class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition duration-300"
              required>
        <option value="" disabled {{ $role ? '' : 'selected' }}>Choose your role</option>
        <option value="owner"   @selected($role === 'owner')>Owner / Manager</option>
        <option value="agent"   @selected($role === 'agent')>Colleague</option>
      </select>
    </div>

    <div>
      <label class="block text-xs text-[#215558]/70 mb-2">What do you mainly want to use Eazyonline for?</label>
      <div class="grid gap-2 text-[#215558] text-sm font-semibold">
        @php $opts = ['tickets'=>'Follow up on tickets','collab'=>'Internal collaboration','comms'=>'Customer communication','reports'=>'Reports','forms'=>'(Multi-step) forms','automation'=>'Automation']; @endphp
        @foreach($opts as $val => $label)
          <label class="inline-flex items-center gap-2">
            <input type="checkbox" name="goals[]" value="{{ $val }}" {{ in_array($val, $goals) ? 'checked' : '' }}>
            {{ $label }}
          </label>
        @endforeach
      </div>
    </div>

    <div class="flex items-center gap-3 mt-2">
      <button type="button"
              hx-get="{{ route('onboarding.step1') }}"
              hx-target="#onboarding-step"
              hx-swap="innerHTML transition:true"
              hx-push-url="true"
              class="bg-gray-200 hover:bg-gray-300 cursor-pointer text-center w-1/3 text-gray-600 text-base font-semibold px-6 py-3 rounded-full transition duration-300">
        Back
      </button>
      <button type="submit"
              class="relative w-full bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
        Next
      </button>
    </div>
  </form>

  {{-- Optioneel: localStorage sync --}}
  @verbatim
  <script>
  (function(){
    const KEY = 'onb.step2';
    const form = document.getElementById('onb-step2-form');
    if(!form) return;

    try {
      const saved = JSON.parse(localStorage.getItem(KEY) || '{}');
      // role
      const role = form.querySelector('[name="role"]');
      if (role && !role.value && saved.role) role.value = saved.role;
      // goals
      if (saved.goals && Array.isArray(saved.goals)) {
        form.querySelectorAll('input[name="goals[]"]').forEach(cb=>{
          if (saved.goals.includes(cb.value)) cb.checked = true;
        });
      }
    } catch(e){}

    form.addEventListener('input', ()=>{
      const data = { role: form.querySelector('[name="role"]')?.value || '', goals: [] };
      form.querySelectorAll('input[name="goals[]"]:checked').forEach(cb=> data.goals.push(cb.value));
      localStorage.setItem(KEY, JSON.stringify(data));
    });
  })();
  </script>
  @endverbatim
</div>
