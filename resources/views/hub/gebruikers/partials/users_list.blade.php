@php
  // verwacht: $users, $rol, $rolLabel, $q
  $count = isset($users) ? $users->count() : 0;
@endphp

<div class="p-2">
  <div class="px-2 pb-2 flex items-center justify-between">
    <div class="flex items-center gap-2">
      <p class="text-sm font-black text-[#215558]">{{ $rolLabel ?? 'Gebruikers' }}</p>
      <span class="text-xs font-semibold px-2 py-1 rounded-full bg-gray-100 border border-gray-200 text-[#215558]">
        {{ $count }}
      </span>
    </div>

    @if(!empty($q))
      <span class="text-xs font-semibold text-gray-500">
        Zoekterm: <span class="text-[#215558]">{{ $q }}</span>
      </span>
    @endif
  </div>

  <div class="flex flex-col gap-1">
    @if($count === 0)
      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
        <p class="text-sm font-semibold text-gray-500">Geen gebruikers gevonden.</p>
      </div>
    @else
      @foreach($users as $u)
        @include('hub.gebruikers.partials._user_list_item', ['u' => $u, 'rol' => $rol])
      @endforeach
    @endif
  </div>
</div>
