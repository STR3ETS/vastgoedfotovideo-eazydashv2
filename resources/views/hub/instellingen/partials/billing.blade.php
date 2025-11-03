<div class="p-4">
  @php
    /** @var \App\Models\Company|null $company */
    $company   = auth()->user()->company ?? null;
    $total     = max(1, (int)($company?->trial_days ?? 30));
    $remaining = (int)($company?->trialRemainingDays() ?? 0);
    $remaining = max(0, min($remaining, $total));
    $pct       = (int) round(($remaining / $total) * 100);
    $start     = $company?->trial_starts_at;
    $end       = $company?->trial_ends_at;
  @endphp

  @if($remaining === 0)
    
  @else
    <div class="bg-gray-100 p-4 rounded-xl">
        <div class="flex items-center justify-between gap-3">
        <div class="text-base text-cyan-900 font-bold flex items-center gap-2">
            Mijn Eazyonline Abbonement
        </div>
        <div class="flex items-center gap-2">
            <span class="py-1 px-2 rounded-full bg-gray-200 border border-gray-300 text-[10px] text-gray-600 font-semibold">Nog {{ $remaining }} dagen resterend</span>
            <span class="py-1 px-2 rounded-full bg-green-200 border border-green-300 text-[10px] text-green-600 font-semibold">Proefperiode</span>
        </div>
        </div>

        <div class="mt-3">
        <div class="w-full h-3 bg-gray-300 rounded-full overflow-hidden"
            role="progressbar"
            aria-label="Resterende proefperiode"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-valuenow="{{ $pct }}">
            <div class="h-full bg-[#0F9B9F] transition-[width] duration-500 ease-out"
                style="width: {{ $pct }}%;"></div>
        </div>
        </div>
        <div class="w-full flex items-center justify-between mt-3">
            <img src="/assets/memoji-row.png" class="max-h-[48px]">
            <button id="company-save-btn" type="submit"
                    class="relative min-w-[200px] bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300 mt-3">
                <span id="company-spinner" class="absolute -left-6 top-1/2 -translate-y-1/2 hidden">
                <i class="fa-solid fa-spinner fa-spin"></i>
                </span>
                <span id="company-check" class="absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 hidden">
                <i class="fa-solid fa-check text-white"></i>
                </span>
                <span id="company-btn-label">
                {{ __('instellingen.actions.customize') }}
                </span>
            </button>
        </div>
    </div>
  @endif
</div>
