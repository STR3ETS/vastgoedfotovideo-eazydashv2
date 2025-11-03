<div id="companies-list">
  @if($companies->isEmpty())
    <div class="text-[#215558] text-xs font-semibold opacity-75 p-3">{{ __('gebruikers.bedrijven.empty') }}</div>
  @else
    <div class="flex flex-col gap-1">
      @foreach($companies as $c)
        @include('hub.gebruikers.partials._bedrijf_list_item', ['c' => $c])
      @endforeach
    </div>
  @endif
</div>
