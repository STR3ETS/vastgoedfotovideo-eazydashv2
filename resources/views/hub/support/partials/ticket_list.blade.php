<div class="flex flex-col gap-2">
  @forelse($tickets as $t)
    <div class="ticket-item p-3 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
      <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-[#215558] font-semibold">{{ $t->subject }}</p>
        <span class="text-[11px] px-2 py-1 rounded-full border border-gray-200 text-gray-600">
          {{ str_replace('_',' ', $t->status) }}
        </span>
      </div>
      <p class="mt-1 text-xs text-gray-500">
        {{ optional($t->user)->name ?? 'Onbekend' }} • {{ $t->created_at->diffForHumans() }}
        @if(!empty($t->category)) • {{ $t->category }} @endif
      </p>
      @php $excerpt = \Illuminate\Support\Str::limit(strip_tags($t->message), 180); @endphp
      <p class="mt-2 text-sm text-gray-700">{{ $excerpt }}</p>
    </div>
  @empty
    <div class="text-sm text-gray-500">Geen tickets gevonden voor deze status.</div>
  @endforelse

  @if(method_exists($tickets, 'hasPages') && $tickets->hasPages())
    <div class="mt-2">
      {{ $tickets->onEachSide(1)->links() }}
    </div>
  @endif
</div>
