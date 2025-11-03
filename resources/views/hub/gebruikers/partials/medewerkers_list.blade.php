<div class="flex flex-col gap-1" id="medewerkers-list">
  @if($medewerkers->isEmpty())
    <p class="text-[#215558] text-xs font-semibold opacity-75 p-3">{{ __('gebruikers.list.empty') }}</p>
  @else
    @foreach($medewerkers as $m)
      @include('hub.gebruikers.partials._medewerker_list_item', ['m' => $m])
    @endforeach
  @endif
</div>
