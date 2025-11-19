{{-- resources/views/hub/seo/show.blade.php --}}
@extends('hub.layouts.app')

@section('content')
    <div class="h-full flex flex-col col-span-5 gap-4">
        {{-- Header --}}
        <div class="bg-[#e0f4f1] rounded-xl px-6 py-4 flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <div class="flex items-center gap-3 text-sm text-[#215558]">
                    <a href="{{ route('support.seo-audit.index', ['company_id' => $audit->company_id]) }}"
                       class="inline-flex items-center gap-1 text-[#215558] hover:underline">
                        <span>&larr;</span>
                        <span>Terug naar overzicht</span>
                    </a>
                </div>
                <h1 class="text-2xl font-black text-[#215558]">
                    SEO audit voor {{ $audit->company->name ?? $audit->domain }}
                </h1>
                <div class="text-xs text-[#215558] opacity-80 flex flex-wrap gap-4">
                    <span>Domein: <strong>{{ $audit->domain }}</strong></span>
                    @if($audit->started_at)
                        <span>Uitgevoerd op {{ $audit->started_at->format('d-m-Y H:i') }}
                            @if($audit->finished_at)
                                - afgerond op {{ $audit->finished_at->format('d-m-Y H:i') }}
                            @endif
                        </span>
                    @endif
                </div>

                @if (session('status'))
                    <div class="mt-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-2 px-3 py-1.5 rounded-xl bg-red-50 border border-red-200 text-[11px] text-red-700">
                        {{ $errors->first() }}
                    </div>
                @endif
            </div>

            <div class="flex flex-col items-end gap-2">
                <div class="flex items-center gap-2">
                    @php
                        $status = $audit->status;
                        $statusLabel = match($status) {
                            'completed' => 'Afgerond',
                            'running'   => 'Bezig',
                            'pending'   => 'In wachtrij',
                            'failed'    => 'Mislukt',
                            default     => ucfirst($status),
                        };
                        $statusClasses = match($status) {
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'running'   => 'bg-blue-100 text-blue-700',
                            'pending'   => 'bg-amber-100 text-amber-700',
                            'failed'    => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-700',
                        };
                        $dotClasses = match($status) {
                            'completed' => 'bg-emerald-500',
                            'running'   => 'bg-blue-500',
                            'pending'   => 'bg-amber-500',
                            'failed'    => 'bg-red-500',
                            default     => 'bg-gray-500',
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses }}">
                        <span class="w-2 h-2 rounded-full mr-1 {{ $dotClasses }}"></span>
                        {{ $statusLabel }}
                    </span>
                </div>

                @php
                    $score = $summary['score'] ?? $audit->overall_score;
                @endphp

                <div class="flex items-baseline gap-1">
                    <span class="text-xs text-[#215558] opacity-80">Algemene SEO score</span>
                    <div class="ml-3 text-right">
                        @if(!is_null($score))
                            <span class="text-3xl font-black text-[#215558]">{{ $score }}</span>
                            <span class="text-sm text-[#215558]">%</span>
                        @else
                            <span class="text-sm text-[#215558]">nvt</span>
                        @endif
                    </div>
                </div>

                <a href="{{ route('support.seo-audit.download-json', $audit) }}"
                   class="inline-flex items-center gap-2 text-xs px-3 py-1 rounded-full bg-white text-[#215558] shadow-sm hover:bg-[#f3fffd]">
                    <span class="text-sm">⬇</span>
                    <span>SERanking rapport downloaden (JSON)</span>
                </a>
            </div>
        </div>

        {{-- Hoofdgrid --}}
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 flex-1">
            {{-- Kolom 1: Samenvatting + belangrijkste issues --}}
            <div class="bg-white rounded-xl p-5 flex flex-col">
                <h2 class="text-sm font-semibold text-[#215558] mb-3">Korte samenvatting</h2>

                {{-- Stat blokjes --}}
                <div class="grid grid-cols-3 gap-3 mb-4 text-xs">
                    <div class="bg-[#f5faf9] rounded-lg px-3 py-2 flex flex-col">
                        <span class="text-[11px] text-[#215558] opacity-80 mb-1">Gescande pagina s</span>
                        <span class="text-lg font-bold text-[#215558]">
                            {{ $summary['pages'] ?? 'nvt' }}
                        </span>
                    </div>
                    <div class="bg-[#ffecec] rounded-lg px-3 py-2 flex flex-col">
                        <span class="text-[11px] text-[#a12020] opacity-80 mb-1">Kritieke fouten</span>
                        <span class="text-lg font-bold text-[#a12020]">
                            {{ $summary['errors'] ?? '0' }}
                        </span>
                    </div>
                    <div class="bg-[#fff7e6] rounded-lg px-3 py-2 flex flex-col">
                        <span class="text-[11px] text-[#b06400] opacity-80 mb-1">Waarschuwingen</span>
                        <span class="text-lg font-bold text-[#b06400]">
                            {{ $summary['warnings'] ?? '0' }}
                        </span>
                    </div>
                </div>

                <p class="text-[11px] text-[#215558] opacity-80 mb-4 leading-relaxed">
                    Gebruik dit rapport als startpunt voor je advies. Begin met de kritieke fouten
                    (techniek en zichtbaarheid), pak daarna de waarschuwingen op en sluit af met
                    optimalisaties rond content en interne links.
                </p>

                <h3 class="text-xs font-semibold text-[#215558] mb-2">
                    Belangrijkste verbeterpunten
                </h3>

                @php
                    $quick = collect($quickWins);
                    if ($quick->isEmpty()) {
                        $quick = collect($rawIssues)
                            ->whereIn('severity', ['critical', 'warning'])
                            ->sortByDesc('value')
                            ->take(5)
                            ->map(function ($i) {
                                $pages = (int) ($i['value'] ?? 0);
                                $pagesTxt = $pages > 0 ? " ({$pages} pagina s)" : '';
                                return [
                                    'title'       => $i['name'] ?: ($i['code'] ?? 'Onbekend probleem'),
                                    'description' => $pagesTxt,
                                    'impact'      => $i['severity'] === 'critical' ? 'hoog' : 'middelmatig',
                                    'category'    => $i['category'] ?? 'Overig',
                                ];
                            });
                    }
                @endphp

                @if($quick->isEmpty())
                    <p class="text-[11px] text-[#215558]">
                        Geen uitgesproken verbeterpunten gevonden in dit rapport. Kijk handmatig naar de
                        belangrijke pagina s en technische basis als je twijfelt.
                    </p>
                @else
                    <ul class="space-y-2 text-[11px] text-[#215558]">
                        @foreach($quick as $item)
                            <li class="flex items-start gap-2">
                                <span class="mt-[3px] w-1.5 h-1.5 rounded-full
                                             {{ ($item['impact'] ?? '') === 'hoog' ? 'bg-[#e11d48]' : 'bg-[#f59e0b]' }}"></span>
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold">
                                            {{ $item['title'] }}
                                        </span>
                                        @if(!empty($item['category']))
                                            <span class="px-1.5 py-0.5 rounded-full bg-[#f5faf9] text-[10px] text-[#215558]">
                                                {{ $item['category'] }}
                                            </span>
                                        @endif
                                    </div>
                                    @if(!empty($item['description']))
                                        <p class="mt-0.5 opacity-80">
                                            {{ $item['description'] }}
                                        </p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            {{-- Kolom 2: Technische details + ruwe JSON --}}
            <div class="bg-white rounded-xl p-5 flex flex-col">
                <h2 class="text-sm font-semibold text-[#215558] mb-3">Technische details</h2>

                <p class="text-[11px] text-[#215558] opacity-80 mb-4">
                    Dit blok is bedoeld als naslag voor jou als specialist. Hier zie je de
                    belangrijkste domeinwaarden en kun je indien nodig het ruwe SERanking rapport
                    bekijken.
                </p>

                <dl class="text-[11px] text-[#215558] mb-4 space-y-1">
                    <div class="flex justify-between">
                        <dt class="opacity-80">Aantal backlinks</dt>
                        <dd class="font-semibold">
                            {{ $domainProps['backlinks'] ?? 'nvt' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="opacity-80">Pagina s in Google index</dt>
                        <dd class="font-semibold">
                            {{ $domainProps['index_google'] ?? 'nvt' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="opacity-80">Domein verloopt op</dt>
                        <dd class="font-semibold">
                            {{ $domainProps['expdate'] ?? 'nvt' }}
                        </dd>
                    </div>
                </dl>

                {{-- Ruwe JSON inklapbaar --}}
                <div
                    x-data="{ open: false }"
                    class="mt-auto border border-[#e0f4f1] rounded-lg bg-[#f5faf9]"
                >
                    <button type="button"
                            class="w-full px-3 py-2 flex items-center justify-between text-[11px] text-[#215558] font-medium"
                            @click="open = !open">
                        <span>Toon ruwe SERanking JSON</span>
                        <span class="text-xs" x-text="open ? 'klik om te klappen' : 'klik om uit te klappen'"></span>
                    </button>
                    <div x-show="open" x-cloak class="border-t border-[#e0f4f1] max-h-72 overflow-auto">
                        <pre class="text-[10px] px-3 py-2 whitespace-pre-wrap text-[#215558]">
{!! json_encode($rawReport, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) !!}
                        </pre>
                    </div>
                </div>
            </div>

            {{-- Kolom 3: Aanpakplan / AI acties --}}
            <div class="bg-white rounded-xl p-5 flex flex-col">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-semibold text-[#215558]">Aanpakplan voor deze klant</h2>

                    <form method="POST" action="{{ route('support.seo-audit.generate-plan', $audit) }}">
                        @csrf
                        <button type="submit"
                                class="px-3 py-1.5 rounded-full bg-[#0F9B9F] hover:bg-[#215558] text-[11px] font-semibold text-white transition cursor-pointer">
                            @if(!empty($aiPlan))
                                AI plan verversen
                            @else
                                Genereer AI takenplan
                            @endif
                        </button>
                    </form>
                </div>

                <p class="text-[11px] text-[#215558] opacity-80 mb-3">
                    Gebruik dit als leidraad voor je advies en werkzaamheden. Werk bij voorkeur
                    van boven naar beneden. Koppel waar nodig taken aan het juiste teamlid
                    (developer, copywriter, SEO of designer).
                </p>

                @php
                    $plan = $aiPlan['plan'] ?? null;
                    $tasks = collect($plan['tasks'] ?? []);
                    $tasksByOwner = $tasks->groupBy(function ($task) {
                        return strtolower($task['owner'] ?? 'seo');
                    });

                    $ownerLabels = [
                        'developer' => 'Developer',
                        'copywriter'=> 'Copywriter',
                        'seo'       => 'SEO specialist',
                        'designer'  => 'Designer',
                        'marketing' => 'Marketing',
                    ];
                @endphp

                @if($tasks->isEmpty())
                    {{-- Fallback op statische recommended_actions als AI nog niets heeft --}}
                    @if(empty($actions))
                        <ol class="list-decimal list-inside space-y-2 text-[11px] text-[#215558]">
                            <li>Controleer de technische basis: statuscodes, sitemap en robots.txt.</li>
                            <li>Optimaliseer content en meta tags op de belangrijkste landingspagina s.</li>
                            <li>Werk aan interne links en autoriteit van belangrijke pagina s.</li>
                            <li>Bespreek de resultaten met de klant en leg vervolgstappen vast.</li>
                        </ol>
                    @else
                        <ol class="list-decimal list-inside space-y-3 text-[11px] text-[#215558]">
                            @foreach($actions as $action)
                                <li>
                                    <div class="flex flex-col gap-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-semibold">{{ $action['title'] }}</span>
                                            <span class="px-1.5 py-0.5 rounded-full bg-[#f5faf9] text-[10px] text-[#215558]">
                                                {{ $action['category'] ?? 'SEO' }}
                                            </span>
                                            @if(!empty($action['owner']))
                                                <span class="px-1.5 py-0.5 rounded-full bg-[#e0f4f1] text-[10px] text-[#215558]">
                                                    Owner: {{ ucfirst($action['owner']) }}
                                                </span>
                                            @endif
                                        </div>
                                        @if(!empty($action['summary']))
                                            <p class="opacity-80">{{ $action['summary'] }}</p>
                                        @endif

                                        @if(!empty($action['suggested_steps']) && is_array($action['suggested_steps']))
                                            <ul class="list-disc list-inside mt-1 space-y-0.5 opacity-90">
                                                @foreach($action['suggested_steps'] as $step)
                                                    <li>{{ $step }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                @else
                    {{-- AI takenplan per owner --}}
                    <div class="space-y-3 text-[11px] text-[#215558]">
                        @foreach($tasksByOwner as $owner => $ownerTasks)
                            <div class="border border-[#e0f4f1] rounded-xl p-3">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[11px] font-semibold text-[#215558]">
                                        {{ $ownerLabels[$owner] ?? ucfirst($owner) }}
                                    </span>
                                    <span class="text-[10px] text-[#215558] opacity-60">
                                        {{ count($ownerTasks) }} taak{{ count($ownerTasks) === 1 ? '' : 'en' }}
                                    </span>
                                </div>
                                <ul class="space-y-1.5">
                                    @foreach($ownerTasks as $task)
                                        @php
                                            $priority = strtolower($task['priority'] ?? 'normal');
                                            $priorityClasses = match($priority) {
                                                'must_fix' => 'bg-red-100 text-red-700 border-red-200',
                                                'high'     => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'low'      => 'bg-gray-100 text-gray-700 border-gray-200',
                                                default    => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            };
                                        @endphp
                                        <li class="border border-[#f0f5f4] rounded-lg px-2.5 py-1.5">
                                            <div class="flex flex-wrap items-center gap-2 mb-0.5">
                                                <span class="font-semibold">
                                                    {{ $task['title'] ?? 'Taak' }}
                                                </span>
                                                @if(!empty($task['category']))
                                                    <span class="px-1.5 py-0.5 rounded-full bg-[#f5faf9] text-[9px] text-[#215558]">
                                                        {{ $task['category'] }}
                                                    </span>
                                                @endif
                                                <span class="px-1.5 py-0.5 rounded-full border text-[9px] {{ $priorityClasses }}">
                                                    {{ $priority === 'must_fix' ? 'Must fix' : ucfirst($priority) }}
                                                </span>
                                                @if(!empty($task['estimated_minutes']))
                                                    <span class="px-1.5 py-0.5 rounded-full bg-[#f5faf9] text-[9px] text-[#215558]">
                                                        ± {{ $task['estimated_minutes'] }} min
                                                    </span>
                                                @endif
                                            </div>
                                            @if(!empty($task['description']))
                                                <p class="opacity-80">
                                                    {{ $task['description'] }}
                                                </p>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endforeach
                    </div>

                    {{-- Extra tekst om te kopiëren naar interne notities of klant --}}
                    <div class="mt-4 pt-3 border-t border-[#e0f4f1] space-y-2">
                        @if(!empty($plan['notes_for_colleague']))
                            <div>
                                <p class="text-[10px] font-semibold text-[#215558] mb-1">
                                    Interne notitie voor collega
                                </p>
                                <p class="text-[11px] text-[#215558] opacity-80">
                                    {{ $plan['notes_for_colleague'] }}
                                </p>
                            </div>
                        @endif

                        @if(!empty($plan['client_summary']))
                            <div>
                                <p class="text-[10px] font-semibold text-[#215558] mb-1">
                                    Korte samenvatting richting klant
                                </p>
                                <p class="text-[11px] text-[#215558] opacity-80">
                                    {{ $plan['client_summary'] }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif

                <div class="mt-4 pt-3 border-t border-[#e0f4f1] flex flex-wrap gap-2">
                    <button type="button"
                            class="px-3 py-1.5 rounded-full text-[11px] bg-[#215558] text-white shadow-sm cursor-not-allowed opacity-60"
                            title="Later: koppelen aan je taak/board systeem">
                        Taak aanmaken
                    </button>
                    <button type="button"
                            class="px-3 py-1.5 rounded-full text-[11px] bg-[#f5faf9] text-[#215558] shadow-sm cursor-not-allowed opacity-60"
                            title="Later: vervolg-audit of ranking check starten">
                        Nieuwe follow-up check
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
