{{-- resources/views/onboarding/index.blade.php --}}
@extends('onboarding.layouts.guest')

@section('content')
<div class="flex flex-col gap-6">
  {{-- Hero --}}
  <div class="w-full h-[150px] rounded-xl relative overflow-hidden">
    <video class="w-full h-full absolute z-1 object-cover" src="/assets/broll-3-cropped.mp4" autoplay muted playsinline loop></video>
    <div class="absolute z-2 w-full h-full bg-[#215558]/30 flex flex-col items-center justify-center">
      <span class="text-white text-3xl font-bold mb-1">eazyonline</span>
      <span class="text-white text-xs font-medium opacity-80">Let's make it <span class="relative">Eazy</span> again.</span>
    </div>
  </div>

  <div class="bg-white rounded-xl p-6">
    {{-- Progress header --}}
    <div class="flex items-center gap-3 px-18 select-none" id="onboarding-progress">
      {{-- Step 1 --}}
      <div class="flex flex-col items-center gap-1">
        <div id="step-dot-1"
             class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 ease-out
                    bg-[#215558]/10 border-[#215558] text-[#215558]">
          <span id="step-icon-1" data-icon='{"default":"<i class=&quot;fa-solid fa-user fa-sm&quot;></i>"}'>
            <i class="fa-solid fa-user fa-sm"></i>
          </span>
        </div>
        <h3 class="text-sm font-semibold text-[#215558] mb-8 text-center whitespace-nowrap">Account</h3>
      </div>

      {{-- Bar 1 --}}
      <div class="w-16 h-1 rounded-full -mt-14 bg-[#215558]/25 overflow-hidden">
        <div id="step-bar-fill-1" class="h-full w-0 bg-[#215558] transition-all duration-500 ease-out"></div>
      </div>

      {{-- Step 2 --}}
      <div class="flex flex-col items-center gap-1">
        <div id="step-dot-2"
             class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 ease-out
                    bg-[#215558]/10 border-[#215558]/0 text-[#215558]">
          <span id="step-icon-2" data-icon='{"default":"<i class=&quot;fa-solid fa-address-card fa-sm&quot;></i>"}'>
            <i class="fa-solid fa-address-card fa-sm"></i>
          </span>
        </div>
        <h3 class="text-sm font-semibold text-[#215558] mb-8 text-center whitespace-nowrap">About you</h3>
      </div>

      {{-- Bar 2 --}}
      <div class="w-16 h-1 rounded-full -mt-14 bg-[#215558]/25 overflow-hidden">
        <div id="step-bar-fill-2" class="h-full w-0 bg-[#215558] transition-all duration-500 ease-out"></div>
      </div>

      {{-- Step 3 --}}
      <div class="flex flex-col items-center gap-1">
        <div id="step-dot-3"
             class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition-all duration-300 ease-out
                    bg-[#215558]/10 border-[#215558]/0 text-[#215558]">
          <span id="step-icon-3" data-icon='{"default":"<i class=&quot;fa-solid fa-building-columns fa-sm&quot;></i>"}'>
            <i class="fa-solid fa-building-columns fa-sm"></i>
          </span>
        </div>
        <h3 class="text-sm font-semibold text-[#215558] mb-8 text-center whitespace-nowrap">Company</h3>
      </div>
    </div>

    {{-- Flash target (optioneel) --}}
    <div id="onboarding-flash" class="hidden"></div>

    {{-- Step content (SSR of HTMX) --}}
    @php
      $serverStepView = $serverStepView ?? null;
      $serverStep     = $serverStep     ?? null;
    @endphp

    <div id="onboarding-step"
    @if(!$serverStepView)
        hx-get="{{ route('onboarding.step1') }}"
        hx-trigger="load"
        hx-target="#onboarding-step"
        hx-swap="innerHTML transition:true"
        hx-push-url="true"
        hx-headers='{"X-CSRF-TOKEN":"{{ csrf_token() }}","Accept":"text/html"}'
    @else
        data-current-step="{{ (int)($serverStep ?? 1) }}"
    @endif
    >
      @if($serverStepView)
        @include($serverStepView)
      @else
        <div class="p-10 text-center text-[#215558]/60">
          <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Laden…
        </div>
      @endif
    </div>
  </div>
</div>

{{-- Animatie CSS + progress logic --}}
<style>
@keyframes progress-pop { 0%{transform:scale(1)} 40%{transform:scale(1.15)} 100%{transform:scale(1)} }
.progress-pop { animation: progress-pop .28s ease-out; }
</style>

@verbatim
<script>
(function () {
  function el(id){ return document.getElementById(id); }
  const TOTAL_STEPS = 3;

    function setIcon(i, type){
        const wrap = el('step-icon-' + i);
        if (!wrap) return;

        // Cache het oorspronkelijke (default) icoon éénmalig
        if (!wrap.dataset.defaultIcon) {
            wrap.dataset.defaultIcon = wrap.innerHTML;
        }

        if (type === 'check') {
            wrap.innerHTML = '<i class="fa-solid fa-check fa-sm text-white"></i>';
        } else {
            wrap.innerHTML = wrap.dataset.defaultIcon;
            // Zorg dat het default icoon niet wit blijft
            const ic = wrap.querySelector('i');
            if (ic) ic.classList.remove('text-white');
        }
    }

  function setDotState(i, state){
    const dot = el('step-dot-' + i);
    if (!dot) return;

    // Reset base
    dot.classList.remove('progress-pop');
    dot.classList.remove('bg-[#215558]', 'border-[#215558]/0');
    dot.classList.add('border-2');

    if (state === 'done'){
      // Volledige kleur + check
      dot.classList.remove('bg-[#215558]/10');
      dot.classList.add('bg-[#215558]', 'border-[#215558]', 'progress-pop');
      setIcon(i, 'check');
      setTimeout(()=> dot.classList.remove('progress-pop'), 320);
    } else if (state === 'current'){
      // Alleen border actief, subtiele fill, originele icon
      dot.classList.add('bg-[#215558]/10', 'border-[#215558]');
      setIcon(i, 'default');
    } else {
      // upcoming/inactive
      dot.classList.add('bg-[#215558]/10');
      dot.classList.add('border-[#215558]/0');
      setIcon(i, 'default');
    }
  }

  function setBarFill(i, filled){
    const bar = el('step-bar-fill-' + i);
    if (!bar) return;
    // animate width via inline style
    requestAnimationFrame(() => {
      bar.style.width = filled ? '100%' : '0%';
    });
  }

  function applyProgress(step){
    for (let i = 1; i <= TOTAL_STEPS; i++){
      if (i < step) {
        setDotState(i, 'done');
      } else if (i === step) {
        setDotState(i, 'current');
      } else {
        setDotState(i, 'inactive');
      }
    }
    // Bars vullen tussen voltooide en huidige
    setBarFill(1, step >= 2);
    setBarFill(2, step >= 3);
  }

    function detectStepFromDom(){
    const wrap = document.getElementById('onboarding-step');

    // 1) Probeer child met data-onboarding-step (HTMX partial)
    const child = wrap ? wrap.querySelector('[data-onboarding-step]') : null;
    if (child) {
        const n = parseInt(child.getAttribute('data-onboarding-step') || '1', 10);
        if (!isNaN(n)) return Math.max(1, Math.min(TOTAL_STEPS, n));
    }

    // 2) Fallback voor SSR (hard reload / direct URL)
    if (wrap && wrap.dataset.currentStep) {
        const m = parseInt(wrap.dataset.currentStep, 10);
        if (!isNaN(m)) return Math.max(1, Math.min(TOTAL_STEPS, m));
    }

    // 3) Default
    return 1;
    }

  // Init (SSR of init-load)
  applyProgress(detectStepFromDom());

  // HTMX swaps
  document.body.addEventListener('htmx:afterSwap', function (e) {
    if (!e?.detail?.target || e.detail.target.id !== 'onboarding-step') return;
    applyProgress(detectStepFromDom());
  });
})();
</script>
@endverbatim
@verbatim
<script>
document.body.addEventListener('onboarding:clearLocal', function () {
  ['onb.step1','onb.step2','onb.step3'].forEach(k => localStorage.removeItem(k));
});
</script>
@endverbatim
@endsection
