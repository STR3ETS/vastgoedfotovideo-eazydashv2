<div class="mt-3 grid grid-cols-1 gap-2">
  @forelse($aanvragen as $aanvraag)
    @include('hub.potentiele-klanten.partials.card', [
      'aanvraag'       => $aanvraag,
      'statusByValue'  => $statusByValue ?? [],
    ])
  @empty
    <div class="text-[#215558] text-xs font-semibold opacity-75">
      Nog geen aanvragen gevonden.
    </div>
  @endforelse
</div>

{{-- Client-side filter: geen resultaten voor de gekozen status(sen) --}}
<div
  class="text-[#215558] text-xs font-semibold opacity-75"
  x-show="activeFilters.length && !hasVisibleCards"
>
  Geen resultaten gevonden voor deze status.
</div>