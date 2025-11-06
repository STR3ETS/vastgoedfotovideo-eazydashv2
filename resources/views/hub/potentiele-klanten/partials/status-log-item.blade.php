@php
  /** @var \App\Models\AanvraagStatusLog $log */
  $valueToLabel = $valueToLabel ?? [];

  $fromLabel = $log->from_status
      ? ($valueToLabel[$log->from_status] ?? ucfirst($log->from_status))
      : '—';

  $toLabel = $log->to_status
      ? ($valueToLabel[$log->to_status] ?? ucfirst($log->to_status))
      : '—';

  $userName = optional($log->user)->name ?? 'Onbekend';
  $when     = optional($log->changed_at ?? $log->created_at)->format('d-m-Y H:i');

  $colorMap = [
      'prospect' => 'bg-[#b3e6ff] text-[#0f6199]',
      'contact'  => 'bg-[#C2F0D5] text-[#20603a]',
      'intake'   => 'bg-[#ffdfb3] text-[#a0570f]',
      'dead'     => 'bg-[#ffb3b3] text-[#8a2a2d]',
      'lead'     => 'bg-[#e0d4ff] text-[#4c2a9b]',
  ];

  $fromClasses = $colorMap[$log->from_status] ?? 'bg-slate-100 text-slate-700';
  $toClasses   = $colorMap[$log->to_status]   ?? 'bg-slate-100 text-slate-700';
@endphp

<li class="text-[11px] bg-white border border-gray-200 rounded-xl p-3">
    <div class="flex items-center justify-between gap-2">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-[#215558] truncate">
                {{ $when }}
            </span>
            <p class="text-xs font-semibold text-[#215558] truncate">
                Door: {{ $userName }}
            </p>
        </div>
        <div class="flex items-center gap-2 min-w-0">
            <span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full {{ $fromClasses }}">
                {{ $fromLabel }}
            </span>
            <i class="fa-solid fa-arrow-right-long text-[#215558] fa-sm mt-0.5"></i>
            <span class="inline-flex items-center text-[11px] font-semibold px-2.5 py-0.5 rounded-full {{ $toClasses }}">
                {{ $toLabel }}
            </span>
        </div>
    </div>
</li>
