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

<div class="mt-3 space-y-2">
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

    <div class="flex items-center justify-between gap-2 p-4 text-xs font-semibold rounded-xl border
                cursor-grab active:cursor-grabbing select-none
                {{ $c['bg'] }} {{ $c['border'] }} {{ $c['text'] }}"
         draggable="true"
         x-on:dragstart="onStatusDragStart('{{ $value }}', '{{ $label }}', $el, $event)"
         x-on:dragend="onStatusDragEnd($el)">

      <div class="flex flex-col">
        <span class="text-sm font-semibold">
          {{ $label }}
        </span>

        <span class="text-xs opacity-80 mt-1"
              x-text="formatStatusCount('{{ $value }}')">
          {{ __(
            $count === 1
              ? 'projecten.status_counts.singular'
              : 'projecten.status_counts.plural',
            ['count' => $count]
          ) }}
        </span>
      </div>

      <div class="w-1.5 h-1.5 rounded-full {{ $c['dot'] }}"></div>
    </div>
  @endforeach
</div>
