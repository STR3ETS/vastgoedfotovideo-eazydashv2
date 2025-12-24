@extends('hub.layouts.app')

@section('content')
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0">
            <div class="h-full min-h-0 overflow-y-auto pr-3">
                @php
                    /** @var \App\Models\SeoProject $project */

                    $domain = $project->domain ?? 'Onbekend domein';
                    $companyName = optional($project->company)->name ?? 'Onbekend bedrijf';

                    $siteId = $serankingSiteId ?? null;
                    $stat = $serankingStat ?? null;
                    $rows = $serankingKeywordRows ?? [];
                    $sites = $serankingSites ?? []; // verwacht: array van GET /sites

                    $todayAvg = data_get($stat, 'today_avg');
                    $top10 = data_get($stat, 'top10');
                    $top30 = data_get($stat, 'top30');
                    $visibilityPercent = data_get($stat, 'visibility_percent');
                    $up = data_get($stat, 'total_up');
                    $down = data_get($stat, 'total_down');

                    $isConnected = !empty($siteId);

                    $inputClass = 'w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-[#215558] placeholder-gray-400 outline-none focus:border-[#0F9B9F] focus:ring-2 focus:ring-[#0F9B9F]/20 transition';
                    $selectClass = 'w-full rounded-2xl border border-gray-300 bg-white px-4 py-3 text-sm text-[#215558] outline-none focus:border-[#0F9B9F] focus:ring-2 focus:ring-[#0F9B9F]/20 transition';

                    $btnPrimary = 'px-5 py-3 rounded-full text-xs font-semibold text-white bg-[#0F9B9F] hover:bg-[#215558] transition';
                    $btnGhost = 'px-5 py-3 rounded-full text-xs font-semibold border border-gray-200 text-[#215558] bg-white hover:bg-gray-100 transition';
                    $pill = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-[11px] border';

                    // Helpers voor auto-select in dropdown
                    $needle = strtolower(trim(preg_replace('#^https?://#i', '', $domain)));
                    $bestSiteId = null;

                    if (is_array($sites) && count($sites) > 0) {
                        foreach ($sites as $s) {
                            $candidate = strtolower((string) ($s['name'] ?? ''));
                            $candidate2 = strtolower((string) ($s['title'] ?? ''));
                            if ($needle && (str_contains($candidate, $needle) || str_contains($candidate2, $needle))) {
                                $bestSiteId = (int) ($s['id'] ?? 0);
                                break;
                            }
                        }
                    }

                    $selectedSiteId = old('site_id') ?: ($siteId ?: ($bestSiteId ?: null));
                @endphp

                {{-- Header --}}
                <div class="flex items-start justify-between gap-4 mb-6">
                    <div class="min-w-0">
                        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">SEO traject</p>
                        <h1 class="text-2xl font-bold text-[#215558] truncate">
                            {{ $domain }}
                        </h1>
                        <p class="text-xs text-gray-500 mt-1">
                            {{ $companyName }}
                            @if(optional($project->company)->city)
                                • {{ optional($project->company)->city }}
                            @endif
                        </p>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @if($isConnected)
                                <span class="{{ $pill }} bg-emerald-50 border-emerald-200 text-emerald-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    SE Ranking gekoppeld (ID: {{ $siteId }})
                                </span>
                            @else
                                <span class="{{ $pill }} bg-amber-50 border-amber-200 text-amber-800">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                    Nog niet gekoppeld
                                </span>
                            @endif

                            @if($project->last_synced_at)
                                <span class="{{ $pill }} bg-gray-50 border-gray-200 text-gray-700">
                                    Laatst bijgewerkt: {{ $project->last_synced_at->format('d-m-Y H:i') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('support.seo.projects.index') }}"
                           class="px-4 py-2 text-xs font-semibold rounded-full border border-gray-200 text-[#215558] bg-white hover:bg-gray-100 transition">
                            Terug
                        </a>

                        <a href="{{ route('support.seo.projects.edit', $project) }}"
                           class="px-4 py-2 text-xs font-semibold rounded-full bg-[#f3f8f8] text-[#215558] hover:bg-[#e5f1f1] transition">
                            Bewerken
                        </a>
                    </div>
                </div>

                {{-- Status melding --}}
                @if (session('status'))
                    <div class="mb-4 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Traject stappen --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white border border-gray-200 rounded-4xl p-5">
                        <p class="text-[10px] uppercase tracking-wide text-gray-500">Stap 1</p>
                        <p class="text-sm font-bold text-[#215558] mt-1">SE Ranking koppelen</p>
                        <p class="text-[12px] text-gray-600 mt-2">
                            Koppel dit traject aan het juiste SE Ranking project, zodat we rankings en visibility kunnen ophalen.
                        </p>
                        <div class="mt-3">
                            @if($isConnected)
                                <span class="text-[12px] text-emerald-700 font-semibold">Gekoppeld</span>
                            @else
                                <span class="text-[12px] text-amber-800 font-semibold">Actie nodig</span>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-4xl p-5">
                        <p class="text-[10px] uppercase tracking-wide text-gray-500">Stap 2</p>
                        <p class="text-sm font-bold text-[#215558] mt-1">Keywords</p>
                        <p class="text-[12px] text-gray-600 mt-2">
                            Voeg de belangrijkste keywords toe. Later komt hier jouw MCP suggestie-knop.
                        </p>
                        <div class="mt-3">
                            <span class="text-[12px] text-gray-700 font-semibold">
                                {{ is_array($project->primary_keywords) ? count($project->primary_keywords) : 0 }} opgeslagen
                            </span>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-4xl p-5">
                        <p class="text-[10px] uppercase tracking-wide text-gray-500">Stap 3</p>
                        <p class="text-sm font-bold text-[#215558] mt-1">Nulmeting</p>
                        <p class="text-[12px] text-gray-600 mt-2">
                            Bekijk de huidige posities en beweging. Je ververst dit wanneer je updates wilt zien.
                        </p>
                        <div class="mt-3">
                            @if($isConnected && count($rows) > 0)
                                <span class="text-[12px] text-emerald-700 font-semibold">Beschikbaar</span>
                            @else
                                <span class="text-[12px] text-gray-600 font-semibold">Nog geen data</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Stap 1: koppelen --}}
                <div class="bg-[#f3f8f8] rounded-4xl p-6 mb-6">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-[#215558]">Stap 1: SE Ranking koppelen</h3>
                            <p class="text-[12px] text-gray-600 mt-1">
                                Selecteer het juiste project en klik op koppelen. Daarna kun je data verversen.
                            </p>
                        </div>

                        @if($isConnected)
                            <form method="POST" action="{{ route('support.seo.projects.seranking.sync', $project) }}">
                                @csrf
                                <button type="submit" class="{{ $btnGhost }}">
                                    Ververs data
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(!$isConnected)
                        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="bg-white border border-gray-200 rounded-3xl p-5">
                                <p class="text-[11px] font-semibold text-[#215558]">Kies project</p>
                                <p class="text-[11px] text-gray-500 mt-1">
                                    We proberen automatisch te matchen op domein. Controleer altijd of je de juiste kiest.
                                </p>

                                @if(is_array($sites) && count($sites) > 0)
                                    <form method="POST" action="{{ route('support.seo.projects.seranking.connect', $project) }}" class="mt-3 space-y-3">
                                        @csrf

                                        <select name="site_id" class="{{ $selectClass }}">
                                            <option value="">Kies een project</option>
                                            @foreach($sites as $s)
                                                @php
                                                    $sid = (int) ($s['id'] ?? 0);
                                                    $label = trim((string) ($s['title'] ?? $s['name'] ?? 'Project'));
                                                @endphp
                                                <option value="{{ $sid }}" @if((string)$selectedSiteId === (string)$sid) selected @endif>
                                                    {{ $label }} (ID: {{ $sid }})
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="{{ $btnPrimary }}">
                                            Koppelen
                                        </button>
                                    </form>
                                @else
                                    <p class="mt-3 text-[12px] text-gray-600">
                                        Geen projecten gevonden in de lijst.
                                    </p>
                                @endif
                            </div>

                            <div class="bg-white border border-gray-200 rounded-3xl p-5">
                                <p class="text-[11px] font-semibold text-[#215558]">Site ID invullen</p>
                                <p class="text-[11px] text-gray-500 mt-1">
                                    Als je het site ID al weet, kun je het hier invullen.
                                </p>

                                <form method="POST" action="{{ route('support.seo.projects.seranking.connect', $project) }}" class="mt-3 space-y-3">
                                    @csrf

                                    <input type="text" name="site_id" class="{{ $inputClass }}" placeholder="Bijv. 11063750">

                                    <button type="submit" class="{{ $btnPrimary }}">
                                        Koppelen
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('support.seo.projects.seranking.recheck', $project) }}">
                                @csrf
                                <button type="submit" class="{{ $btnPrimary }}">
                                    Start recheck
                                </button>
                            </form>

                            <form method="POST" action="{{ route('support.seo.projects.audits.start', $project) }}">
                                @csrf
                                <button type="submit" class="{{ $btnGhost }}">
                                    Start website audit
                                </button>
                            </form>

                            <p class="text-[11px] text-gray-500">
                                Recheck start een nieuwe meting in SE Ranking. Gebruik daarna “Ververs data” om updates te zien.
                            </p>
                        </div>
                    @endif
                </div>

                {{-- KPI cards --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white border border-gray-200 rounded-4xl p-5">
                        <p class="text-[10px] uppercase tracking-wide text-gray-500">Visibility</p>
                        <p class="text-xl font-bold text-[#215558] mt-1">
                            {{ $visibilityPercent !== null ? number_format((float)$visibilityPercent, 1, ',', '.') . '%' : 'Geen data' }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-1">SE Ranking</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-4xl p-5">
                        <p class="text-[10px] uppercase tracking-wide text-gray-500">Gem. positie</p>
                        <p class="text-xl font-bold text-[#215558] mt-1">
                            {{ $todayAvg !== null ? (int)$todayAvg : 'Geen data' }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-1">Laatste check</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-4xl p-5">
                        <p class="text-[10px] uppercase tracking-wide text-gray-500">Top 10</p>
                        <p class="text-xl font-bold text-[#215558] mt-1">
                            {{ $top10 !== null ? (int)$top10 : 'Geen data' }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-1">Keywords</p>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-4xl p-5">
                        <p class="text-[10px] uppercase tracking-wide text-gray-500">Stijgers / dalers</p>
                        <p class="text-xl font-bold text-[#215558] mt-1">
                            {{ $up !== null ? (int)$up : 0 }} / {{ $down !== null ? (int)$down : 0 }}
                        </p>
                        <p class="text-[11px] text-gray-500 mt-1">Sinds vorige check</p>
                    </div>
                </div>

                {{-- Stap 2: Keywords --}}
                <div class="bg-white border border-gray-200 rounded-4xl p-6 mb-6">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-[#215558]">Stap 2: keywords toevoegen</h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Voeg 10 tot 20 keywords toe. Daarna kun je verversen om posities te zien.
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" disabled
                                    class="px-4 py-2 text-xs font-semibold rounded-full border border-gray-200 text-gray-400 bg-white cursor-not-allowed">
                                MCP suggesties
                            </button>
                        </div>
                    </div>

                    @if(!$isConnected)
                        <p class="text-xs text-gray-500">
                            Koppel eerst SE Ranking (stap 1). Daarna kun je keywords toevoegen.
                        </p>
                    @else
                        {{-- Geen geneste forms. We gebruiken formaction op de 2e button. --}}
                        <form method="POST" action="{{ route('support.seo.projects.seranking.keywords.add', $project) }}" class="space-y-3">
                            @csrf

                            <textarea name="keywords_text" rows="4" class="{{ $inputClass }}"
                                      placeholder="1 keyword per regel, bijv:
woningontruiming arnhem
woningontruiming nijmegen
spoed ontruiming"></textarea>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="submit" class="{{ $btnPrimary }}">
                                    Keywords toevoegen
                                </button>

                                <button type="submit"
                                        formaction="{{ route('support.seo.projects.seranking.recheck', $project) }}"
                                        formmethod="POST"
                                        class="{{ $btnGhost }}">
                                    Direct recheck starten
                                </button>

                                <p class="text-[11px] text-gray-500">
                                    Tip: na een recheck zie je de updates terug via “Ververs data”.
                                </p>
                            </div>
                        </form>
                    @endif
                </div>

                {{-- Stap 3: Nulmeting --}}
                <div class="bg-white border border-gray-200 rounded-4xl p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <div>
                            <h3 class="text-sm font-bold text-[#215558]">Stap 3: nulmeting</h3>
                            <p class="text-xs text-gray-500 mt-1">
                                Positie 0 betekent: niet gevonden in top 100.
                            </p>
                        </div>

                        @if($isConnected)
                            <form method="POST" action="{{ route('support.seo.projects.seranking.sync', $project) }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 text-xs font-semibold rounded-full border border-gray-200 text-[#215558] bg-white hover:bg-gray-100 transition">
                                    Ververs
                                </button>
                            </form>
                        @endif
                    </div>

                    @if(!$isConnected)
                        <p class="text-xs text-gray-500">Koppel SE Ranking om rankings te zien.</p>
                    @elseif(count($rows) === 0)
                        <p class="text-xs text-gray-500">Nog geen rankings. Voeg keywords toe en klik op ververs.</p>
                    @else
                        <div class="grid grid-cols-12 text-[11px] text-gray-500 font-semibold pb-2 border-b border-gray-200">
                            <div class="col-span-5">Keyword</div>
                            <div class="col-span-2">Positie</div>
                            <div class="col-span-2">Verschil</div>
                            <div class="col-span-2">Volume</div>
                            <div class="col-span-1 text-right">CPC</div>
                        </div>

                        <div class="divide-y divide-gray-200">
                            @foreach(array_slice($rows, 0, 30) as $r)
                                @php
                                    $pos = (int)($r['pos'] ?? 0);
                                    $change = (int)($r['change'] ?? 0);

                                    $changeText = $change === 0 ? '0' : ($change > 0 ? '+' . $change : (string)$change);
                                    $changeClass = $change > 0 ? 'text-emerald-700' : ($change < 0 ? 'text-red-700' : 'text-gray-500');

                                    $posClass = $pos > 0 && $pos <= 10 ? 'text-emerald-700' : ($pos > 0 && $pos <= 30 ? 'text-amber-700' : ($pos > 0 ? 'text-[#215558]' : 'text-gray-400'));
                                @endphp

                                <div class="grid grid-cols-12 py-3 text-[12px]">
                                    <div class="col-span-5 text-[#215558] font-semibold truncate">
                                        {{ $r['keyword'] ?? '-' }}
                                        @if(!empty($r['landing_page']))
                                            <div class="text-[10px] text-gray-500 font-normal truncate mt-0.5">
                                                {{ $r['landing_page'] }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="col-span-2 font-semibold {{ $posClass }}">
                                        {{ $pos > 0 ? $pos : '0' }}
                                    </div>

                                    <div class="col-span-2 font-semibold {{ $changeClass }}">
                                        {{ $changeText }}
                                    </div>

                                    <div class="col-span-2 text-gray-700">
                                        {{ (int)($r['volume'] ?? 0) }}
                                    </div>

                                    <div class="col-span-1 text-right text-gray-700">
                                        {{ number_format((float)($r['cpc'] ?? 0), 2, ',', '.') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
@endsection
