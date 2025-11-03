@php
  /**
   * View: hub/instellingen/partials/team.blade.php
   * Vereist: $user (ingelogde gebruiker), optioneel $company (meegeven in controller)
   */
  /** @var \App\Models\User $user */
  /** @var \App\Models\Company|null $company */
  $company = $company ?? auth()->user()?->company;
@endphp

<div class="p-4 flex flex-col gap-5">
  {{-- Invite per e-mail --}}
  <div class="flex items-center justify-between">
    <div class="flex flex-col gap-6 min-w-[400px] w-full max-w-[400px]">
      <form
        hx-post="{{ route('support.instellingen.team.invite') }}"
        hx-target="#invite-flash-wrap"
        hx-swap="innerHTML transition:true"
        hx-headers='{"X-CSRF-TOKEN":"{{ csrf_token() }}","Accept":"text/html"}'
        class="flex flex-col gap-3"
      >
        <div>
          <label class="block text-xs text-[#215558] opacity-70 mb-1">
            {{ __('instellingen.invite.placeholders.email') }}
          </label>
          <input
            name="email"
            type="email"
            required
            placeholder="{{ __('instellingen.invite.fields.email') }}"
            class="w-full py-3 px-4 text-sm text-[#215558] font-semibold rounded-xl border border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300">
        </div>

        <button type="submit"
                class="relative mt-3 w-full bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
          <span class="absolute -left-6 top-1/2 -translate-y-1/2 hidden opacity-0" data-role="spinner">
            <i class="fa-solid fa-spinner fa-spin"></i>
          </span>
          <span class="absolute left-1/2 -translate-x-1/2 top-1/2 -translate-y-1/2 hidden" data-role="check">
            <i class="fa-solid fa-check text-white"></i>
          </span>
          <span data-role="label">{{ __('instellingen.invite.actions.send') }}</span>
        </button>
      </form>

      <div id="invite-flash-wrap" class="hidden"></div>
    </div>

    <img src="/assets/team-uitnodigen-memojis.png" alt="" class="max-w-[8rem]">
  </div>

  <hr class="border-gray-200">

  {{-- Teamleden (zonder picker) --}}
  @if(!$company)
    <div class="mt-2 text-xs text-[#215558] opacity-75 font-semibold">
      {{ __('gebruikers.empty.no_company_context') ?? 'Geen bedrijfscontext gevonden. Vraag een admin om je aan een bedrijf te koppelen.' }}
    </div>
  @else
    @php
      $uid = 'cmp-'.$company->id;

      $__gekoppeld = \App\Models\User::where('rol','klant')
        ->where('company_id', $company->id)
        ->orderBy('name')
        ->get(['id','name','email','is_company_admin']);

      $companyAdminCount  = \App\Models\User::where('company_id', $company->id)
        ->where('is_company_admin', true)
        ->count();

      $companyMemberCount = \App\Models\User::where('company_id', $company->id)
        ->count();
    @endphp

    <div id="company-persons-{{ $uid }}" class="flex flex-col gap-1">
      @forelse($__gekoppeld as $u)
        @include('hub.gebruikers.partials._bedrijf_persoon_row', [
          'u'                  => $u,
          'company'            => $company,
          'asLink'             => false,              // Team: nooit klikbaar
          'ctx'                => 'team',             // Context doorgeven voor juiste render (bv. e-mail tonen + "Jij"-badge)
          'companyAdminCount'  => $companyAdminCount, // Voor disable-logica
          'companyMemberCount' => $companyMemberCount,
        ])
      @empty
        <div class="text-[#215558] text-xs font-semibold opacity-75 p-3">
          {{ __('gebruikers.empty.no_persons_linked') ?? 'Nog geen personen gekoppeld.' }}
        </div>
      @endforelse
    </div>
  @endif
</div>

@verbatim
<script>
  // Invite form UX: label blijft zichtbaar tijdens load; verdwijnt pas bij succes (groene check)
  document.addEventListener('htmx:beforeRequest', function (e) {
    const form = e.target;
    if (!form.matches('form[hx-post*="/team/invite"]')) return;

    const btn     = form.querySelector('button[type="submit"]');
    const labelEl = btn?.querySelector('[data-role="label"]');
    const spinEl  = btn?.querySelector('[data-role="spinner"]');
    const checkEl = btn?.querySelector('[data-role="check"]');

    // remember original label & classes once
    if (btn && !btn.dataset.origClass) {
      btn.dataset.origClass = btn.className;
      btn.dataset.origHtml  = labelEl ? labelEl.textContent.trim() : btn.textContent.trim();
    }

    // loading state: label blijft zichtbaar, enkel spinner aan
    btn?.setAttribute('disabled', 'disabled');
    spinEl?.classList.remove('hidden');
    checkEl?.classList.add('hidden');
    labelEl?.classList.remove('opacity-0'); // << belangrijk: zichtbaar tijdens load

    // base (cyan) style + cursor-wait
    if (btn) {
      btn.className = 'relative mt-3 w-full bg-[#0F9B9F] hover:bg-[#215558] text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300';
    }
  });

  document.addEventListener('htmx:afterRequest', function (e) {
    const form = e.target;
    if (!form.matches('form[hx-post*="/team/invite"]')) return;

    const btn     = form.querySelector('button[type="submit"]');
    const labelEl = btn?.querySelector('[data-role="label"]');
    const spinEl  = btn?.querySelector('[data-role="spinner"]');
    const checkEl = btn?.querySelector('[data-role="check"]');

    // stop spinner
    spinEl?.classList.add('hidden');

    const xhr = e.detail && e.detail.xhr;
    const ok  = xhr && xhr.status >= 200 && xhr.status < 300;

    if (ok) {
      // Succes: verberg label NU pas, toon check, maak groen
      if (btn) {
        btn.removeAttribute('disabled');
        labelEl?.classList.add('opacity-0');     // << label verdwijnt pas hier
        checkEl?.classList.remove('hidden');

        btn.className = 'relative mt-3 w-full bg-green-500 cursor-default text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300';

        try { form.reset(); } catch(_) {}

        // Na 3s terug naar originele staat
        setTimeout(() => {
          if (!btn.isConnected) return;
          checkEl?.classList.add('hidden');
          labelEl?.classList.remove('opacity-0');
          if (btn.dataset.origClass) btn.className = btn.dataset.origClass;
          if (btn.dataset.origHtml && labelEl) labelEl.textContent = btn.dataset.origHtml;
          btn.removeAttribute('disabled');
        }, 3000);
      }
    } else {
      // Fout: label blijft zichtbaar, reset knopstijl
      if (btn) {
        btn.removeAttribute('disabled');
        checkEl?.classList.add('hidden');
        labelEl?.classList.remove('opacity-0');
        btn.className = btn.dataset.origClass || 'relative mt-3 w-full bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300';
      }
    }
  });
</script>
@endverbatim