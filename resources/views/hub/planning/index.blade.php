@extends('hub.layouts.app')

@section('content')
    <style>
        .custom-scroll {
            scrollbar-width: thin;
            scrollbar-color: #191D3820 transparent;
        }
        .custom-scroll::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background-color: #191D3820; border-radius: 9999px; }
        .custom-scroll::-webkit-scrollbar-button { width: 0; height: 0; display: none; }
    </style>

    @php
        use Carbon\Carbon;

        // section default
        $section = $section ?? request()->query('section', 'today');
        $baseUrl = url('/app/planning-management');

        $view = request()->query('view', 'list'); // list | map | calendar (calendar later)
        if (!in_array($view, ['list','map','calendar'], true)) $view = 'list';

        $groupBy = request()->query('group_by', 'start_date'); // start_date | photographer | region
        if (!in_array($groupBy, ['start_date','photographer','region'], true)) $groupBy = 'start_date';

        $range = request()->query('range', 'all'); // all | today | this_week | this_month | this_year | future_only
        if (!in_array($range, ['all','today','this_week','this_month','this_year','future_only'], true)) $range = 'all';

        $statusPill = function (?string $status) {
            $status = strtolower(trim((string) $status));

            return match ($status) {
                'new' => ['label' => 'Nieuw', 'class' => 'text-[#2A324B] bg-[#2A324B]/15'],
                'planned' => ['label' => 'Ingepland', 'class' => 'text-[#87A878] bg-[#87A878]/15'],
                'done' => ['label' => 'Afgerond', 'class' => 'text-[#009AC3] bg-[#009AC3]/15'],
                'cancelled', 'canceled' => ['label' => 'Geannuleerd', 'class' => 'text-[#DF2935] bg-[#DF2935]/15'],
                default => ['label' => $status !== '' ? ucfirst($status) : 'Onbekend', 'class' => 'text-[#2A324B] bg-[#2A324B]/15'],
            };
        };

        $formatTime = function ($dt) {
            return $dt ? Carbon::parse($dt)->format('H:i') : '-';
        };

        $formatDateNlLong = function ($dt) {
            return $dt ? Carbon::parse($dt)->locale('nl')->translatedFormat('l j F Y') : '';
        };

        $formatDateNlShort = function ($dt) {
            return $dt ? Carbon::parse($dt)->locale('nl')->translatedFormat('D j M.') : '-';
        };

        $projectUrl = function ($project) {
            return $project ? route('support.projecten.show', $project) : '#';
        };

        $projectTitle = function ($project) {
            return $project?->title ?: 'Project';
        };

        $projectClientName = function ($project) {
            return $project?->client?->name ?: '-';
        };

        $planningLocation = function ($pi) {
            if (!empty($pi->location)) return $pi->location;

            $or = $pi->project?->onboardingRequest;
            if (!$or) return '-';

            $addr = trim(($or->address ?? '') . ', ' . ($or->postcode ?? '') . ' ' . ($or->city ?? ''));
            $addr = trim($addr, " ,");

            return $addr !== '' ? $addr : '-';
        };

        $planningRegion = function ($pi) {
            $or = $pi->project?->onboardingRequest;
            if ($or && !empty($or->city)) return $or->city;

            $loc = (string) ($pi->location ?? '');
            if (str_contains($loc, ',')) {
                $parts = array_map('trim', explode(',', $loc));
                return end($parts) ?: '-';
            }

            return '-';
        };

        $categoryLabel = function ($project) {
            $cat = trim((string) ($project?->category ?? ''));
            return $cat !== '' ? $cat : 'onboarding';
        };

        // Grouping helper for planning list/map
        $groupedPlanning = function ($items) use ($groupBy, $planningRegion) {
            if ($groupBy === 'photographer') {
                return $items->groupBy(function ($pi) {
                    return $pi->assignee?->name ?: 'Niet toegewezen';
                });
            }

            if ($groupBy === 'region') {
                return $items->groupBy(function ($pi) use ($planningRegion) {
                    return $planningRegion($pi) ?: 'Onbekend';
                });
            }

            return $items->groupBy(function ($pi) {
                $d = $pi->start_at ? Carbon::parse($pi->start_at)->toDateString() : null;
                return $d ?: 'zonder-datum';
            });
        };

        $planningGroupTitle = function ($key) use ($groupBy, $formatDateNlLong) {
            if ($groupBy === 'start_date') {
                if ($key === 'zonder-datum') return 'Zonder datum';
                return ucfirst($formatDateNlLong($key));
            }
            return $key;
        };

        $rangeLabel = function ($range) {
            return match ($range) {
                'today' => 'Vandaag',
                'this_week' => 'Deze week',
                'this_month' => 'Deze maand',
                'this_year' => 'Dit jaar',
                'future_only' => 'Alleen toekomst',
                default => 'Alle planningen',
            };
        };

        $hasActiveFilter = (isset($filterDate) && $filterDate) || ($range !== 'all');
    @endphp

    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

            {{-- TODAY --}}
            @if ($section === 'today')
                <div class="shrink-0 w-full border-b border-gray-200 pb-6 mb-6">
                    <div class="w-full flex items-center justify-between gap-6">
                        <a href="{{ $baseUrl . '?section=today&date=' . ($prevDate?->toDateString() ?? now()->subDay()->toDateString()) }}"
                           class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <i class="fa-solid fa-chevron-left text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">Vorige dag</span>
                        </a>

                        <div class="text-center min-w-0">
                            <h1 class="text-[#191D38] font-black text-xl leading-tight">Planningsoverzicht</h1>
                            <p class="text-sm text-[#191D38]/70 font-semibold mt-1">
                                {{ ucfirst($dateLabel ?? now()->locale('nl')->translatedFormat('l j F Y')) }}
                            </p>
                        </div>

                        <a href="{{ $baseUrl . '?section=today&date=' . ($nextDate?->toDateString() ?? now()->addDay()->toDateString()) }}"
                           class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">Volgende dag</span>
                            <i class="fa-solid fa-chevron-right text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                        </a>
                    </div>
                </div>

                <div class="flex-1 min-h-0 bg-[#191D38]/5 rounded-2xl overflow-hidden flex flex-col">
                    <div class="shrink-0 px-6 py-4 bg-white/60 border-b border-[#191D38]/10">
                        <div class="grid grid-cols-[1.7fr_1.1fr_1fr_0.6fr] items-center gap-6">
                            <p class="text-[#191D38] font-bold text-xs opacity-50">Project</p>
                            <p class="text-[#191D38] font-bold text-xs opacity-50">Tijd</p>
                            <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Klant</p>
                            <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
                        </div>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto custom-scroll">
                        @if (($todayRequests ?? collect())->isEmpty())
                            <div class="h-full w-full flex items-center justify-center px-6">
                                <p class="text-[#191D38]/70 text-sm font-semibold">Geen planningen gevonden voor deze periode.</p>
                            </div>
                        @else
                            <div class="divide-y divide-[#191D38]/10">
                                @foreach ($todayRequests as $pi)
                                    @php
                                        $project = $pi->project;
                                        $title = $projectTitle($project);
                                        $location = $planningLocation($pi);
                                        $client = $projectClientName($project);

                                        $start = $formatTime($pi->start_at);
                                        $end   = $formatTime($pi->end_at);

                                        $category = $categoryLabel($project);
                                        $pill = $statusPill($project?->status ?? null);
                                    @endphp

                                    <div class="px-6 py-5">
                                        <div class="grid grid-cols-[1.7fr_1.1fr_1fr_0.6fr] items-center gap-6">

                                            <a href="{{ $projectUrl($project) }}" class="min-w-0 block">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <span class="inline-flex items-center gap-2 text-xs font-semibold text-[#009AC3]">
                                                        <i class="fa-solid fa-camera"></i> {{ $category }}
                                                    </span>

                                                    <span class="{{ $pill['class'] }} inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-semibold">
                                                        {{ $pill['label'] }}
                                                    </span>
                                                </div>

                                                <p class="text-[#191D38] font-black text-base leading-tight truncate">{{ $title }}</p>
                                                <p class="text-[#191D38]/70 text-sm font-semibold mt-1 truncate">{{ $location }}</p>
                                            </a>

                                            <div class="text-[#191D38] font-semibold text-sm">
                                                {{ $start }} - {{ $end }}
                                            </div>

                                            <div class="text-right min-w-0">
                                                <p class="text-[#191D38] font-semibold text-sm truncate">{{ $client }}</p>
                                                <span class="inline-flex items-center justify-center mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-[#009AC3]/15 text-[#009AC3]">
                                                    Project
                                                </span>
                                            </div>

                                            <div class="flex items-center justify-end gap-3">
                                                <a href="{{ route('support.planning.edit', $pi) }}" class="cursor-pointer" title="Bewerken">
                                                    <i class="fa-solid fa-pencil hover:text-[#009AC3] transition duration-200"></i>
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('support.planning.destroy', $pi) }}"
                                                      onsubmit="return confirm('Weet je zeker dat je deze planning wilt verwijderen?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="cursor-pointer" title="Verwijderen">
                                                        <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                                                    </button>
                                                </form>
                                            </div>

                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif


            {{-- PLANNING --}}
            @if ($section === 'planning')
                <div class="shrink-0 w-full mb-4 flex items-center justify-between gap-3 flex-wrap">

                    {{-- ✅ Filter dropdown (standaard: "Filter op datum") --}}
                    <div x-data="{ open:false }" class="relative inline-block">
                        <form x-ref="filterForm" method="GET" action="{{ $baseUrl }}" class="flex items-center gap-2">
                            <input type="hidden" name="section" value="planning">
                            <input type="hidden" name="view" value="{{ $view }}">
                            <input type="hidden" name="group_by" value="{{ $groupBy }}">
                            <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">

                            {{-- range hidden, wordt gezet vanuit dropdown --}}
                            <input type="hidden" name="range" value="{{ $range }}" x-ref="range">

                            {{-- Button --}}
                            <button
                                type="button"
                                @click="open = !open"
                                @keydown.escape.window="open = false"
                                class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300"
                            >
                                <i class="fa-solid fa-filter text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                                <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">
                                    Filter op datum
                                </span>

                                {{-- subtiele status --}}
                                <span class="text-xs font-semibold text-[#191D38]/60 group-hover:text-white/80 transition duration-200">
                                    @if(isset($filterDate) && $filterDate)
                                        {{ ucfirst($formatDateNlShort($filterDate)) }}
                                    @else
                                        {{ $rangeLabel($range) }}
                                    @endif
                                </span>

                                <i class="fa-solid fa-chevron-down text-[#191D38]/60 group-hover:text-white transition duration-200 text-xs"></i>
                            </button>

                            {{-- Dropdown panel --}}
                            <div
                                x-show="open"
                                x-transition
                                @click.outside="open = false"
                                class="absolute left-0 top-full z-[2000] mt-2 w-[320px] rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden"
                                style="display:none;"
                                >
                                <div class="p-2">
                                    <p class="px-3 pt-2 pb-2 text-[11px] font-black text-[#191D38]/60 uppercase tracking-wide">
                                        Periode
                                    </p>

                                    @php
                                        $rangeOptions = [
                                            'all' => ['label' => 'Alle planningen', 'icon' => 'fa-infinity'],
                                            'today' => ['label' => 'Vandaag', 'icon' => 'fa-calendar-day'],
                                            'this_week' => ['label' => 'Deze week', 'icon' => 'fa-calendar-week'],
                                            'this_month' => ['label' => 'Deze maand', 'icon' => 'fa-calendar'],
                                            'this_year' => ['label' => 'Dit jaar', 'icon' => 'fa-calendar-days'],
                                            'future_only' => ['label' => 'Alleen toekomst', 'icon' => 'fa-clock'],
                                        ];
                                    @endphp

                                    <div class="space-y-1">
                                        @foreach($rangeOptions as $key => $opt)
                                            <button
                                                type="button"
                                                class="w-full px-3 py-2 rounded-xl flex items-center justify-between gap-3 hover:bg-[#191D38]/5 transition"
                                                @click="
                                                    $refs.range.value = '{{ $key }}';
                                                    $refs.filterForm.submit();
                                                "
                                            >
                                                <span class="inline-flex items-center gap-2 text-sm font-semibold text-[#191D38]">
                                                    <i class="fa-solid {{ $opt['icon'] }} text-[#009AC3]"></i>
                                                    {{ $opt['label'] }}
                                                </span>

                                                @if($range === $key)
                                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#009AC3]/15 text-[#009AC3] text-xs font-black">
                                                        <i class="fa-solid fa-check"></i>
                                                    </span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </div>

                                    <div class="my-3 border-t border-gray-200"></div>

                                    <p class="px-3 pt-1 pb-2 text-[11px] font-black text-[#191D38]/60 uppercase tracking-wide">
                                        Exacte datum
                                    </p>

                                    <div class="px-3 pb-2">
                                        <div class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 flex items-center gap-3">
                                            <i class="fa-solid fa-calendar text-[#009AC3]"></i>
                                            <input
                                                type="date"
                                                name="date"
                                                value="{{ isset($filterDate) && $filterDate ? $filterDate->toDateString() : '' }}"
                                                class="w-full text-sm font-semibold text-[#191D38] outline-none bg-transparent"
                                                onchange="this.form.submit()"
                                            >
                                        </div>
                                    </div>

                                    @if($hasActiveFilter)
                                        <div class="px-3 pb-3 pt-1">
                                            <a
                                                href="{{ $baseUrl . '?section=planning&view=' . $view . '&group_by=' . $groupBy . '&per_page=' . ($perPage ?? 10) }}"
                                                class="w-full inline-flex items-center justify-center gap-2 h-9 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-[#191D38] hover:bg-[#191D38] hover:text-white hover:border-[#191D38] transition"
                                            >
                                                <i class="fa-solid fa-xmark"></i>
                                                Reset filters
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>

                    {{-- View + Group + Per page --}}
                    <div class="flex items-center gap-2">
                        <form method="GET" action="{{ $baseUrl }}">
                            <input type="hidden" name="section" value="planning">
                            <input type="hidden" name="group_by" value="{{ $groupBy }}">
                            <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                            <input type="hidden" name="range" value="{{ $range }}">
                            @if(isset($filterDate) && $filterDate)
                                <input type="hidden" name="date" value="{{ $filterDate->toDateString() }}">
                            @endif

                            <div class="relative">
                                <i class="fa-solid fa-display absolute left-4 top-1/2 -translate-y-1/2 text-[#191D38]/60 text-sm pointer-events-none"></i>

                                <select
                                    name="view"
                                    class="h-9 bg-white border border-gray-200 pl-10 pr-10 rounded-full text-sm text-[#191D38] font-semibold outline-none appearance-none cursor-pointer hover:border-[#009AC3] transition"
                                    onchange="this.form.submit()"
                                >
                                    <option value="list" @selected($view==='list')>Lijstweergave</option>
                                    <option value="map" @selected($view==='map')>Kaart</option>
                                    <option value="calendar" @selected($view==='calendar')>Kalender</option>
                                </select>

                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#191D38]/50 text-xs pointer-events-none"></i>
                            </div>

                        </form>

                        <form method="GET" action="{{ $baseUrl }}">
                            <input type="hidden" name="section" value="planning">
                            <input type="hidden" name="view" value="{{ $view }}">
                            <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                            <input type="hidden" name="range" value="{{ $range }}">
                            @if(isset($filterDate) && $filterDate)
                                <input type="hidden" name="date" value="{{ $filterDate->toDateString() }}">
                            @endif

                            <div class="relative">
                                <i class="fa-solid fa-layer-group absolute left-4 top-1/2 -translate-y-1/2 text-[#191D38]/60 text-sm pointer-events-none"></i>

                                <select
                                    name="group_by"
                                    class="h-9 bg-white border border-gray-200 pl-10 pr-10 rounded-full text-sm text-[#191D38] font-semibold outline-none appearance-none cursor-pointer hover:border-[#009AC3] transition"
                                    onchange="this.form.submit()"
                                >
                                    <option value="start_date" @selected($groupBy==='start_date')>Startdatum</option>
                                    <option value="photographer" @selected($groupBy==='photographer')>Fotograaf</option>
                                    <option value="region" @selected($groupBy==='region')>Regio</option>
                                </select>

                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#191D38]/50 text-xs pointer-events-none"></i>
                            </div>

                        </form>

                        <form method="GET" action="{{ $baseUrl }}">
                            <input type="hidden" name="section" value="planning">
                            <input type="hidden" name="view" value="{{ $view }}">
                            <input type="hidden" name="group_by" value="{{ $groupBy }}">
                            <input type="hidden" name="range" value="{{ $range }}">
                            @if(isset($filterDate) && $filterDate)
                                <input type="hidden" name="date" value="{{ $filterDate->toDateString() }}">
                            @endif

                            <div class="relative">
                                <i class="fa-solid fa-list-ol absolute left-4 top-1/2 -translate-y-1/2 text-[#191D38]/60 text-sm pointer-events-none"></i>

                                <select
                                    name="per_page"
                                    class="h-9 bg-white border border-gray-200 pl-10 pr-10 rounded-full text-sm text-[#191D38] font-semibold outline-none appearance-none cursor-pointer hover:border-[#009AC3] transition"
                                    onchange="this.form.submit()"
                                >
                                    @foreach([10,25,50,100] as $opt)
                                        <option value="{{ $opt }}" @selected(($perPage ?? 10) === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>

                                <i class="fa-solid fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-[#191D38]/50 text-xs pointer-events-none"></i>
                            </div>

                        </form>
                    </div>
                </div>
                {{-- Kalenderweergave --}}
                @if($view === 'calendar')
                    {{-- FullCalendar --}}
                    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css">
                    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
                    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>

                    <style>
                        /* ===== FullCalendar hard reset naar jouw styling ===== */
                        .planning-cal .fc { font-family: inherit; }
                        .planning-cal .fc .fc-scrollgrid,
                        .planning-cal .fc .fc-scrollgrid table {
                            border-color: rgba(25,29,56,.10) !important;
                        }

                        .planning-cal .fc .fc-col-header-cell-cushion {
                            padding: 10px 0 !important;
                            font-weight: 900 !important;
                            color: rgba(25,29,56,.55) !important;
                            text-transform: lowercase;
                            font-size: 12px !important;
                            letter-spacing: .02em;
                        }

                        .planning-cal .fc .fc-daygrid-day-number {
                            font-weight: 800 !important;
                            color: rgba(25,29,56,.85) !important;
                            padding: 10px !important;
                            font-size: 12px !important;
                        }

                        .planning-cal .fc .fc-daygrid-day {
                            background: #fff !important;
                        }

                        .planning-cal .fc .fc-daygrid-day.fc-day-other {
                            background: rgba(25,29,56,.02) !important;
                        }

                        .planning-cal .fc .fc-day-today {
                            background: rgba(0,154,195,.08) !important;
                        }

                        /* events */
                        .planning-cal .fc .fc-daygrid-event {
                            border: 0 !important;
                            background: transparent !important;
                            padding: 0 !important;
                            margin: 6px 8px 0 8px !important;
                        }

                        .planning-cal .fc .fc-daygrid-event .fc-event-main {
                            padding: 0 !important;
                        }

                        .planning-cal .fc .fc-event-title,
                        .planning-cal .fc .fc-event-time {
                            font-weight: 800 !important;
                        }

                        .planning-cal .fc .fc-more-link {
                            margin: 6px 10px !important;
                            font-weight: 800 !important;
                            color: #009AC3 !important;
                        }

                        /* jouw event pill */
                        .planning-cal-event {
                            display: flex;
                            align-items: center;
                            gap: 8px;
                            width: 100%;
                            padding: 7px 10px;
                            border-radius: 9999px;
                            border: 1px solid rgba(25,29,56,.10);
                            background: rgba(255,255,255,.95);
                            backdrop-filter: blur(8px);
                            color: #191D38;
                        }

                        .planning-cal-dot {
                            width: 8px;
                            height: 8px;
                            border-radius: 9999px;
                            background: #009AC3;
                            flex: 0 0 auto;
                        }

                        .planning-cal-time {
                            font-size: 11px;
                            font-weight: 900;
                            color: rgba(25,29,56,.55);
                            flex: 0 0 auto;
                        }

                        .planning-cal-title {
                            font-size: 12px;
                            font-weight: 900;
                            color: #191D38;
                            min-width: 0;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            white-space: nowrap;
                        }

                        .planning-cal-event:hover {
                            border-color: rgba(0,154,195,.35);
                            background: rgba(0,154,195,.06);
                        }
                    </style>

                    <div class="planning-cal flex-1 min-h-0 bg-[#191D38]/5 rounded-2xl overflow-hidden ring-1 ring-[#191D38]/10 flex flex-col">
                        {{-- Header die WEL in jouw stijl past --}}
                        <div class="shrink-0 px-6 py-4 bg-white/70 border-b border-[#191D38]/10">
                            <div class="flex items-center justify-between gap-4 flex-wrap">
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        class="w-10 h-9 rounded-full border border-gray-200 bg-white flex items-center justify-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300"
                                        onclick="window.__planningCalPrev && window.__planningCalPrev();"
                                        title="Vorige"
                                    >
                                        <i class="fa-solid fa-chevron-left text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="w-10 h-9 rounded-full border border-gray-200 bg-white flex items-center justify-center hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300"
                                        onclick="window.__planningCalNext && window.__planningCalNext();"
                                        title="Volgende"
                                    >
                                        <i class="fa-solid fa-chevron-right text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                                    </button>

                                    <button
                                        type="button"
                                        class="px-4 h-9 rounded-full border border-gray-200 bg-white text-sm font-semibold text-[#191D38] hover:bg-[#009AC3] hover:text-white hover:border-[#009AC3] transition"
                                        onclick="window.__planningCalToday && window.__planningCalToday();"
                                    >
                                        Vandaag
                                    </button>
                                </div>

                                <div class="min-w-0 text-center">
                                    <p class="text-[#191D38] font-black text-lg leading-tight" id="planningCalTitle">Kalender</p>
                                    <p class="text-[#191D38]/70 text-xs font-semibold mt-1 truncate">
                                        Klik op een item om het project te openen
                                    </p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <div class="inline-flex rounded-full border border-gray-200 bg-white overflow-hidden">
                                        <button
                                            type="button"
                                            class="px-4 h-9 text-sm font-semibold text-[#191D38] hover:bg-[#009AC3] hover:text-white transition"
                                            onclick="window.__planningCalView && window.__planningCalView('dayGridMonth');"
                                            id="btnCalMonth"
                                        >
                                            maand
                                        </button>
                                        <button
                                            type="button"
                                            class="px-4 h-9 text-sm font-semibold text-[#191D38] hover:bg-[#009AC3] hover:text-white transition border-l border-gray-200"
                                            onclick="window.__planningCalView && window.__planningCalView('timeGridWeek');"
                                            id="btnCalWeek"
                                        >
                                            week
                                        </button>
                                        <button
                                            type="button"
                                            class="px-4 h-9 text-sm font-semibold text-[#191D38] hover:bg-[#009AC3] hover:text-white transition border-l border-gray-200"
                                            onclick="window.__planningCalView && window.__planningCalView('timeGridDay');"
                                            id="btnCalDay"
                                        >
                                            dag
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Calendar canvas --}}
                        <div class="flex-1 min-h-0 p-4">
                            <div id="planningCalendar" class="h-full bg-white rounded-2xl border border-gray-200 overflow-hidden"></div>
                        </div>
                    </div>

                    <script>
                        (function () {
                            const el = document.getElementById('planningCalendar');
                            if (!el || typeof FullCalendar === 'undefined') return;

                            const params = new URLSearchParams(window.location.search);
                            const titleEl = document.getElementById('planningCalTitle');

                            const btnMonth = document.getElementById('btnCalMonth');
                            const btnWeek  = document.getElementById('btnCalWeek');
                            const btnDay   = document.getElementById('btnCalDay');

                            const setActive = (viewType) => {
                                const activeClasses = ['bg-[#009AC3]','text-white'];
                                const normalClasses = ['text-[#191D38]'];

                                const apply = (btn, active) => {
                                    btn.classList.remove(...activeClasses);
                                    btn.classList.add(...normalClasses);
                                    if (active) {
                                        btn.classList.add(...activeClasses);
                                        btn.classList.remove(...normalClasses);
                                    }
                                };

                                apply(btnMonth, viewType === 'dayGridMonth');
                                apply(btnWeek,  viewType === 'timeGridWeek');
                                apply(btnDay,   viewType === 'timeGridDay');
                            };

                            const formatTitle = (date) => {
                                // NL maand + jaar, eerste letter uppercase
                                const txt = new Intl.DateTimeFormat('nl-NL', { month: 'long', year: 'numeric' }).format(date);
                                return txt.charAt(0).toUpperCase() + txt.slice(1);
                            };

                            const calendar = new FullCalendar.Calendar(el, {
                                locale: 'nl',
                                firstDay: 1,
                                headerToolbar: false, // we bouwen onze eigen header
                                initialView: 'dayGridMonth',
                                initialDate: params.get('date') || undefined,
                                nowIndicator: true,
                                dayMaxEvents: true,
                                expandRows: true,
                                height: '100%',

                                events: function (fetchInfo, successCallback, failureCallback) {
                                    const qs = new URLSearchParams(window.location.search);
                                    qs.set('ajax', '1');
                                    qs.set('start', fetchInfo.startStr);
                                    qs.set('end', fetchInfo.endStr);

                                    fetch(`{{ $baseUrl }}?${qs.toString()}`, { headers: { 'Accept': 'application/json' } })
                                        .then(r => r.ok ? r.json() : Promise.reject(r))
                                        .then(data => successCallback(data))
                                        .catch(() => failureCallback());
                                },

                                eventClick: function (info) {
                                    if (info.event.url) {
                                        info.jsEvent.preventDefault();
                                        window.location.href = info.event.url;
                                    }
                                },

                                // ✅ jouw event pill render
                                eventContent: function (arg) {
                                    const start = arg.event.start;
                                    const time = start
                                        ? new Intl.DateTimeFormat('nl-NL', { hour: '2-digit', minute: '2-digit' }).format(start)
                                        : '';

                                    const wrap = document.createElement('div');
                                    wrap.className = 'planning-cal-event';

                                    const dot = document.createElement('span');
                                    dot.className = 'planning-cal-dot';

                                    const t = document.createElement('span');
                                    t.className = 'planning-cal-time';
                                    t.textContent = time;

                                    const title = document.createElement('span');
                                    title.className = 'planning-cal-title';
                                    title.textContent = arg.event.title || 'Planning';

                                    wrap.appendChild(dot);
                                    if (time) wrap.appendChild(t);
                                    wrap.appendChild(title);

                                    return { domNodes: [wrap] };
                                },

                                datesSet: function (info) {
                                    // title sync
                                    if (titleEl) titleEl.textContent = formatTitle(info.view.currentStart);
                                    setActive(info.view.type);
                                }
                            });

                            calendar.render();

                            // init title + active buttons
                            if (titleEl) titleEl.textContent = formatTitle(calendar.getDate());
                            setActive(calendar.view.type);

                            window.__planningCalPrev = () => calendar.prev();
                            window.__planningCalNext = () => calendar.next();
                            window.__planningCalToday = () => calendar.today();
                            window.__planningCalView = (v) => calendar.changeView(v);
                        })();
                    </script>
                @else

                    @php
                        $items = ($planningItems ?? collect());
                        $groups = $groupedPlanning($items);
                    @endphp

                    @if($view === 'map')
                        @php
                            $items = ($planningItems ?? collect())->values();

                            $mapPoints = $items->map(function ($pi, $idx) use ($projectTitle, $planningLocation, $projectUrl, $projectClientName, $formatTime) {
                                $project = $pi->project;

                                return [
                                    'id' => (int) $pi->id,
                                    'nr' => $idx + 1,
                                    'title' => $projectTitle($project),
                                    'location' => $planningLocation($pi),
                                    'url' => $projectUrl($project),
                                    'client' => $projectClientName($project),
                                    'time' => trim($formatTime($pi->start_at) . ' - ' . $formatTime($pi->end_at)),
                                ];
                            })->values();
                        @endphp

                        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
                        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

                        <style>
                            .nr-marker {
                                width: 30px;
                                height: 30px;
                                border-radius: 9999px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-weight: 800;
                                font-size: 12px;
                                color: #fff;
                                background: #009AC3;
                                border: 2px solid #fff;
                                box-shadow: 0 6px 16px rgba(25, 29, 56, .25);
                            }
                            .nr-marker--active {
                                transform: scale(1.08);
                                background: #191D38;
                            }
                        </style>

                        <div class="flex-1 min-h-0 relative rounded-2xl overflow-hidden ring-1 ring-[#191D38]/10 bg-white">

                            <div id="planningMap" class="absolute inset-0 z-0"></div>

                            <div x-data="{ open: false }" class="absolute top-4 left-4 right-4 lg:left-auto lg:right-4 lg:w-[460px] z-[1000] pointer-events-auto">
                                {{-- Toggle button (blijft altijd zichtbaar) --}}
                                <div x-show="!open" x-transition class="flex justify-end">
                                    <button
                                        type="button"
                                        @click="open = true"
                                        class="px-3 h-8 rounded-full border border-gray-200 bg-white/95 backdrop-blur text-xs font-semibold text-[#191D38] hover:bg-[#009AC3] hover:text-white hover:border-[#009AC3] transition inline-flex items-center gap-2"
                                    >
                                        <i class="fa-solid fa-eye"></i>
                                        Toon lijst
                                    </button>
                                </div>

                                <div x-show="open" x-transition class="bg-white/95 backdrop-blur rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                                    <div class="px-5 py-4 border-b border-gray-200">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-[#191D38] font-black text-sm">Kaartweergave</p>
                                                <p class="text-[#191D38]/70 text-xs font-semibold mt-1 truncate">
                                                    {{ $items->count() }} planningen met markers
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-2">
                                                <button
                                                    type="button"
                                                    @click="open = false"
                                                    class="px-3 h-8 rounded-full border border-gray-200 bg-white text-xs font-semibold text-[#191D38] hover:bg-[#191D38] hover:text-white hover:border-[#191D38] transition"
                                                >
                                                    <i class="fa-solid fa-eye-slash mr-1"></i>
                                                    Verberg lijst
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="max-h-[38vh] overflow-y-auto custom-scroll divide-y divide-[#191D38]/10">
                                        @if($items->isEmpty())
                                            <div class="px-5 py-5">
                                                <p class="text-[#191D38]/70 text-sm font-semibold">Geen planningen gevonden.</p>
                                            </div>
                                        @else
                                            @foreach($mapPoints as $p)
                                                <button
                                                    type="button"
                                                    class="w-full text-left px-5 py-4 hover:bg-white transition"
                                                    onclick="window.__planningMapFocus && window.__planningMapFocus({{ $p['id'] }});"
                                                >
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-2">
                                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-[#009AC3]/15 text-[#009AC3] text-xs font-black">
                                                                    {{ $p['nr'] }}
                                                                </span>
                                                                <p class="text-[#191D38] font-black text-sm truncate">{{ $p['title'] }}</p>
                                                            </div>

                                                            <p class="text-[#191D38]/70 text-sm font-semibold mt-1 truncate">{{ $p['location'] }}</p>
                                                            <p class="text-[#191D38]/60 text-xs font-semibold mt-2 truncate">
                                                                {{ $p['client'] }} · {{ $p['time'] }}
                                                            </p>
                                                        </div>

                                                        <span class="shrink-0 inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-semibold bg-[#009AC3]/15 text-[#009AC3]">
                                                            Project
                                                        </span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>

                        <script>
                            (function () {
                                const points = @json($mapPoints);

                                const mapEl = document.getElementById('planningMap');
                                if (!mapEl || typeof L === 'undefined') return;

                                const map = L.map('planningMap', { zoomControl: true }).setView([52.1326, 5.2913], 7);

                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(map);

                                const markersById = new Map();
                                const markerGroup = L.featureGroup().addTo(map);

                                const cacheKey = (q) => 'geo_cache_v1:' + q.toLowerCase();
                                const getCached = (q) => {
                                    try { return JSON.parse(localStorage.getItem(cacheKey(q)) || 'null'); } catch (e) { return null; }
                                };
                                const setCached = (q, latlng) => {
                                    try { localStorage.setItem(cacheKey(q), JSON.stringify(latlng)); } catch (e) {}
                                };

                                const escapeHtml = (str) => {
                                    return String(str ?? '')
                                        .replaceAll('&','&amp;')
                                        .replaceAll('<','&lt;')
                                        .replaceAll('>','&gt;')
                                        .replaceAll('"','&quot;')
                                        .replaceAll("'","&#039;");
                                };

                                const numberIcon = (nr, active=false) => {
                                    return L.divIcon({
                                        className: '',
                                        html: `<div class="nr-marker ${active ? 'nr-marker--active' : ''}">${nr}</div>`,
                                        iconSize: [30, 30],
                                        iconAnchor: [15, 30],
                                        popupAnchor: [0, -28]
                                    });
                                };

                                const addMarker = (p, lat, lon) => {
                                    const m = L.marker([lat, lon], { icon: numberIcon(p.nr) }).addTo(markerGroup);
                                    const streetViewUrl = `https://www.google.com/maps?layer=c&cbll=${lat},${lon}`;

                                    const popupHtml = `
                                        <div style="min-width:220px">
                                            <div style="font-weight:800;color:#191D38;margin-bottom:6px">${escapeHtml(p.nr)}. ${escapeHtml(p.title)}</div>
                                            <div style="font-weight:600;color:rgba(25,29,56,.7);margin-bottom:8px">${escapeHtml(p.location)}</div>
                                            <div style="font-weight:600;color:rgba(25,29,56,.6);font-size:12px;margin-bottom:10px">${escapeHtml(p.client)} · ${escapeHtml(p.time)}</div>
                                            <div style="display:flex; gap:8px; flex-wrap:wrap">
                                                <a href="${escapeHtml(p.url)}"
                                                    style="display:inline-block;padding:8px 10px;border-radius:999px;background:rgba(0,154,195,.15);color:#009AC3;font-weight:700;font-size:12px;text-decoration:none">
                                                    Open project
                                                </a>

                                                <a href="${streetViewUrl}"
                                                    target="_blank" rel="noopener"
                                                    style="display:inline-block;padding:8px 10px;border-radius:999px;background:rgba(25,29,56,.08);color:#191D38;font-weight:800;font-size:12px;text-decoration:none">
                                                    Street View
                                                </a>
                                            </div>
                                        </div>
                                    `;
                                    m.bindPopup(popupHtml);

                                    m.on('popupopen', () => m.setIcon(numberIcon(p.nr, true)));
                                    m.on('popupclose', () => m.setIcon(numberIcon(p.nr, false)));

                                    markersById.set(p.id, m);
                                };

                                const fitAll = () => {
                                    const layers = markerGroup.getLayers();
                                    if (!layers.length) {
                                        map.setView([52.1326, 5.2913], 7);
                                        return;
                                    }
                                    map.fitBounds(markerGroup.getBounds(), { padding: [60, 60] });
                                };

                                window.__planningMapFitAll = fitAll;

                                window.__planningMapFocus = (id) => {
                                    const m = markersById.get(id);
                                    if (!m) return;
                                    map.setView(m.getLatLng(), Math.max(map.getZoom(), 13), { animate: true });
                                    m.openPopup();
                                };

                                const sleep = (ms) => new Promise(r => setTimeout(r, ms));

                                const geocode = async (q) => {
                                    const cached = getCached(q);
                                    if (cached && cached.lat && cached.lon) return cached;

                                    const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(q);
                                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                                    if (!res.ok) return null;

                                    const data = await res.json();
                                    if (!Array.isArray(data) || !data.length) return null;

                                    const lat = parseFloat(data[0].lat);
                                    const lon = parseFloat(data[0].lon);
                                    if (!isFinite(lat) || !isFinite(lon)) return null;

                                    const out = { lat, lon };
                                    setCached(q, out);
                                    return out;
                                };

                                const run = async () => {
                                    if (!points.length) return;

                                    for (let i = 0; i < points.length; i++) {
                                        const p = points[i];
                                        const q = (p.location || '').trim();
                                        if (!q) continue;

                                        const cached = getCached(q);
                                        if (cached && cached.lat && cached.lon) {
                                            addMarker(p, cached.lat, cached.lon);
                                            continue;
                                        }

                                        const found = await geocode(q);
                                        if (found) addMarker(p, found.lat, found.lon);

                                        await sleep(1100);
                                    }

                                    fitAll();
                                };

                                run();
                            })();
                        </script>
                    @else

                        {{-- List view: zelfde styling als today --}}
                        <div class="flex-1 min-h-0 bg-[#191D38]/5 rounded-2xl overflow-hidden flex flex-col">
                            <div class="shrink-0 px-6 py-4 bg-white/60 border-b border-[#191D38]/10">
                                <div class="grid grid-cols-[1.7fr_1.1fr_1fr] items-center gap-6">
                                    <p class="text-[#191D38] font-bold text-xs opacity-50">Project</p>
                                    <p class="text-[#191D38] font-bold text-xs opacity-50">Tijd</p>
                                    <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Klant</p>
                                </div>
                            </div>

                            <div class="flex-1 min-h-0 overflow-y-auto custom-scroll">
                                @if ($items->isEmpty())
                                    <div class="h-full w-full flex items-center justify-center px-6">
                                        <p class="text-[#191D38]/70 text-sm font-semibold">Geen planningen gevonden.</p>
                                    </div>
                                @else
                                    <div class="divide-y divide-[#191D38]/10">
                                        @foreach($groups as $gKey => $gItems)
                                            <div class="px-6 py-3 bg-white/40 border-b border-[#191D38]/10">
                                                <p class="text-[#191D38] font-black text-sm">{{ $planningGroupTitle($gKey) }}</p>
                                            </div>

                                            @foreach($gItems as $pi)
                                                @php
                                                    $project = $pi->project;
                                                    $title = $projectTitle($project);
                                                    $location = $planningLocation($pi);
                                                    $client = $projectClientName($project);
                                                    $start = $formatTime($pi->start_at);
                                                    $end   = $formatTime($pi->end_at);
                                                    $pill = $statusPill($project?->status ?? null);
                                                @endphp

                                                <a href="{{ $projectUrl($project) }}" class="block px-6 py-5 hover:bg-white/60 transition duration-200">
                                                    <div class="grid grid-cols-[1.7fr_1.1fr_1fr] items-center gap-6">
                                                        <div class="min-w-0">
                                                            <div class="flex items-center gap-2 mb-2">
                                                                <span class="inline-flex items-center gap-2 text-xs font-semibold text-[#009AC3]">
                                                                    <i class="fa-solid fa-camera"></i> {{ $categoryLabel($project) }}
                                                                </span>
                                                                <span class="{{ $pill['class'] }} inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-semibold">
                                                                    {{ $pill['label'] }}
                                                                </span>
                                                            </div>

                                                            <p class="text-[#191D38] font-black text-base leading-tight truncate">{{ $title }}</p>
                                                            <p class="text-[#191D38]/70 text-sm font-semibold mt-1 truncate">{{ $location }}</p>

                                                            <p class="text-[#191D38]/60 text-xs font-semibold mt-2 truncate">
                                                                {{ $pi->assignee?->name ?: 'Niet toegewezen' }}
                                                            </p>
                                                        </div>

                                                        <div class="text-[#191D38] font-semibold text-sm">
                                                            {{ $start }} - {{ $end }}
                                                        </div>

                                                        <div class="text-right min-w-0">
                                                            <p class="text-[#191D38] font-semibold text-sm truncate">{{ $client }}</p>
                                                            <span class="inline-flex items-center justify-center mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-[#009AC3]/15 text-[#009AC3]">
                                                                Project
                                                            </span>
                                                        </div>
                                                    </div>
                                                </a>
                                            @endforeach
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @endif
            @endif


            {{-- TEAM --}}
            @if ($section === 'team')
                <div class="shrink-0 w-full border-b border-gray-200 pb-6 mb-6">
                    <div class="w-full flex items-center justify-between gap-6">
                        <a href="{{ $baseUrl . '?section=team&date=' . ($teamPrevDate?->toDateString() ?? now()->subDay()->toDateString()) }}"
                           class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <i class="fa-solid fa-chevron-left text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">Vorige dag</span>
                        </a>

                        <div class="text-center min-w-0">
                            <h1 class="text-[#191D38] font-black text-xl leading-tight">Teamoverzicht</h1>
                            <p class="text-sm text-[#191D38]/70 font-semibold mt-1">
                                {{ ucfirst($teamDateLabel ?? now()->locale('nl')->translatedFormat('l j F Y')) }}
                            </p>
                        </div>

                        <a href="{{ $baseUrl . '?section=team&date=' . ($teamNextDate?->toDateString() ?? now()->addDay()->toDateString()) }}"
                           class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">Volgende dag</span>
                            <i class="fa-solid fa-chevron-right text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                        </a>
                    </div>
                </div>

                <div class="flex-1 min-h-0 overflow-y-auto custom-scroll space-y-4">
                    @if (($teamMembers ?? collect())->isEmpty())
                        <div class="flex-1 min-h-0 bg-[#191D38]/5 rounded-2xl p-8 flex items-center justify-center">
                            <p class="text-[#191D38]/70 text-sm font-semibold">Geen fotografen gevonden die deze dag werken.</p>
                        </div>
                    @else
                        @foreach ($teamMembers as $tm)
                            @php $u = $tm['user']; @endphp

                            <div class="w-full bg-white border border-gray-200 rounded-2xl p-6 flex items-center justify-between gap-6">
                                <div class="min-w-0">
                                    <p class="text-[#191D38] font-black text-base truncate">{{ $u->name }}</p>
                                    <p class="text-[#191D38]/70 text-sm font-semibold mt-1">Locatie: {{ $tm['location'] }}</p>
                                </div>

                                <div class="text-center">
                                    <p class="text-[#191D38]/70 text-sm font-semibold">
                                        Werkuren: <span class="text-[#191D38] font-black">{{ $tm['work_start'] }} - {{ $tm['work_end'] }}</span>
                                    </p>
                                </div>

                                <div class="flex items-center gap-6">
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border border-gray-200 bg-white">
                                        {{ $tm['tasks_count'] }} taken
                                    </span>

                                    <div class="w-56">
                                        <div class="flex items-center justify-between mb-2">
                                            <p class="text-[#191D38] text-sm font-black">Werkdruk:</p>
                                            <p class="text-[#191D38]/70 text-sm font-semibold">{{ $tm['workload_percent'] }}%</p>
                                        </div>

                                        <div class="w-full h-2 rounded-full bg-[#191D38]/10 overflow-hidden">
                                            <div class="h-2 rounded-full bg-[#009AC3]" style="width: {{ $tm['workload_percent'] }}%"></div>
                                        </div>

                                        <p class="text-[#191D38]/60 text-xs font-semibold mt-2">
                                            {{ $tm['workload_used_hours'] }}h / {{ $tm['workload_total_hours'] }}h
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            @endif


            {{-- QC --}}
            @if ($section === 'qc')
                <div class="flex-1 min-h-0 bg-[#191D38]/5 rounded-2xl p-8 flex items-center justify-center">
                    <p class="text-[#191D38]/70 text-sm font-semibold">Kwaliteitscontrole.</p>
                </div>
            @endif

        </div>
    </div>
@endsection
