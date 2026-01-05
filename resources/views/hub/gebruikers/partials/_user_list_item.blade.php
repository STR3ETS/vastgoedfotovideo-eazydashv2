<a
  href="{{ route('support.gebruikers.show', $u->id) }}"
  class="w-full p-3 rounded-xl flex items-center justify-between hover:bg-gray-200 transition duration-300 cursor-pointer"
  hx-get="{{ route('support.gebruikers.show', $u->id) }}"
  hx-target="#user-detail-card"
  hx-swap="innerHTML"
  hx-push-url="true"
  hx-on::after-request="document.querySelector('#user-detail-card')?.classList.remove('hidden')"
>
  <div class="min-w-0">
    <p class="text-sm font-semibold text-[#215558] truncate">{{ $u->name }}</p>
    <p class="text-xs text-gray-500 truncate">{{ $u->email }}</p>
  </div>

  <i class="fa-solid fa-chevron-right text-gray-300"></i>
</a>
