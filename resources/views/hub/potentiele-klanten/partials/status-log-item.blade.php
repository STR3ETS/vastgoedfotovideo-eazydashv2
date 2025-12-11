@php
  /** @var \App\Models\AanvraagStatusLog $log */
  $valueToLabel = $valueToLabel ?? [];

  $fromLabel = $log->from_status
      ? ($valueToLabel[$log->from_status] ?? ucfirst($log->from_status))
      : '—';

  $toLabel = $log->to_status
      ? ($valueToLabel[$log->to_status] ?? ucfirst($log->to_status))
      : '—';

  $userName = optional($log->user)->name ?? __('potentiele_klanten.logbook.unknown_user');
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

<li class="text-[11px] bg-white pl-8 py-2 relative">
    <div class="absolute left-2.25 top-0 bottom-0 w-px bg-[#215558]/20"></div>
    <div class="absolute left-1 top-2 w-3 h-3 rounded-full bg-[#f3f8f8] border-[2px] border-[#215558]/20 z-[1]"></div>
    <div class="flex flex-col gap-2">
        <div class="flex flex-col">
            <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Aangepast op</p>
            <p class="text-sm font-semibold text-[#215558]">{{ $when }}</p>
        </div>
        <div class="flex flex-col">
            <p class="text-[11px] font-semibold opacity-50 text-[#215558]">Aangepast door</p>
            <p class="text-sm font-semibold text-[#215558]">{{ $userName }}</p>
        </div>
        <div class="flex flex-col">
            <p class="text-[11px] font-semibold opacity-50 text-[#215558] mb-1">Aanpassing</p>
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
    </div>
</li>
