@extends('hub.layouts.app')

@section('content')
    {{-- Rechter kolom – SEO projecten overzicht --}}
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0"
             x-data="{
                openOverzichtSection: true,
                openStatistiekenSection: true,
                activeStatus: 'all',
                searchTerm: '',
                sortKey: null,
                sortDir: null,

                matches(row) {
                    if (!this.searchTerm) return true;
                    const term = this.searchTerm.toLowerCase();
                    return (row.dataset.domain || '').toLowerCase().includes(term)
                        || (row.dataset.company || '').toLowerCase().includes(term);
                },

                sortByDomain() {
                    if (this.sortKey !== 'domain') {
                        this.sortKey = 'domain';
                        this.sortDir = 'asc';
                    } else if (this.sortDir === 'asc') {
                        this.sortDir = 'desc';
                    } else {
                        this.sortKey = null;
                        this.sortDir = null;
                    }
                    this.applySort();
                },

                sortByHealth() {
                    if (this.sortKey !== 'health') {
                        this.sortKey = 'health';
                        this.sortDir = 'desc';
                    } else if (this.sortDir === 'desc') {
                        this.sortDir = 'asc';
                    } else {
                        this.sortKey = null;
                        this.sortDir = null;
                    }
                    this.applySort();
                },

                applySort() {
                    const tbody = this.$refs.projectsBody;
                    if (!tbody) return;

                    const rows = Array.from(tbody.children);

                    if (!this.sortKey || !this.sortDir) {
                        rows.sort((a, b) => {
                            return (parseInt(a.dataset.index) || 0) - (parseInt(b.dataset.index) || 0);
                        });
                    } else if (this.sortKey === 'domain') {
                        rows.sort((a, b) => {
                            const av = (a.dataset.domain || '').toLowerCase();
                            const bv = (b.dataset.domain || '').toLowerCase();
                            if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                            if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                            return 0;
                        });
                    } else if (this.sortKey === 'health') {
                        rows.sort((a, b) => {
                            const av = parseFloat(a.dataset.health || '0') || 0;
                            const bv = parseFloat(b.dataset.health || '0') || 0;
                            if (av < bv) return this.sortDir === 'asc' ? -1 : 1;
                            if (av > bv) return this.sortDir === 'asc' ? 1 : -1;
                            return 0;
                        });
                    }

                    rows.forEach(row => tbody.appendChild(row));
                }
            }">
            <div class="h-full min-h-0 overflow-y-auto pr-3">
                @php
                    $projects = $projects ?? collect();

                    $highRiskThreshold = 60;
                    $staleDays = 30;

                    $highRiskCount = $projects->filter(function($p) use ($highRiskThreshold) {
                        return (int)($p->health_overall ?? 0) > 0
                            && (int)$p->health_overall < $highRiskThreshold;
                    })->count();

                    $runningCount = $projects->filter(function($p) use ($highRiskThreshold) {
                        $health = (int)($p->health_overall ?? 0);
                        return ($p->status ?? null) === 'active' && $health >= $highRiskThreshold;
                    })->count();

                    $pausedCount = $projects->filter(function($p) {
                        return ($p->status ?? null) === 'paused';
                    })->count();

                    $staleCount = $projects->filter(function($p) use ($staleDays) {
                        $last = $p->last_synced_at ?? null;
                        if (! $last) return true;
                        try {
                            return $last->lt(now()->subDays($staleDays));
                        } catch (\Throwable $e) {
                            return false;
                        }
                    })->count();

                    $totalProjects = $projects->count();
                    $avgHealth = $totalProjects ? round($projects->avg('health_overall') ?? 0) : 0;

                    $formatDate = function ($date) {
                        if (! $date) return 'Geen data';
                        try {
                            return $date->format('d-m-Y');
                        } catch (\Throwable $e) {
                            return (string)$date;
                        }
                    };
                @endphp

                {{-- Titel + samenvatting --}}
                <div class="w-full flex items-center justify-between gap-4 mb-6">
                    <div class="min-w-0">
                        <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                            SEO projecten
                        </h3>
                        <p class="text-xs text-[#21555880] font-semibold mt-1 truncate">
                            Zie in één oogopslag welke klanten goed staan, waar risico zit
                            en welke projecten aandacht nodig hebben.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        {{-- Refresh / herladen --}}
                        <a href="{{ route('support.seo.projects.index') }}"
                            class="px-6 py-3 text-white font-semibold text-sm bg-[#215558] hover:bg-[#215558]/80 transition duration-300 rounded-full text-center">
                            Vernieuw overzicht
                        </a>

                        {{-- Nieuw SEO project --}}
                        <a href="{{ route('support.seo.projects.create') }}"
                            class="px-6 py-3 text-[#215558] font-semibold text-sm bg-white/90 hover:bg-white transition duration-300 rounded-full text-center">
                            Nieuw SEO project
                        </a>
                    </div>

                </div>

                {{-- Samenvattingsbadges --}}
                <div class="flex flex-wrap items-center justify-between p-2 rounded-full bg-[#f3f8f8] mb-4 gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="py-2 px-3 rounded-full text-xs font-semibold bg-emerald-500 text-white text-center">
                            Lopende trajecten: {{ $runningCount }}
                        </span>
                        <span class="py-2 px-3 rounded-full text-xs font-semibold bg-red-500 text-white text-center">
                            Hoog risico: {{ $highRiskCount }}
                        </span>
                        <span class="py-2 px-3 rounded-full text-xs font-semibold bg-orange-500 text-white text-center">
                            Geen recente audit / sync: {{ $staleCount }}
                        </span>
                        <span class="py-2 px-3 rounded-full text-xs font-semibold bg-gray-500 text-white text-center">
                            Totaal: {{ $totalProjects }}
                        </span>
                    </div>
                    <span class="py-2 px-3 rounded-full text-xs font-extrabold text-[#215558]">
                        Gemiddelde gezondheid:&nbsp;{{ $avgHealth }} / 100
                    </span>
                </div>

                {{-- Sectie: Overzicht --}}
                <div class="w-full flex items-center gap-2 min-w-0 mb-4">
                    <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                        Overzicht
                    </h3>
                    <button type="button"
                            class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                            @click="openOverzichtSection = !openOverzichtSection">
                        <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                           :class="openOverzichtSection ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                </div>

                <div x-show="openOverzichtSection" x-transition>
                    @if($projects->count())
                        <div class="flex items-center justify-between mt-1 mb-3">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Filter: Alle --}}
                                <button
                                    type="button"
                                    @click="activeStatus = 'all'"
                                    :class="[
                                        'flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer',
                                        activeStatus === 'all'
                                            ? 'bg-[#215558] border-[#215558] text-white'
                                            : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'
                                    ]"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#215558]"></span>
                                    <span>Alle projecten</span>
                                </button>

                                {{-- Lopend (active en gezond) --}}
                                <button
                                    type="button"
                                    @click="activeStatus = activeStatus === 'running' ? 'all' : 'running'"
                                    :class="[
                                        'flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer',
                                        activeStatus === 'running'
                                            ? 'bg-[#C2F0D5] border-[#20603a] text-[#20603a]'
                                            : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'
                                    ]"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#20603a]"></span>
                                    <span>Lopend</span>
                                </button>

                                {{-- Hoog risico --}}
                                <button
                                    type="button"
                                    @click="activeStatus = activeStatus === 'risk' ? 'all' : 'risk'"
                                    :class="[
                                        'flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer',
                                        activeStatus === 'risk'
                                            ? 'bg-[#ffb3b3] border-[#8a2a2d] text-[#8a2a2d]'
                                            : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'
                                    ]"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#8a2a2d]"></span>
                                    <span>Hoog risico</span>
                                </button>

                                {{-- Gepauzeerd --}}
                                <button
                                    type="button"
                                    @click="activeStatus = activeStatus === 'paused' ? 'all' : 'paused'"
                                    :class="[
                                        'flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border transition cursor-pointer',
                                        activeStatus === 'paused'
                                            ? 'bg-[#e5e7eb] border-[#4b5563] text-[#111827]'
                                            : 'bg-white border-gray-200 text-[#215558] opacity-60 hover:opacity-100'
                                    ]"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#4b5563]"></span>
                                    <span>Gepauzeerd</span>
                                </button>
                            </div>

                            <input
                                type="text"
                                x-model="searchTerm"
                                class="w-[260px] p-2 text-xs text-[#215558] font-semibold rounded-xl border bg-white border-gray-200 outline-none focus:border-[#3b8b8f] transition duration-300"
                                placeholder="Zoek op domein of bedrijfsnaam"
                            >
                        </div>

                        {{-- Tabel --}}
                        <div class="w-full p-8 bg-[#f3f8f8] rounded-4xl">
                            <div class="grid grid-cols-8 gap-2 pb-4 border-b border-b-gray-200">
                                <p
                                    class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none"
                                    @click="sortByDomain()"
                                >
                                    Website / domein
                                    <span class="inline-flex w-3 h-3 items-center justify-center">
                                        <i
                                            class="fa-solid fa-chevron-down text-[9px] opacity-30"
                                            x-show="sortKey !== 'domain' || !sortDir"
                                        ></i>
                                        <i
                                            class="fa-solid fa-chevron-down text-[9px]"
                                            x-show="sortKey === 'domain' && sortDir === 'asc'"
                                        ></i>
                                        <i
                                            class="fa-solid fa-chevron-up text-[9px]"
                                            x-show="sortKey === 'domain' && sortDir === 'desc'"
                                        ></i>
                                    </span>
                                </p>
                                <p class="text-xs font-bold text-[#215558]">Bedrijf</p>
                                <p class="text-xs font-bold text-[#215558]">Status</p>
                                <p
                                    class="text-xs font-bold text-[#215558] flex items-center gap-1 cursor-pointer select-none"
                                    @click="sortByHealth()"
                                >
                                    Gezondheid
                                    <span class="inline-flex w-3 h-3 items-center justify-center">
                                        <i
                                            class="fa-solid fa-chevron-down text-[9px] opacity-30"
                                            x-show="sortKey !== 'health' || !sortDir"
                                        ></i>
                                        <i
                                            class="fa-solid fa-chevron-down text-[9px]"
                                            x-show="sortKey === 'health' && sortDir === 'desc'"
                                        ></i>
                                        <i
                                            class="fa-solid fa-chevron-up text-[9px]"
                                            x-show="sortKey === 'health' && sortDir === 'asc'"
                                        ></i>
                                    </span>
                                </p>
                                <p class="text-xs font-bold text-[#215558]">Zichtbaarheid</p>
                                <p class="text-xs font-bold text-[#215558]">Organisch verkeer</p>
                                <p class="text-xs font-bold text-[#215558]">Laatste audit / sync</p>
                                <p class="text-xs font-bold text-[#215558] text-right">Acties</p>
                            </div>

                            <div class="divide-gray-200 flex flex-col gap-2 pt-4" x-ref="projectsBody">
                                @foreach($projects as $project)
                                    @php
                                        /** @var \App\Models\SeoProject $project */
                                        $domain = $project->domain ?? 'Onbekende site';
                                        $companyName = optional($project->company)->name ?? 'Onbekend bedrijf';

                                        $health = (int)($project->health_overall ?? 0);
                                        $visibility = $project->visibility_index ?? null;
                                        $traffic = $project->organic_traffic ?? null;
                                        $lastSync = $project->last_synced_at ?? $project->created_at;

                                        // status label + kleur
                                        $statusRaw = $project->status ?? 'active';
                                        if ($statusRaw === 'paused') {
                                            $statusLabel = 'Gepauzeerd';
                                            $statusClasses = 'bg-gray-100 text-gray-700';
                                            $statusKey = 'paused';
                                        } elseif ($health > 0 && $health < $highRiskThreshold) {
                                            $statusLabel = 'Hoog risico';
                                            $statusClasses = 'bg-red-100 text-red-700';
                                            $statusKey = 'risk';
                                        } elseif ($statusRaw === 'onboarding') {
                                            $statusLabel = 'Onboarding';
                                            $statusClasses = 'bg-cyan-100 text-cyan-700';
                                            $statusKey = 'running';
                                        } else {
                                            $statusLabel = 'Lopend';
                                            $statusClasses = 'bg-emerald-100 text-emerald-700';
                                            $statusKey = 'running';
                                        }

                                        // health kleur
                                        if ($health >= 80) {
                                            $healthColor = 'text-emerald-600';
                                        } elseif ($health >= 60) {
                                            $healthColor = 'text-amber-600';
                                        } elseif ($health > 0) {
                                            $healthColor = 'text-red-600';
                                        } else {
                                            $healthColor = 'text-gray-400';
                                        }

                                        $visibilityText = $visibility !== null
                                            ? number_format($visibility, 2, ',', '.')
                                            : 'Geen data';

                                        $trafficText = $traffic !== null
                                            ? number_format($traffic, 0, ',', '.')
                                            : 'Geen data';
                                    @endphp

                                    <div
                                        class="grid grid-cols-8 gap-2 items-center text-sm text-[#215558] font-medium"
                                        data-index="{{ $loop->index }}"
                                        data-domain="{{ $domain }}"
                                        data-company="{{ $companyName }}"
                                        data-health="{{ $health }}"
                                        data-status="{{ $statusKey }}"
                                        x-show="(activeStatus === 'all' || activeStatus === '{{ $statusKey }}') && matches($el)"
                                        x-cloak
                                    >
                                        <p class="font-bold truncate" title="{{ $domain }}">
                                            {{ $domain }}
                                        </p>
                                        <p class="truncate" title="{{ $companyName }}">
                                            {{ $companyName }}
                                        </p>
                                        <div class="flex items-center">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $statusClasses }}">
                                                {{ $statusLabel }}
                                            </span>
                                        </div>
                                        <p class="{{ $healthColor }}">
                                            @if($health > 0)
                                                {{ $health }} / 100
                                            @else
                                                <span class="text-xs text-gray-400 italic">Nog geen score</span>
                                            @endif
                                        </p>
                                        <p>
                                            {{ $visibilityText }}
                                        </p>
                                        <p>
                                            {{ $trafficText }}
                                        </p>
                                        <p>
                                            {{ $formatDate($lastSync) }}
                                        </p>
                                        <div class="flex items-center justify-end">
                                            <a href="{{ route('support.seo.projects.show', $project) }}"
                                               class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer">
                                                <i class="fa-solid fa-up-right-from-square text-[#215558] text-xs"></i>
                                                <div
                                                    class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute bottom-[135%] right-0
                                                            opacity-0 invisible translate-y-1 pointer-events-none
                                                            group-hover:opacity-100 group-hover:visible group-hover:translate-y-0 group-hover:pointer-events-auto
                                                            transition-all duration-200 ease-out z-10">
                                                    <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                                                        Bekijk SEO project
                                                    </p>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-xs text-gray-500">
                            Er zijn nog geen SEO projecten gevonden.
                        </p>
                    @endif
                </div>

                {{-- Sectie: Statistieken (nu simpel, later kunnen we Chart.js koppelen) --}}
                <div class="w-full flex items-center gap-2 min-w-0 pt-8">
                    <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                        Statistieken
                    </h3>
                    <button type="button"
                            class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-gray-200 transition cursor-pointer mr-1"
                            @click="openStatistiekenSection = !openStatistiekenSection">
                        <i class="fa-solid fa-chevron-down text-[#215558] fa-xs transform transition-transform duration-200"
                           :class="openStatistiekenSection ? 'rotate-180' : 'rotate-0'"></i>
                    </button>
                </div>
                <div
                    x-show="openStatistiekenSection"
                    x-transition
                    class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-[#f3f8f8] rounded-4xl p-8 flex flex-col justify-between">
                        <p class="text-lg font-bold text-[#215558] mb-2 text-left">
                            Lopende trajecten
                        </p>
                        <p class="text-3xl font-extrabold text-[#215558] mb-1">
                            {{ $runningCount }}
                        </p>
                        <p class="text-[11px] font-semibold text-[#21555880]">
                            Klanten waarbij actief aan SEO wordt gewerkt.
                        </p>
                    </div>
                    <div class="bg-[#f3f8f8] rounded-4xl p-8 flex flex-col justify-between">
                        <p class="text-lg font-bold text-[#215558] mb-2 text-left">
                            Hoog risico
                        </p>
                        <p class="text-3xl font-extrabold text-red-600 mb-1">
                            {{ $highRiskCount }}
                        </p>
                        <p class="text-[11px] font-semibold text-[#21555880]">
                            Projecten met een gezondheidsscore lager dan {{ $highRiskThreshold }}.
                        </p>
                    </div>
                    <div class="bg-[#f3f8f8] rounded-4xl p-8 flex flex-col justify-between">
                        <p class="text-lg font-bold text-[#215558] mb-2 text-left">
                            Geen recente audit
                        </p>
                        <p class="text-3xl font-extrabold text-amber-600 mb-1">
                            {{ $staleCount }}
                        </p>
                        <p class="text-[11px] font-semibold text-[#21555880]">
                            Projecten zonder audit of sync in de afgelopen {{ $staleDays }} dagen.
                        </p>
                    </div>
                    <div class="bg-[#f3f8f8] rounded-4xl p-8 flex flex-col justify-between">
                        <p class="text-lg font-bold text-[#215558] mb-2 text-left">
                            Gem. gezondheid
                        </p>
                        <p class="text-3xl font-extrabold text-[#215558] mb-1">
                            {{ $avgHealth }} / 100
                        </p>
                        <p class="text-[11px] font-semibold text-[#21555880]">
                            Gemiddelde overall score van alle SEO projecten.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
