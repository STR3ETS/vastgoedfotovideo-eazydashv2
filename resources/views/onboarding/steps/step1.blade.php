<div data-onboarding-step="1" class="pb-2">
  <form
    hx-post="{{ route('onboarding.step1.store') }}"
    hx-target="#onboarding-step"
    hx-swap="innerHTML transition:true"
    hx-push-url="true"
    hx-headers='{"X-CSRF-TOKEN":"{{ csrf_token() }}","Accept":"text/html"}'
    class="flex flex-col gap-4" id="onb-step1-form"
  >
    <div>
        <label class="block text-xs text-[#215558]/70 mb-1">Name</label>
        <input type="text" name="name"
                value="{{ old('name', data_get($state, 'account.name', '')) }}"
                class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                placeholder="Your first- and lastname" required>
    </div>
    <div>
        <label class="block text-xs text-[#215558]/70 mb-1">E-mail</label>
        <input type="email" name="email"
                value="{{ old('email', data_get($state, 'account.email', '')) }}"
                class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                placeholder="Your e-mail" required>
    </div>
    <div>
        <label class="block text-xs text-[#215558]/70 mb-1">Phone number</label>
        <input type="tel" name="phone"
                value="{{ old('phone', data_get($state, 'account.phone', '')) }}"
                class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300 mb-0.5"
                placeholder="Your phone number">
        <span class="text-[#215558] text-xs opacity-50">* This can be used to contact you to assist you.</span>
    </div>
    <div class="flex items-center gap-3 mt-2">
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
    const KEY = 'onb.step1';
    const form = document.getElementById('onb-step1-form');
    if(!form) return;

    // Prefill from localStorage IF inputs are still empty
    try {
      const saved = JSON.parse(localStorage.getItem(KEY) || '{}');
      ['name','email','phone'].forEach(n=>{
        const el = form.querySelector(`[name="${n}"]`);
        if (el && !el.value && saved[n]) el.value = saved[n];
      });
    } catch(e){}

    // Save on input
    form.addEventListener('input', ()=>{
      const data = {};
      ['name','email','phone'].forEach(n=>{
        const el = form.querySelector(`[name="${n}"]`);
        data[n] = el ? el.value : '';
      });
      localStorage.setItem(KEY, JSON.stringify(data));
    });

    // On successful HTMX submit, keep localStorage (optioneel: leegmaken)
    // document.body.addEventListener('htmx:afterOnLoad', (e)=>{ if (e.target === form) localStorage.removeItem(KEY); });
  })();
  </script>
  @endverbatim
</div>
