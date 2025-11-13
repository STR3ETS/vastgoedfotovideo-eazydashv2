@php
  /** @var \Illuminate\Support\Collection|\App\Models\Project[]|\Illuminate\Pagination\LengthAwarePaginator $projects */
@endphp

@if($projects->isEmpty())
  <p class="text-[#215558] text-xs font-semibold opacity-75">
    {{ __('projecten.empty') }}
  </p>
@else
  <div class="grid grid-cols-1 gap-2">
    @foreach($projects as $project)
      @include('hub.projecten.partials.card', [
        'project'       => $project,
        'statusByValue' => $statusByValue ?? [],
      ])
    @endforeach
  </div>
@endif
