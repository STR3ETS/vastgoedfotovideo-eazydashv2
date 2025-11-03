{{-- resources/views/hub/gebruikers/partials/_bedrijf_persoon_row.blade.php --}}
@php
/** @var \App\Models\User $u */
/** @var \App\Models\Company $company */
/** @var bool|null $asLink */
$asLink = $asLink ?? true;

/** Context */
$isTeam = ($ctx ?? null) === 'team';
$isSelf = auth()->id() === $u->id;

/* Deze twee kun je vanuit de aanroeper meegeven; zo niet, tellen we hier. */
$companyAdminCount  = $companyAdminCount  ?? \App\Models\User::where('company_id', $company->id)->where('is_company_admin', true)->count();
$companyMemberCount = $companyMemberCount ?? \App\Models\User::where('company_id', $company->id)->count();

/* Regels:
   - kroon uit als: self OF (user is admin én hij is de laatste admin)
   - ontkoppelen uit als: self OF (enige member) OF (user is admin én laatste admin)
*/
$isLastAdmin     = $u->is_company_admin && $companyAdminCount <= 1;
$isOnlyMember    = $companyMemberCount <= 1;

$crownDisabled   = $isSelf || $isLastAdmin;
$unlinkDisabled  = $isSelf || $isOnlyMember || $isLastAdmin;

/* Styling */
$baseClasses = 'item-user w-full p-3 rounded-xl flex items-center justify-between gap-3 transition duration-300 block';
$linkClasses = $asLink ? ' hover:bg-gray-200 cursor-pointer' : ' bg-gray-50 cursor-default';
$wrapperClasses = $baseClasses.$linkClasses;

/* In Instellingen > Team: "Jouw account" altijd bovenaan + extra spacing
   Let op: parent container moet display:flex + flex-col (of grid) hebben voor order-* te werken. */
$rowPositionClasses = ($isTeam && $isSelf) ? ' order-first mb-4' : '';
@endphp

@if($asLink)
  <a href="{{ route('support.gebruikers.klanten.show', $u) }}"
     class="{{ $wrapperClasses }}{{ $rowPositionClasses }}"
     id="person-row-{{ $u->id }}">
@else
  <div class="{{ $wrapperClasses }}{{ $rowPositionClasses }}"
       id="person-row-{{ $u->id }}">
@endif
  <div class="w-full flex items-center justify-between">
    <span class="flex items-center gap-1">
      <i class="min-w-[16px] fa-solid fa-user text-[#215558] text-[12.5px] mt-[0.20rem]"></i>

      <span class="px-1 text-sm text-[#215558] font-semibold flex items-center">
        <div class="px-2">
          <p>{{ $u->name }}</p>
          <p class="text-xs opacity-50">{{ $u->email }}</p>
        </div>

        @if($isTeam && $isSelf)
          <span
            class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold
                   bg-[#0F9B9F]/10 text-[#0F9B9F] border border-[#0F9B9F]/30 select-none"
            aria-label="Jij"
            title="Jij">{{ __('instellingen.invite.you') }}</span>
        @endif
      </span>
    </span>

    <span class="flex items-center gap-2">
      {{-- Kroon --}}
      @include('hub.gebruikers.partials._bedrijf_persoon_crown', [
        'u'                   => $u,
        'company'             => $company,
        'ctx'                 => $ctx ?? null,              // laat doorgeven vanuit Team
        'disabled'            => $crownDisabled,            // << belangrijk
        'companyAdminCount'   => $companyAdminCount,        // optioneel informatief
        'companyMemberCount'  => $companyMemberCount,       // optioneel informatief
      ])

      {{-- Ontkoppelen --}}
      @if($unlinkDisabled)
        <button type="button"
                class="w-7 h-7 rounded-full bg-red-100 text-red-500 flex items-center justify-center opacity-50 cursor-not-allowed"
                aria-disabled="true">
          <i class="fa-solid fa-minus text-[11px]"></i>
        </button>
      @else
        <button type="button"
                class="w-7 h-7 rounded-full bg-red-100 hover:bg-red-200 transition duration-300 flex items-center justify-center relative group cursor-pointer"
                onclick="event.preventDefault(); event.stopPropagation();"
                hx-delete="{{ route('support.gebruikers.bedrijven.personen.ontkoppel', [$company, $u]) }}"
                hx-headers='{"X-Requested-With":"XMLHttpRequest","Accept":"text/html","X-CSRF-TOKEN":"{{ csrf_token() }}"}'
                hx-on::after-request="
                  const xhr = event.detail?.xhr;
                  if (xhr && xhr.status >= 200 && xhr.status < 300) {
                    document.getElementById('person-row-{{ $u->id }}')?.remove();
                  }
                ">
          <i class="fa-solid fa-minus text-red-500 text-[11px]"></i>

          <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2 opacity-0 invisible translate-x-1 pointer-events-none group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 transition-all duration-300 ease-out z-10">
            <p class="text-[#215558] text-xs font-semibold whitespace-nowrap">
              {{ __('gebruikers.actions.unassign_user_company') }}
            </p>
          </div>
        </button>
      @endif
    </span>
  </div>

@if($asLink)
  </a>
@else
  </div>
@endif