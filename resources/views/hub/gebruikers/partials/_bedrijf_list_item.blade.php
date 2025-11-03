@php
    /** @var \App\Models\Company $c */
    $oob = $oob ?? false;
@endphp

<div id="company-{{ $c->id }}"
     class="item-company w-full p-3 rounded-xl flex items-center justify-between gap-3 hover:bg-gray-200 transition duration-300 cursor-pointer"
     @if($oob) hx-swap-oob="true" @endif
     hx-get="{{ route('support.gebruikers.bedrijven.show', $c) }}"
     hx-target="#user-detail-card"
     hx-swap="innerHTML transition:true"
     hx-push-url="true">

  <div class="flex items-center gap-1">
    <i class="min-w-[16px] fa-solid fa-building-columns text-[#215558] fa-sm"></i>
    <p class="px-1 text-sm text-[#215558] font-semibold">{{ $c->name }}</p>
  </div>
</div>
