@extends('hub.layouts.app')

@section('content')
    {{-- Rechter kolom – SEO project dashboard --}}
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0"
             x-data="{
                openAudits: true,
                openOverview: true,
                openFocus: true,
                openDetails: true,
             }">
            <div class="h-full min-h-0 overflow-y-auto pr-3">
                @php
                    /** @var \App\Models\SeoProject $project */

                    $domain        = $project->domain ?? 'Onbekende site';
                    $url           = $project->url ?? ( $project->domain ? 'https://' . $project->domain : null );
                    $companyName   = optional($project->company)->name ?? 'Onbekend bedrijf';
                    $companyCity   = optional($project->company)->city ?? null;

                    $statusRaw     = $project->status ?? 'active';
                    $priority      = $project->priority ?? null;

                    $health        = (int)($project->health_overall ?? 0);

                    // Laatste audit object
                    $lastAudit     = $project->lastAudit;

                    // Scores uit laatste audit (vallen terug op 0 als er nog geen audit is)
                    $techScore     = (int) data_get($lastAudit, 'score_technical', 0);
                    $contentScore  = (int) data_get($lastAudit, 'score_content', 0);
                    $linksScore    = (int) data_get($lastAudit, 'score_authority', 0);

                    $visibility    = data_get($project, 'visibility_index');
                    $traffic       = data_get($project, 'organic_traffic');

                    $lastAuditAt   = $lastAudit->created_at ?? $project->last_synced_at ?? $project->created_at;
                    $lastSyncedAt  = $project->last_synced_at ?? null;

                    $goalPrimary   = $project->primary_goal ?? null;
                    $goalNotes     = $project->goal_notes ?? null;

                    $notes         = $project->notes ?? null;

                    $staleDays     = 30;
                    $isStale       = false;
                    if ($lastAuditAt) {
                        try {
                            $isStale = $lastAuditAt->lt(now()->subDays($staleDays));
                        } catch (\Throwable $e) {
                            $isStale = false;
                        }
                    } else {
                        $isStale = true;
                    }

                    // Status label
                    if ($statusRaw === 'paused') {
                        $statusLabel   = 'Gepauzeerd';
                        $statusClasses = 'bg-gray-100 text-gray-700';
                    } elseif ($statusRaw === 'onboarding') {
                        $statusLabel   = 'Onboarding';
                        $statusClasses = 'bg-cyan-100 text-cyan-700';
                    } else {
                        $statusLabel   = 'Lopend';
                        $statusClasses = 'bg-emerald-100 text-emerald-700';
                    }

                    // Gezondheidskleur
                    if ($health >= 80) {
                        $healthColor = 'text-emerald-600';
                        $healthLabel = 'Sterke basis';
                    } elseif ($health >= 60) {
                        $healthColor = 'text-amber-600';
                        $healthLabel = 'Oke, maar ruimte voor verbetering';
                    } elseif ($health > 0) {
                        $healthColor = 'text-red-600';
                        $healthLabel = 'Hoog risico';
                    } else {
                        $healthColor = 'text-gray-400';
                        $healthLabel = 'Nog geen score';
                    }

                    // Fase bepalen
                    if ($health === 0) {
                        $faseLabel = 'Startfase';
                        $faseText  = 'We staan aan het begin: eerst de basis goed zetten (techniek, indexatie, zoekwoorden).';
                    } elseif ($health < 60) {
                        $faseLabel = 'Herstel & basis';
                        $faseText  = 'Er zijn duidelijke problemen. Focus op technische fouten en contentbasis.';
                    } elseif ($health < 80) {
                        $faseLabel = 'Groei & optimalisatie';
                        $faseText  = 'De basis staat, nu gericht content uitbreiden en autoriteit verhogen.';
                    } else {
                        $faseLabel = 'Opschalen';
                        $faseText  = 'Sterke basis. Focus op verdiepende content, converterende paginas en autoriteit.';
                    }

                    // Risico-indicatie
                    $riskLevel = 'Laag';
                    $riskColor = 'text-emerald-600';
                    $riskText  = 'Geen grote acute SEO-risicos zichtbaar op basis van de huidige score.';

                    if ($health < 60 && $health > 0) {
                        $riskLevel = 'Hoog';
                        $riskColor = 'text-red-600';
                        $riskText  = 'Er zijn belangrijke SEO issues. Dit project moet prioriteit krijgen.';
                    } elseif ($health >= 60 && $health < 80) {
                        $riskLevel = 'Gemiddeld';
                        $riskColor = 'text-amber-600';
                        $riskText  = 'De basis is oke, maar er zijn duidelijke verbeterkansen en risicos op langere termijn.';
                    } elseif ($health === 0) {
                        $riskLevel = 'Onbekend';
                        $riskColor = 'text-gray-500';
                        $riskText  = 'Nog geen score. Eerst een audit of crawl draaien.';
                    }

                    if ($isStale) {
                        $riskText .= ' Daarnaast is de laatste audit of sync verouderd. Eerst opnieuw meten.';
                        if ($riskLevel === 'Laag') {
                            $riskLevel = 'Gemiddeld';
                            $riskColor = 'text-amber-600';
                        }
                    }

                    // Focusgebieden bepalen simpel op basis van deel scores
                    $focusTech    = $techScore > 0 && $techScore < 70;
                    $focusContent = $contentScore > 0 && $contentScore < 70;
                    $focusLinks   = $linksScore > 0 && $linksScore < 70;

                    // Audit status en labels (voor het audit blok)
                    $auditStatus       = $lastAudit->status ?? null;
                    $auditSource       = $lastAudit->source ?? null;
                    $auditOverallScore = $lastAudit->score_overall ?? null;
                    $auditDate         = $lastAudit->finished_at ?? $lastAudit->created_at ?? null;

                    if ($auditStatus === 'running') {
                        $auditStatusLabel   = 'Bezig met audit';
                        $auditStatusClasses = 'bg-sky-100 text-sky-700';
                    } elseif ($auditStatus === 'failed') {
                        $auditStatusLabel   = 'Mislukt';
                        $auditStatusClasses = 'bg-red-100 text-red-700';
                    } elseif ($auditStatus === 'completed') {
                        $auditStatusLabel   = 'Afgerond';
                        $auditStatusClasses = 'bg-emerald-100 text-emerald-700';
                    } elseif ($auditStatus === 'pending') {
                        $auditStatusLabel   = 'In wachtrij';
                        $auditStatusClasses = 'bg-amber-100 text-amber-700';
                    } elseif ($auditStatus) {
                        $auditStatusLabel   = ucfirst($auditStatus);
                        $auditStatusClasses = 'bg-gray-100 text-gray-700';
                    } else {
                        $auditStatusLabel   = null;
                        $auditStatusClasses = '';
                    }

                    if ($auditSource === 'seranking') {
                        $auditSourceLabel = 'SERanking audit';
                    } elseif ($auditSource === 'mcp') {
                        $auditSourceLabel = 'OpenAI MCP audit';
                    } elseif ($auditSource === 'manual') {
                        $auditSourceLabel = 'Handmatig ingevoerd';
                    } elseif ($auditSource) {
                        $auditSourceLabel = ucfirst($auditSource);
                    } else {
                        $auditSourceLabel = null;
                    }

                    $rawSummary = $lastAudit->raw_summary ?? null;
                    $summaryText = null;
                    if (is_array($rawSummary)) {
                        // Probeer een paar veel voorkomende keys
                        $summaryText = $rawSummary['headline'] ??
                                       $rawSummary['summary'] ??
                                       implode("\n", array_filter($rawSummary, 'is_string'));
                    } elseif (is_string($rawSummary)) {
                        $summaryText = $rawSummary;
                    }

                    $formatDate = function ($date) {
                        if (! $date) return 'Geen data';
                        try {
                            return $date->format('d-m-Y');
                        } catch (\Throwable $e) {
                            return (string) $date;
                        }
                    };

                    $formatDateTime = function ($date) {
                        if (! $date) return 'Geen data';
                        try {
                            return $date->format('d-m-Y H:i');
                        } catch (\Throwable $e) {
                            return (string) $date;
                        }
                    };

                    $formatNumber = function ($value, $decimals = 0) {
                        if ($value === null || $value === '') return 'Geen data';
                        return number_format((float)$value, $decimals, ',', '.');
                    };
                @endphp

                {{-- Header: domein + klant + gezondheid --}}
                <div class="w-full flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                                {{ $domain }}
                            </h3>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusClasses }}">
                                {{ $statusLabel }}
                            </span>
                            @if($priority)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#f97316]/10 text-[#9a3412] border border-[#f97316]/30">
                                    Prioriteit: {{ ucfirst($priority) }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-[#21555880] font-semibold truncate mb-1">
                            {{ $companyName }}@if($companyCity) &nbsp;&middot;&nbsp;{{ $companyCity }}@endif
                        </p>
                        @if($url)
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-1 text-[11px] font-semibold text-[#0F9B9F] hover:text-[#215558] transition">
                                <i class="fa-solid fa-up-right-from-square text-[10px]"></i>
                                <span>{{ $url }}</span>
                            </a>
                        @endif
                    </div>
                    <div class="flex items-center gap-6">
                        <div class="flex items-center gap-3">
                            <div class="relative flex items-center justify-center w-20 h-20 rounded-full bg-[#f3f8f8]">
                                <div class="absolute inset-1 rounded-full bg-white flex items-center justify-center">
                                    <p class="text-xl font-extrabold {{ $healthColor }}">
                                        @if($health > 0)
                                            {{ $health }}
                                        @else
                                            –
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold text-[#21555880] mb-0.5">
                                    Gezondheidsscore
                                </p>
                                <p class="text-sm font-bold text-[#215558] mb-0.5">
                                    @if($health > 0)
                                        {{ $health }} / 100
                                    @else
                                        Nog geen score
                                    @endif
                                </p>
                                <p class="text-[11px] font-semibold text-[#21555880]">
                                    {{ $healthLabel }}
                                </p>
                            </div>
                        </div>
                        <div class="hidden md:flex flex-col items-end">
                            <p class="text-[11px] font-semibold text-[#21555880] mb-0.5">
                                Laatste audit of sync
                            </p>
                            <p class="text-sm font-bold text-[#215558]">
                                {{ $formatDate($lastAuditAt) }}
                                @if($isStale)
                                    <span class="inline-flex items-center ml-2 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">
                                        Verouderd
                                    </span>
                                @endif
                            </p>
                            @if($lastSyncedAt)
                                <p class="text-[11px] font-semibold text-[#21555880]">
                                    Laatste sync: {{ $formatDate($lastSyncedAt) }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sectie: Audits & metingen --}}
                <div class="w-full flex items-center gap-2 min-w-0 mb-4">
                    <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                        Audits & metingen
                    </h3>
                    <button type="button"
                            class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                            @click="openAudits = !openAudits">
                        <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                           :class="openAudits ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                </div>

                <div x-show="openAudits" x-transition class="mb-6">
                    @if($lastAudit)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Laatste audit samenvatting --}}
                            <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-bold text-[#215558]">
                                        Laatste audit
                                    </p>
                                    @if($auditStatusLabel)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $auditStatusClasses }}">
                                            {{ $auditStatusLabel }}
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] font-semibold text-[#21555880] mb-1">
                                    Datum: {{ $formatDateTime($auditDate) }}
                                </p>
                                @if($auditSourceLabel)
                                    <p class="text-[11px] font-semibold text-[#21555880] mb-1">
                                        Bron: {{ $auditSourceLabel }}
                                    </p>
                                @endif
                                <p class="text-[11px] font-semibold text-[#21555880]">
                                    @if($summaryText)
                                        {{ $summaryText }}
                                    @else
                                        Deze audit heeft nog geen beknopte samenvatting. Dit kunnen we later automatisch laten genereren via SERanking of MCP.
                                    @endif
                                </p>
                            </div>

                            {{-- Scores uit audit --}}
                            <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                                <p class="text-xs font-bold text-[#215558] mb-2">
                                    Scores uit audit
                                </p>
                                <dl class="text-[11px] font-semibold text-[#21555880] space-y-1">
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="w-1/2">Totaalscore</dt>
                                        <dd class="w-1/2 text-right text-[#215558]">
                                            @if($auditOverallScore !== null)
                                                {{ (int) $auditOverallScore }} / 100
                                            @else
                                                <span class="text-gray-400 italic">Niet beschikbaar</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="w-1/2">Techniek</dt>
                                        <dd class="w-1/2 text-right text-[#215558]">
                                            @if($techScore > 0)
                                                {{ $techScore }} / 100
                                            @else
                                                <span class="text-gray-400 italic">Nog geen score</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="w-1/2">Content</dt>
                                        <dd class="w-1/2 text-right text-[#215558]">
                                            @if($contentScore > 0)
                                                {{ $contentScore }} / 100
                                            @else
                                                <span class="text-gray-400 italic">Nog geen score</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="w-1/2">Autoriteit</dt>
                                        <dd class="w-1/2 text-right text-[#215558]">
                                            @if($linksScore > 0)
                                                {{ $linksScore }} / 100
                                            @else
                                                <span class="text-gray-400 italic">Nog geen score</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Workflow voor je team --}}
                            <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                                <p class="text-xs font-bold text-[#215558] mb-2">
                                    Hoe nu verder met deze audit
                                </p>
                                <p class="text-[11px] font-semibold text-[#21555880] mb-3">
                                    Gebruik deze audit als startpunt voor de concrete takenlijst in de SEO module.
                                    Straks koppelen we hier automatisch issues en taken aan.
                                </p>
                                <ul class="text-[11px] font-semibold text-[#215558] space-y-1">
                                    <li>• Check eerst de kritieke technische issues (4xx, indexatie, performance).</li>
                                    <li>• Bepaal welke paginas direct aangepakt moeten worden op basis van de audit.</li>
                                    <li>• Vertaal de belangrijkste bevindingen naar 3 tot 5 concrete SEO taken.</li>
                                </ul>
                            </div>
                        </div>
                    @else
                        <div class="bg-[#f3f8f8] rounded-4xl p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                            <div class="max-w-xl">
                                <p class="text-xs font-bold text-[#215558] mb-1">
                                    Nog geen audit voor dit project
                                </p>
                                <p class="text-[11px] font-semibold text-[#21555880] mb-2">
                                    Start altijd met minimaal een technische site audit en een basis check van content en autoriteit.
                                    Dit blok zal straks automatisch gevuld worden vanuit SERanking of de OpenAI MCP.
                                </p>
                                <p class="text-[11px] font-semibold text-[#21555880]">
                                    Voor nu kun je handmatig de eerste audit draaien in je tools en de resultaten in de database opslaan,
                                    of we bouwen in de volgende stap direct de koppeling met SERanking.
                                </p>
                            </div>
                            <div class="flex flex-col items-start md:items-end gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700">
                                    Aanbevolen: audit draaien
                                </span>
                                {{-- In de volgende stap koppelen we hier een echte "Nieuwe audit starten" actie aan --}}
                                <button type="button"
                                        class="px-4 py-2 rounded-full text-xs font-semibold bg-[#0F9B9F] text-white hover:bg-[#215558] transition cursor-default">
                                    Nieuwe audit starten
                                </button>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sectie: Overzicht & metrics --}}
                <div class="w-full flex items-center gap-2 min-w-0 mb-4">
                    <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                        Overzicht & status
                    </h3>
                    <button type="button"
                            class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                            @click="openOverview = !openOverview">
                        <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                           :class="openOverview ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                </div>

                <div x-show="openOverview" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                        {{-- Fase --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Huidige fase
                            </p>
                            <p class="text-sm font-extrabold text-[#215558] mb-1">
                                {{ $faseLabel }}
                            </p>
                            <p class="text-[11px] font-semibold text-[#21555880]">
                                {{ $faseText }}
                            </p>
                        </div>

                        {{-- Risico --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Risico op terugval of verlies
                            </p>
                            <p class="text-sm font-extrabold {{ $riskColor }} mb-1">
                                {{ $riskLevel }}
                            </p>
                            <p class="text-[11px] font-semibold text-[#21555880]">
                                {{ $riskText }}
                            </p>
                        </div>

                        {{-- Zichtbaarheid --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Zichtbaarheidsscore
                            </p>
                            <p class="text-sm font-extrabold text-[#215558] mb-1">
                                @if($visibility !== null && $visibility !== '')
                                    {{ $formatNumber($visibility, 2) }}
                                @else
                                    <span class="text-xs text-gray-400 italic">Nog geen data</span>
                                @endif
                            </p>
                            <p class="text-[11px] font-semibold text-[#21555880]">
                                Indicatie hoe zichtbaar het domein is in zoekresultaten.
                            </p>
                        </div>

                        {{-- Organisch verkeer --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Organisch verkeer (maand)
                            </p>
                            <p class="text-sm font-extrabold text-[#215558] mb-1">
                                @if($traffic !== null && $traffic !== '')
                                    {{ $formatNumber($traffic, 0) }}
                                @else
                                    <span class="text-xs text-gray-400 italic">Nog geen data</span>
                                @endif
                            </p>
                            <p class="text-[11px] font-semibold text-[#21555880]">
                                Ruwe schatting van aantal bezoekers via Google.
                            </p>
                        </div>
                    </div>

                    {{-- Deel-scores (als beschikbaar) --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Technische gezondheid
                            </p>
                            <p class="text-lg font-extrabold text-[#215558] mb-1">
                                @if($techScore > 0)
                                    {{ $techScore }} / 100
                                @else
                                    <span class="text-xs text-gray-400 italic">Nog geen score</span>
                                @endif
                            </p>
                            <p class="text-[11px] font-semibold text-[#21555880]">
                                Basis: crawlbaarheid, snelheid, indexatie, structured data, mobile.
                            </p>
                        </div>
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Content & on page
                            </p>
                            <p class="text-lg font-extrabold text-[#215558] mb-1">
                                @if($contentScore > 0)
                                    {{ $contentScore }} / 100
                                @else
                                    <span class="text-xs text-gray-400 italic">Nog geen score</span>
                                @endif
                            </p>
                            <p class="text-[11px] font-semibold text-[#21555880]">
                                Relevantie, keyworddekking, metas, headings, interne links.
                            </p>
                        </div>
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Autoriteit & links
                            </p>
                            <p class="text-lg font-extrabold text-[#215558] mb-1">
                                @if($linksScore > 0)
                                    {{ $linksScore }} / 100
                                @else
                                    <span class="text-xs text-gray-400 italic">Nog geen score</span>
                                @endif
                            </p>
                            <p class="text-[11px] font-semibold text-[#21555880]">
                                Linkprofiel, domeinautoriteit en reputatie ten opzichte van concurrenten.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Sectie: Focus & volgende acties (handig voor je team) --}}
                <div class="w-full flex items-center gap-2 min-w-0 mb-4">
                    <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                        Focus & volgende stappen
                    </h3>
                    <button type="button"
                            class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                            @click="openFocus = !openFocus">
                        <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                           :class="openFocus ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                </div>

                <div x-show="openFocus" x-transition>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        {{-- Techniek --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between border
                            @if($focusTech) border-red-300 @else border-transparent @endif">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-[#215558]">
                                    Techniek
                                </p>
                                @if($focusTech)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700">
                                        Focuspunt
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] font-semibold text-[#21555880] mb-3">
                                Zorg dat de technische basis perfect is: geen 4xx fouten, goede laadsnelheid,
                                logische URL structuur en een schone crawl.
                            </p>
                            <ul class="text-[11px] font-semibold text-[#215558] space-y-1">
                                <li>• Controleer 4xx en 5xx en omleidingen.</li>
                                <li>• Check pagespeed voor de belangrijkste landingspaginas.</li>
                                <li>• Controleer sitemap en robots op afwijkingen.</li>
                            </ul>
                        </div>

                        {{-- Content --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between border
                            @if($focusContent) border-red-300 @else border-transparent @endif">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-[#215558]">
                                    Content & zoekwoorden
                                </p>
                                @if($focusContent)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700">
                                        Focuspunt
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] font-semibold text-[#21555880] mb-3">
                                Bepaal welke paginas belangrijk zijn voor omzet, en zorg dat die inhoudelijk
                                volledig, uniek en goed geoptimaliseerd zijn.
                            </p>
                            <ul class="text-[11px] font-semibold text-[#215558] space-y-1">
                                <li>• Maak een lijst met top vijf belangrijke diensten of paginas.</li>
                                <li>• Check of elke pagina een hoofdzoekwoord en variaties dekt.</li>
                                <li>• Optimaliseer titels, meta descriptions en koppen.</li>
                            </ul>
                        </div>

                        {{-- Autoriteit --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col justify-between border
                            @if($focusLinks) border-red-300 @else border-transparent @endif">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-xs font-bold text-[#215558]">
                                    Autoriteit & linkprofiel
                                </p>
                                @if($focusLinks)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700">
                                        Focuspunt
                                    </span>
                                @endif
                            </div>
                            <p class="text-[11px] font-semibold text-[#21555880] mb-3">
                                Kijk of dit domein genoeg autoriteit heeft in vergelijking met concurrenten
                                op de belangrijkste zoekwoorden.
                            </p>
                            <ul class="text-[11px] font-semibold text-[#215558] space-y-1">
                                <li>• Maak een lijst van drie tot vijf belangrijkste concurrenten.</li>
                                <li>• Vergelijk domein en paginawaarden (DR of DA, backlinks).</li>
                                <li>• Bedenk twee of drie concrete acties om relevante links te krijgen.</li>
                            </ul>
                        </div>
                    </div>

                    {{-- Doelen & klantnotities --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col">
                            <p class="text-xs font-bold text-[#215558] mb-2">
                                Hoofddoel van SEO voor deze klant
                            </p>
                            @if($goalPrimary)
                                <p class="text-sm font-semibold text-[#215558] mb-2">
                                    {{ $goalPrimary }}
                                </p>
                            @else
                                <p class="text-xs text-gray-400 italic mb-2">
                                    Nog geen primair doel ingevuld. Zet hier bijvoorbeeld:
                                    meer aanvragen voor een bepaalde dienst, of
                                    meer relevante bezoekers in een bepaalde regio.
                                </p>
                            @endif

                            @if($goalNotes)
                                <p class="text-[11px] font-semibold text-[#21555880] whitespace-pre-line">
                                    {{ $goalNotes }}
                                </p>
                            @endif
                        </div>
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col">
                            <p class="text-xs font-bold text-[#215558] mb-2">
                                Interne notities of aandachtspunten
                            </p>
                            @if($notes)
                                <p class="text-[11px] font-semibold text-[#21555880] whitespace-pre-line">
                                    {{ $notes }}
                                </p>
                            @else
                                <p class="text-xs text-gray-400 italic">
                                    Gebruik dit blok om interne notities bij te houden:
                                    belangrijke URLs, afspraken met de klant, uitzonderingen, enzovoort.
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Sectie: Details (domein + klant) --}}
                <div class="w-full flex items-center gap-2 min-w-0 mb-4">
                    <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                        Domein & klantdetails
                    </h3>
                    <button type="button"
                            class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                            @click="openDetails = !openDetails">
                        <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                           :class="openDetails ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                </div>

                <div x-show="openDetails" x-transition class="mb-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col gap-2">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Domein & service
                            </p>
                            <dl class="text-[11px] font-semibold text-[#21555880] space-y-1">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="w-1/3">Domein</dt>
                                    <dd class="w-2/3 text-right text-[#215558] truncate">
                                        {{ $domain }}
                                    </dd>
                                </div>
                                @if($url)
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="w-1/3">URL</dt>
                                        <dd class="w-2/3 text-right">
                                            <a href="{{ $url }}" target="_blank" rel="noopener"
                                               class="inline-flex items-center gap-1 text-[#0F9B9F] hover:text-[#215558] transition truncate">
                                                <i class="fa-solid fa-up-right-from-square text-[9px]"></i>
                                                <span class="truncate">{{ $url }}</span>
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="w-1/3">Status</dt>
                                    <dd class="w-2/3 text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-semibold {{ $statusClasses }}">
                                            {{ $statusLabel }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="w-1/3">Aangemaakt</dt>
                                    <dd class="w-2/3 text-right text-[#215558]">
                                        {{ $formatDate($project->created_at) }}
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-[#f3f8f8] rounded-4xl p-5 flex flex-col gap-2">
                            <p class="text-xs font-bold text-[#215558] mb-1">
                                Klant
                            </p>
                            <dl class="text-[11px] font-semibold text-[#21555880] space-y-1">
                                <div class="flex items-center justify-between gap-4">
                                    <dt class="w-1/3">Bedrijfsnaam</dt>
                                    <dd class="w-2/3 text-right text-[#215558] truncate">
                                        {{ $companyName }}
                                    </dd>
                                </div>
                                @php
                                    $contactName  = optional($project->company)->contact_name ?? null;
                                    $contactEmail = optional($project->company)->email ?? null;
                                    $contactPhone = optional($project->company)->phone ?? null;
                                @endphp
                                @if($contactName)
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="w-1/3">Contactpersoon</dt>
                                        <dd class="w-2/3 text-right text-[#215558] truncate">
                                            {{ $contactName }}
                                        </dd>
                                    </div>
                                @endif
                                @if($contactEmail)
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="w-1/3">E mail</dt>
                                        <dd class="w-2/3 text-right truncate">
                                            <a href="mailto:{{ $contactEmail }}"
                                               class="text-[#0F9B9F] hover:text-[#215558]">
                                                {{ $contactEmail }}
                                            </a>
                                        </dd>
                                    </div>
                                @endif
                                @if($contactPhone)
                                    <div class="flex items-center justify-between gap-4">
                                        <dt class="w-1/3">Telefoon</dt>
                                        <dd class="w-2/3 text-right text-[#215558] truncate">
                                            {{ $contactPhone }}
                                        </dd>
                                    </div>
                                @endif
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
