@php
  /** @var \App\Models\Company $company */
  $uid = 'cmp-'.$company->id;
@endphp

<div class="mb-2 bg-gray-100 rounded-xl border border-gray-200 px-3 flex items-center">
  <div class="w-[16px]">
    <i class="fa-solid fa-magnifying-glass fa-sm text-gray-500"></i>
  </div>
  <input type="text"
        name="q"
        value="{{ $q }}"
        placeholder="{{ __('gebruikers.search.placeholder') }}"
        class="py-2 w-[calc(100%-16px)] text-sm text-gray-500 pl-3 font-medium outline-none"
        hx-get="{{ route('support.gebruikers.bedrijven.personen', $company) }}"
        hx-target="#person-picker-panel-body-{{ $uid }}"
        hx-swap="innerHTML transition:true"
        hx-trigger="keyup changed delay:250ms">
</div>

@if($users->isEmpty())
  <div class="text-[#215558] text-xs font-semibold opacity-75 p-3">Geen vrije personen gevonden…</div>
@else
  <div class="max-h-[320px] overflow-y-auto flex flex-col gap-1">
    @foreach($users as $u)
      <button type="button"
              class="w-full p-3 rounded-xl flex items-center justify-between gap-3 hover:bg-gray-200 transition duration-300 cursor-pointer"
              hx-post="{{ route('support.gebruikers.bedrijven.personen.koppel', $company) }}"
              hx-vals='{"user_id":"{{ $u->id }}"}'
              hx-target="#company-persons-{{ $uid }}"
              hx-swap="beforeend transition:true"
              hx-headers='{"X-Requested-With":"XMLHttpRequest","Accept":"text/html"}'
              hx-push-url="false"
              hx-replace-url="false"

              hx-on::after-request="
                const xhr = event.detail?.xhr;
                if (xhr && xhr.status >= 200 && xhr.status < 300) {
                  // 1) verwijder de geklikte optie uit de lijst
                  this.remove();

                  // 2) sluit het panel zacht
                  const panel = this.closest('[data-person-picker-panel]');
                  if (panel) {
                    panel.classList.add('opacity-0','translate-y-1','pointer-events-none');
                    panel.classList.remove('__open');
                  }
                  // 3) GEEN extra fetch hier nodig — server stuurt OOB refresh mee
                }
              "
      >
        <div class="flex items-center gap-1">
          <i class="min-w-[16px] fa-solid fa-user text-[#215558] text-[12.5px] mt-[0.20rem]"></i>
          <p class="px-1 text-sm text-[#215558] font-semibold">{{ $u->name }}</p>
        </div>
      </button>
    @endforeach
  </div>
@endif
