@php
/** @var \App\Models\User $u */
/** @var \App\Models\Company $company */
/** @var bool|null $disabled */
$isSelf   = auth()->id() === $u->id;
$disabled = ($disabled ?? false) || $isSelf; // self altijd disabled
$icon     = 'fa-solid fa-sm fa-crown transition duration-300';
@endphp

@if($disabled)
  <span class="cursor-not-allowed opacity-50" aria-disabled="true">
    <i class="{{ $icon }} {{ $u->is_company_admin ? 'text-yellow-500' : 'text-gray-300' }}"></i>
  </span>
@else
  <button
    type="button"
    onclick="event.preventDefault(); event.stopPropagation();"
    class="cursor-pointer relative group"
    hx-post="{{ route('support.gebruikers.bedrijven.admin.toggle', [$company, $u]) }}"
    hx-target="#admin-crown-{{ $u->id }}"
    hx-swap="outerHTML transition:true"
    hx-headers='{"X-Requested-With":"XMLHttpRequest","Accept":"text/html","X-CSRF-TOKEN":"{{ csrf_token() }}"}'
    @if(!empty($ctx)) hx-vals='{"ctx":"{{ $ctx }}"}' @endif
    id="admin-crown-{{ $u->id }}"
  >
    <i class="{{ $icon }} {{ $u->is_company_admin ? 'text-yellow-500 hover:text-yellow-600' : 'text-gray-300 hover:text-gray-400' }}"></i>
    <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2 opacity-0 invisible translate-x-1 pointer-events-none group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 transition-all duration-300 ease-out z-10">
      <p class="text-[#215558] text-xs font-semibold whitespace-nowrap">
        {{ $u->is_company_admin ? __('gebruikers.actions.unassign_user_admin') : __('gebruikers.actions.assign_user_admin') }}
      </p>
    </div>
  </button>
@endif
