@php
  /** @var array $statusMap label => value */
  $statusMap = $statusMap ?? [
    'Prospect' => 'prospect',
    'Contact'  => 'contact',
    'Intake'   => 'intake',
    'Dead'     => 'dead',
    'Lead'     => 'lead',
  ];

  $colors = [
    'prospect' => [
      'bg'    => 'bg-[#b3e6ff]',
      'border'=> 'border-[#92cbe8]',
      'text'  => 'text-[#0f6199]',
      'dot'   => 'bg-[#0f6199]',
    ],
    'contact' => [
      'bg'    => 'bg-[#C2F0D5]',
      'border'=> 'border-[#a1d3b6]',
      'text'  => 'text-[#20603a]',
      'dot'   => 'bg-[#20603a]',
    ],
    'intake' => [
      'bg'    => 'bg-[#ffdfb3]',
      'border'=> 'border-[#e8c392]',
      'text'  => 'text-[#a0570f]',
      'dot'   => 'bg-[#a0570f]',
    ],
    'dead' => [
      'bg'    => 'bg-[#ffb3b3]',
      'border'=> 'border-[#e09494]',
      'text'  => 'text-[#8a2a2d]',
      'dot'   => 'bg-[#8a2a2d]',
    ],
    'lead' => [
      'bg'    => 'bg-[#e0d4ff]',
      'border'=> 'border-[#c3b4f0]',
      'text'  => 'text-[#4c2a9b]',
      'dot'   => 'bg-[#4c2a9b]',
    ],
  ];

  $statusCounts = $statusCounts ?? []; // ['prospect' => 3, ...]
@endphp

<ul class="mt-3 space-y-2">
  @foreach($statusMap as $label => $value)
    @php
      $c     = $colors[$value];
      $count = $statusCounts[$value] ?? 0;
    @endphp

    <li class="w-full">
      <div class="flex items-center justify-between gap-2 p-4 text-xs font-semibold rounded-xl border
                  cursor-grab active:cursor-grabbing select-none
                  {{ $c['bg'] }} {{ $c['border'] }} {{ $c['text'] }}"
           draggable="true"
           x-on:dragstart="onStatusDragStart('{{ $value }}', '{{ $label }}', $el, $event)"
           x-on:dragend="onStatusDragEnd($el)">

        <div class="flex flex-col">
          <span class="text-sm font-semibold">
            {{ __('potentiele_klanten.statuses.' . $value) }}
          </span>
          <span class="text-xs opacity-80 mt-1"
                x-text="formatStatusCount('{{ $value }}')">
            {{ __(
              $count === 1
                ? 'potentiele_klanten.status_counts.singular'
                : 'potentiele_klanten.status_counts.plural',
              ['count' => $count]
            ) }}
          </span>
        </div>

        <div class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></div>
      </div>
    </li>
  @endforeach
</ul>
