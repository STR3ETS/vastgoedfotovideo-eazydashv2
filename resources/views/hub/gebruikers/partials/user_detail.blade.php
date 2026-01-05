@php
  $isAdmin = (auth()->user()->rol ?? null) === 'admin';
  $isEdit  = request()->query('mode') === 'edit' && $isAdmin;

  $roles = [
    ['value' => 'admin',          'label' => 'Admin'],
    ['value' => 'klant',          'label' => 'Klant'],
    ['value' => 'team-manager',   'label' => 'Team manager'],
    ['value' => 'client-manager', 'label' => 'Klant manager'],
    ['value' => 'fotograaf',      'label' => 'Fotograaf'],
  ];

  $days = [
    'monday'    => 'Maandag',
    'tuesday'   => 'Dinsdag',
    'wednesday' => 'Woensdag',
    'thursday'  => 'Donderdag',
    'friday'    => 'Vrijdag',
    'saturday'  => 'Zaterdag',
    'sunday'    => 'Zondag',
  ];

  $work = is_array($user->work_hours ?? null) ? $user->work_hours : [];
@endphp

<div class="p-4 flex flex-col gap-4">
  <div class="flex items-start justify-between gap-3">
    <div class="min-w-0">
      <h2 class="text-lg font-black text-[#215558] truncate">{{ $user->name }}</h2>
      <p class="text-sm text-gray-500 truncate">{{ $user->email }}</p>
    </div>

    @if($isAdmin && !$isEdit)
      <button
        type="button"
        class="w-8 h-8 bg-gray-200 hover:bg-gray-300 transition duration-300 cursor-pointer rounded-full flex items-center justify-center"
        hx-get="{{ route('support.gebruikers.show', $user->id) }}?mode=edit"
        hx-target="#user-detail-card"
        hx-swap="innerHTML"
        aria-label="Gebruiker bewerken"
      >
        <i class="fa-solid fa-pen text-[#215558]"></i>
      </button>
    @endif
  </div>

  @if($isEdit)
    <form
      class="grid gap-2"
      hx-patch="{{ route('support.gebruikers.update', $user->id) }}"
      hx-target="#user-detail-card"
      hx-swap="innerHTML"
    >
      @csrf

      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Rol</label>
        <select
          name="rol"
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
          required
        >
          @foreach($roles as $r)
            <option value="{{ $r['value'] }}" @selected(old('rol', $user->rol) === $r['value'])>{{ $r['label'] }}</option>
          @endforeach
        </select>
      </div>

      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Naam</label>
        <input
          name="name"
          value="{{ old('name', $user->name) }}"
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
          required
        />
      </div>

      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
        <label class="block text-xs font-semibold text-gray-500 mb-1">E-mail</label>
        <input
          type="email"
          name="email"
          value="{{ old('email', $user->email) }}"
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
          required
        />
      </div>

      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Adres</label>
        <input
          name="address"
          value="{{ old('address', $user->address) }}"
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
        />
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
          <label class="block text-xs font-semibold text-gray-500 mb-1">Postcode</label>
          <input
            name="postal_code"
            value="{{ old('postal_code', $user->postal_code) }}"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
          />
        </div>

        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
          <label class="block text-xs font-semibold text-gray-500 mb-1">Stad</label>
          <input
            name="city"
            value="{{ old('city', $user->city) }}"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
          />
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
          <label class="block text-xs font-semibold text-gray-500 mb-1">Provincie</label>
          <input
            name="state_province"
            value="{{ old('state_province', $user->state_province) }}"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
          />
        </div>

        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
          <label class="block text-xs font-semibold text-gray-500 mb-1">Telefoonnummer</label>
          <input
            name="phone"
            value="{{ old('phone', $user->phone) }}"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
          />
        </div>
      </div>

      {{-- ✅ Werktijden --}}
      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200 mt-1">
        <p class="text-xs font-semibold text-gray-500 mb-2">Werktijden</p>

        <div class="grid gap-2">
          @foreach($days as $key => $label)
            @php
              $startVal = old("work_hours.$key.start", data_get($work, "$key.start"));
              $endVal   = old("work_hours.$key.end", data_get($work, "$key.end"));
            @endphp

            <div class="grid grid-cols-3 gap-2 items-center">
              <p class="text-sm font-semibold text-[#215558]">{{ $label }}</p>

              <input
                type="time"
                name="work_hours[{{ $key }}][start]"
                value="{{ $startVal }}"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
              />

              <input
                type="time"
                name="work_hours[{{ $key }}][end]"
                value="{{ $endVal }}"
                class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[#0F9B9F]/30"
              />
            </div>
          @endforeach
        </div>

        <p class="text-xs text-gray-400 mt-2">Laat een dag leeg als er niet gewerkt wordt.</p>
      </div>

      <div class="flex items-center gap-2 mt-2">
        <button
          type="submit"
          class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300"
        >
          Opslaan
        </button>

        <button
          type="button"
          class="bg-gray-200 hover:bg-gray-300 cursor-pointer text-center w-full text-gray-600 text-base font-semibold px-6 py-3 rounded-full transition duration-300"
          hx-get="{{ route('support.gebruikers.show', $user->id) }}"
          hx-target="#user-detail-card"
          hx-swap="innerHTML"
        >
          Annuleren
        </button>
      </div>
    </form>
  @else
    <div class="grid gap-2">
      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
        <p class="text-xs font-semibold text-gray-500 mb-1">Rol</p>
        <p class="text-sm font-semibold text-[#215558]">{{ $user->rol }}</p>
      </div>

      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
        <p class="text-xs font-semibold text-gray-500 mb-1">Adres</p>
        <p class="text-sm font-semibold text-[#215558]">{{ $user->address ?: '-' }}</p>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
          <p class="text-xs font-semibold text-gray-500 mb-1">Postcode</p>
          <p class="text-sm font-semibold text-[#215558]">{{ $user->postal_code ?: '-' }}</p>
        </div>
        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
          <p class="text-xs font-semibold text-gray-500 mb-1">Stad</p>
          <p class="text-sm font-semibold text-[#215558]">{{ $user->city ?: '-' }}</p>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
          <p class="text-xs font-semibold text-gray-500 mb-1">Provincie</p>
          <p class="text-sm font-semibold text-[#215558]">{{ $user->state_province ?: '-' }}</p>
        </div>
        <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
          <p class="text-xs font-semibold text-gray-500 mb-1">Telefoonnummer</p>
          <p class="text-sm font-semibold text-[#215558]">{{ $user->phone ?: '-' }}</p>
        </div>
      </div>

      {{-- ✅ Werktijden view --}}
      <div class="p-3 rounded-xl bg-gray-50 border border-gray-200">
        <p class="text-xs font-semibold text-gray-500 mb-2">Werktijden</p>

        <div class="grid gap-1">
          @foreach($days as $key => $label)
            @php
              $s = data_get($work, "$key.start");
              $e = data_get($work, "$key.end");
              $line = ($s && $e) ? "$s - $e" : '-';
            @endphp

            <div class="flex items-center justify-between">
              <p class="text-sm font-semibold text-[#215558]">{{ $label }}</p>
              <p class="text-sm font-semibold text-[#215558]">{{ $line }}</p>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    @if($isAdmin)
      <div class="flex items-center gap-2">
        <button
          type="button"
          class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold px-6 py-3 rounded-full transition duration-300"
          onclick="window.openConfirmDelete('{{ route('support.gebruikers.destroy', $user->id) }}', '{{ addslashes($user->name) }}')"
        >
          Verwijderen
        </button>
      </div>
    @endif
  @endif
</div>
