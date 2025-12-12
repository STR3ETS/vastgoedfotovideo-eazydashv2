@php
  $statusCounts = $statusCounts ?? [];

  $colors = [
      'preview' => [
          'bg'    => 'bg-[#e0d4ff]',
          'border'=> 'border-[#c3b4f0]',
          'text'  => 'text-[#4c2a9b]',
          'dot'   => 'bg-[#4c2a9b]',
      ],
      'waiting_customer' => [
          'bg'    => 'bg-[#b3e6ff]',
          'border'=> 'border-[#92cbe8]',
          'text'  => 'text-[#0f6199]',
          'dot'   => 'bg-[#0f6199]',
      ],
      'offerte' => [
          'bg'    => 'bg-[#ffdfb3]',
          'border'=> 'border-[#e8c392]',
          'text'  => 'text-[#a0570f]',
          'dot'   => 'bg-[#a0570f]',
      ],
  ];
@endphp

<div class="flex flex-wrap items-center justify-between p-2 rounded-full bg-[#f3f8f8]">
  <ul class="flex items-center gap-2 w-full">
    @foreach($statusMap as $status)
      @php
        $value = $status['value'];
        $label = $status['label'];

        $c = $colors[$value] ?? [
            'bg'    => 'bg-gray-50',
            'border'=> 'border-gray-200',
            'text'  => 'text-[#215558]',
            'dot'   => 'bg-[#215558]',
        ];

        $count = $statusCounts[$value] ?? 0;
      @endphp

      <li class="w-1/3">
        <div class="w-full flex items-center justify-between py-2 px-3 rounded-full text-xs font-semibold border
                    cursor-grab active:cursor-grabbing select-none
                    {{ $c['bg'] }} {{ $c['border'] }} {{ $c['text'] }}"
             draggable="true"
             x-on:dragstart="onStatusDragStart('{{ $value }}', '{{ $label }}', $el, $event)"
             x-on:dragend="onStatusDragEnd($el)">

          <div class="w-full flex items-center justify-between gap-2">
            <span class="text-xs font-semibold">
              {{ $label }}
            </span>

            <span class="text-[11px] opacity-80"
                  x-text="formatStatusCount('{{ $value }}')">
              {{ __(
                $count === 1
                  ? 'projecten.status_counts.singular'
                  : 'projecten.status_counts.plural',
                ['count' => $count]
              ) }}
            </span>
          </div>
        </div>
      </li>
    @endforeach
  </ul>
</div>
