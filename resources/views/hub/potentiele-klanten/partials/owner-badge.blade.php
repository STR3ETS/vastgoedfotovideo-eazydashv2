@php
    use Illuminate\Support\Str;

    $owner = $aanvraag->owner ?? null;

    // owner_id is de source of truth
    $ownerId   = $aanvraag->owner_id ?? ($owner->id ?? null);

    // Naam uit de relatie
    $ownerName = $owner?->name ?? '';

    // Bepaal memoji zoals bovenin bij $assigneesById
    $ownerAvatar = '';

    if ($ownerId && $ownerName) {
        $avatarKey = $owner->memoji_key
            ?? Str::lower(Str::before($ownerName, ' ')); // eerste naam als key

        $ownerAvatar = "/assets/eazyonline/memojis/{$avatarKey}.webp";
    }

    // Fallback naar default memoji als er wel een owner is maar geen specifieke key
    if (!$ownerAvatar && $ownerId) {
        $ownerAvatar = '/assets/eazyonline/memojis/default.webp';
    }

    $hasOwner = !empty($ownerId) && !empty($ownerName);
@endphp

<span
    class="px-2 h-8 rounded-full border flex items-center justify-center
           {{ $hasOwner ? 'bg-white border-[#215558]/25' : 'bg-[#f3f8f8] border-dashed border-[#215558]/30' }}"
    data-owner-badge
    data-owner-id="{{ $ownerId ?? '' }}"
>
    {{-- Altijd een img houden voor JS, alleen verbergen als er nog geen owner is --}}
    <img
        data-owner-badge-avatar
        src="{{ $ownerAvatar }}"
        class="max-h-[80%] {{ $hasOwner ? '' : 'hidden' }}"
        alt=""
    >

    @unless($hasOwner)
        <span
            class="w-4 h-4 rounded-full flex items-center justify-center mr-0.5"
            data-owner-badge-placeholder
        >
            <i class="fa-regular fa-user text-[11px] text-[#215558]/40 mb-0.5"></i>
        </span>
    @endunless

    <span
        data-owner-badge-name
        class="text-xs font-semibold ml-1 whitespace-nowrap
               {{ $hasOwner ? 'text-[#215558]' : 'text-[#215558]/60' }}"
    >
        @if($hasOwner)
            {{ $ownerName }}
        @elseif($ownerId)
            Nog niet toegewezen
        @else
            Niet toegewezen
        @endif
    </span>
</span>
