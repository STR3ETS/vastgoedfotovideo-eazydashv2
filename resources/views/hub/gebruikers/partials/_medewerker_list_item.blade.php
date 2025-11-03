@php($oob = $oob ?? false)

<a
    id="user-item-{{ $m->id }}"
    @if($oob) hx-swap-oob="true" @endif
    href="{{ route('support.gebruikers.medewerkers.show', $m) }}"
    class="item-user w-full p-3 rounded-xl flex items-center justify-between hover:bg-gray-200 transition duration-300"
    hx-get="{{ route('support.gebruikers.medewerkers.show', $m) }}"
    hx-target="#user-detail-card"
    hx-swap="innerHTML transition:true"
    hx-push-url="true"
    data-id="{{ $m->id }}"
>
    <div class="flex items-center gap-1">
        <i class="min-w-[16px] fa-solid fa-user text-[#215558] fa-sm"></i>
        <p class="px-1 text-sm text-[#215558] font-semibold">{{ $m->name }}</p>
    </div>

    {{-- Verwijderknop -> opent confirm overlay (GEEN directe hx-delete hier) --}}
    <button
        type="button"
        class="w-8 h-8 cursor-pointer bg-red-100 hover:bg-red-200 transition duration-300 rounded-full flex items-center justify-center relative group"
        aria-label="Verwijder medewerker {{ $m->name }}"

        {{-- Benodigde data voor de overlay/JS --}}
        data-delete-url="{{ route('support.gebruikers.medewerkers.destroy', $m) }}"
        data-delete-target="#medewerkers-list"
        data-user-name="{{ e($m->name) }}"
        data-user-id="{{ $m->id }}"

        onclick="event.preventDefault(); event.stopPropagation(); window.openDeleteConfirm && window.openDeleteConfirm(this);"
    >
        <i class="fa-solid fa-sm fa-trash-can text-red-500"></i>
        <!-- Tooltip -->
        <div
        class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-[135%] top-1/2 -translate-y-1/2
                opacity-0 invisible translate-x-1 pointer-events-none
                group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto
                transition-all duration-300 ease-out z-10">
            <p class="text-[#215558] text-xs font-semibold whitespace-nowrap">{{ __('gebruikers.confirm.tooltip_delete') }}</p>
        </div>
    </button>
</a>
