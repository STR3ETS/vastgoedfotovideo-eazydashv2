@extends('hub.layouts.app')

@section('content')
    <style>
        /* Dunne, subtiele scrollbar zonder pijltjes */
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

        $section = request()->query('section');
        $baseUrl = url('/app/planning-management');

        $statusPill = function (?string $status) {
            $status = strtolower(trim((string) $status));

            return match ($status) {
                'new' => [
                    'label' => 'Nieuw',
                    'class' => 'text-[#2A324B] bg-[#2A324B]/20',
                ],
                'planned' => [
                    'label' => 'Ingepland',
                    'class' => 'text-[#87A878] bg-[#87A878]/20',
                ],
                'done' => [
                    'label' => 'Afgerond',
                    'class' => 'text-[#009AC3] bg-[#009AC3]/20',
                ],
                'cancelled', 'canceled' => [
                    'label' => 'Geannuleerd',
                    'class' => 'text-[#DF2935] bg-[#DF2935]/20',
                ],
                default => [
                    'label' => $status !== '' ? ucfirst($status) : 'Onbekend',
                    'class' => 'text-[#2A324B] bg-[#2A324B]/20',
                ],
            };
        };

        $slotLabel = function (?string $slot) {
            $slot = trim((string) $slot);
            if ($slot === '') return '-';

            // Normaliseer bekende formats naar "HH:MM - HH:MM"
            if (preg_match('/(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})/', $slot, $m)) {
                $start = $m[1];
                $end = $m[2];
                return $start . ' - ' . $end;
            }

            return $slot;
        };

        // TODAY
        $selectedDate = null;
        $prevDate = null;
        $nextDate = null;
        $dateLabel = null;
        $todayRequests = collect();

        if ($section === 'today' ) {
            $dateParam = request()->query('date');

            try {
                $selectedDate = $dateParam ? Carbon::parse($dateParam)->startOfDay() : now()->startOfDay();
            } catch (\Throwable $e) {
                $selectedDate = now()->startOfDay();
            }

            $prevDate = $selectedDate->copy()->subDay();
            $nextDate = $selectedDate->copy()->addDay();
            $dateLabel = $selectedDate->copy()->locale('nl')->translatedFormat('l j F Y');

            $todayRequests = \App\Models\OnboardingRequest::query()
                ->with('user')
                ->whereDate('shoot_date', $selectedDate->toDateString())
                ->orderBy('shoot_slot')
                ->orderByDesc('id')
                ->get();
        }

        // PLANNING
        $planningItems = collect();
        $filterDate = null;

        $perPage = (int) request()->query('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100], true)) $perPage = 10;

        if ($section === 'planning') {
            $filterDateParam = request()->query('date');

            try {
                $filterDate = $filterDateParam ? Carbon::parse($filterDateParam)->startOfDay() : null;
            } catch (\Throwable $e) {
                $filterDate = null;
            }

            $q = \App\Models\OnboardingRequest::query()->with('user');

            if ($filterDate) {
                $q->whereDate('shoot_date', $filterDate->toDateString());
            }

            $planningItems = $q
                ->orderByDesc('shoot_date')
                ->orderBy('shoot_slot')
                ->limit($perPage)
                ->get();
        }
    @endphp

    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-2xl h-full min-h-0 flex flex-col">

            @if (!$section)
                <div class="flex-1 min-h-0 bg-[#191D38]/5 rounded-2xl p-8 flex items-center justify-center">
                    <div class="text-center">
                        <p class="text-[#191D38] font-black text-xl mb-2">Planning & Management</p>
                        <p class="text-[#191D38]/70 text-sm font-semibold">
                            Kies links in de sidebar een onderdeel, bijvoorbeeld “Veldoverzicht vandaag” of “Planning”.
                        </p>
                    </div>
                </div>
            @endif

            {{-- TODAY --}}
            @if ($section === 'today')
                <div class="shrink-0 w-full border-b border-gray-200 pb-6 mb-6">
                    <div class="w-full flex items-center justify-between gap-6">
                        <a href="{{ $baseUrl . '?section=today&date=' . $prevDate->toDateString() }}"
                           class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <i class="fa-solid fa-chevron-left text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">
                                Vorige dag
                            </span>
                        </a>

                        <div class="text-center min-w-0">
                            <h1 class="text-[#191D38] font-black text-xl leading-tight">
                                Planningsoverzicht
                            </h1>
                            <p class="text-sm text-[#191D38]/70 font-semibold mt-1">
                                {{ ucfirst($dateLabel) }}
                            </p>
                        </div>

                        <a href="{{ $baseUrl . '?section=today&date=' . $nextDate->toDateString() }}"
                           class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">
                                Volgende dag
                            </span>
                            <i class="fa-solid fa-chevron-right text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                        </a>
                    </div>
                </div>

                <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-tl-2xl rounded-tr-2xl">
                    <div class="grid grid-cols-[0.8fr_1.6fr_1.2fr_1fr_1fr_0.7fr] items-center gap-6">
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Tijdslot</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Adres</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Contact</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Pakket</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50 text-right">Acties</p>
                    </div>
                </div>

                <div class="flex-1 min-h-0 bg-[#191D38]/5 overflow-y-auto rounded-bl-2xl rounded-br-2xl custom-scroll">
                    @if ($todayRequests->isEmpty())
                        <div class="h-full w-full flex items-center justify-center px-6">
                            <p class="text-[#191D38]/70 text-sm font-semibold">
                                Geen planningen gevonden voor deze periode.
                            </p>
                        </div>
                    @else
                        <div class="px-6 py-2 divide-y divide-[#191D38]/10">
                            @foreach ($todayRequests as $r)
                                @php
                                    $pill = $statusPill($r->status ?? null);

                                    $contactName = trim(($r->contact_first_name ?? '') . ' ' . ($r->contact_last_name ?? ''));
                                    $contactName = $contactName !== '' ? $contactName : 'Onbekend';

                                    $address = trim(($r->address ?? '') . ' ' . ($r->postcode ?? '') . ' ' . ($r->city ?? ''));
                                    $address = $address !== '' ? $address : 'Geen adres ingevuld';

                                    $slot = $slotLabel($r->shoot_slot ?? null);

                                    $package = trim((string) ($r->package ?? ''));
                                    $package = $package !== '' ? $package : '-';
                                @endphp

                                <div class="py-3 grid grid-cols-[0.8fr_1.6fr_1.2fr_1fr_1fr_0.7fr] items-center gap-6">
                                    <div class="text-[#191D38] font-semibold text-sm">
                                        {{ $slot }}
                                    </div>

                                    <div class="min-w-0">
                                        <div class="text-[#191D38] font-semibold text-sm truncate">
                                            {{ $address }}
                                        </div>
                                        <div class="text-[#191D38]/60 text-xs font-semibold mt-1 truncate">
                                            Aanvraag #{{ $r->id }}
                                            @if($r->user)
                                                <span class="ml-2 shrink-0 inline-flex items-center gap-1.5 px-2 py-[2px] rounded-full text-[10px] font-semibold bg-[#191D38]/10 text-[#191D38]/70">
                                                    {{ $r->user->name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="min-w-0">
                                        <div class="text-[#191D38] text-sm font-semibold truncate">
                                            {{ $contactName }}
                                        </div>
                                        <div class="text-[#191D38]/60 text-xs font-semibold truncate mt-1">
                                            {{ $r->contact_phone ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="text-[#191D38] text-sm font-semibold">
                                        {{ $package }}
                                    </div>

                                    <div class="{{ $pill['class'] }} text-xs font-semibold rounded-full py-1.5 text-center px-3">
                                        {{ $pill['label'] }}
                                    </div>
                                    <div class="justify-end text-[#191D38] flex items-center gap-2">
                                        <a href="{{ route('support.planning.edit', $r) }}" class="cursor-pointer" title="Bewerken">
                                            <i class="fa-solid fa-pencil hover:text-[#009AC3] transition duration-200"></i>
                                        </a>
                                        <form method="POST"
                                            action="{{ route('support.planning.destroy', $r) }}"
                                            onsubmit="return confirm('Weet je zeker dat je deze planning wilt verwijderen?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="cursor-pointer" title="Verwijderen">
                                                <i class="fa-solid fa-trash-can hover:text-[#009AC3] transition duration-200"></i>
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- PLANNING --}}
            @if ($section === 'planning')
                <div class="shrink-0 w-full mb-4 flex items-center justify-between gap-4">
                    {{-- Filter op datum --}}
                    <form method="GET" action="{{ $baseUrl }}" class="flex items-center gap-2">
                        <input type="hidden" name="section" value="planning">
                        <input type="hidden" name="per_page" value="{{ $perPage }}">

                        <label class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 cursor-pointer hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <i class="fa-solid fa-sliders text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">
                                Filter op datum
                            </span>
                            <input
                                type="date"
                                name="date"
                                value="{{ $filterDate ? $filterDate->toDateString() : '' }}"
                                class="ml-2 h-9 bg-transparent text-xs text-[#191D38] font-semibold outline-none cursor-pointer group-hover:text-white"
                                onchange="this.form.submit()"
                            >
                        </label>

                        @if($filterDate)
                            <a href="{{ $baseUrl . '?section=planning&per_page=' . $perPage }}"
                               class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#191D38] hover:border-[#191D38] group transition duration-300">
                                <i class="fa-solid fa-xmark text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                                <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">
                                    Reset
                                </span>
                            </a>
                        @endif
                    </form>

                    {{-- Rechter controls (UI klaar) --}}
                    <div class="flex items-center gap-2">
                        <button type="button"
                                class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <i class="fa-solid fa-list text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">
                                Lijstweergave
                            </span>
                            <i class="fa-solid fa-chevron-down text-[#191D38]/60 group-hover:text-white transition duration-200 text-xs"></i>
                        </button>

                        <button type="button"
                                class="px-4 h-9 border border-gray-200 bg-white rounded-full flex items-center gap-2 hover:bg-[#009AC3] hover:border-[#009AC3] group transition duration-300">
                            <i class="fa-solid fa-layer-group text-[#191D38] group-hover:text-white transition duration-200 text-sm"></i>
                            <span class="text-sm text-[#191D38] font-semibold group-hover:text-white transition duration-200">
                                Groeperen
                            </span>
                            <i class="fa-solid fa-chevron-down text-[#191D38]/60 group-hover:text-white transition duration-200 text-xs"></i>
                        </button>

                        <form method="GET" action="{{ $baseUrl }}">
                            <input type="hidden" name="section" value="planning">
                            @if($filterDate)
                                <input type="hidden" name="date" value="{{ $filterDate->toDateString() }}">
                            @endif
                            <select
                                name="per_page"
                                class="h-9 bg-white border border-gray-200 pl-4 pr-10 rounded-full text-xs text-[#191D38] font-medium outline-none appearance-none cursor-pointer"
                                onchange="this.form.submit()"
                            >
                                @foreach([10,25,50,100] as $opt)
                                    <option value="{{ $opt }}" @selected($perPage === $opt)>{{ $opt }}</option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                </div>

                {{-- Table header --}}
                <div class="shrink-0 px-6 py-4 bg-[#191D38]/10 rounded-tl-2xl rounded-tr-2xl">
                    <div class="grid grid-cols-[0.9fr_2.2fr_1.2fr_0.9fr_2.4fr_1.4fr_1.2fr_0.9fr] items-center gap-6">
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Datum</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Naam</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Fotograaf</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Dienst</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Locatie</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Klant</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Tijdslot</p>
                        <p class="text-[#191D38] font-bold text-xs opacity-50">Status</p>
                    </div>
                </div>

                {{-- Table body --}}
                <div class="flex-1 min-h-0 bg-[#191D38]/5 overflow-y-auto rounded-bl-2xl rounded-br-2xl custom-scroll">
                    @if ($planningItems->isEmpty())
                        <div class="h-full w-full flex items-center justify-center px-6">
                            <p class="text-[#191D38]/70 text-sm font-semibold">
                                Geen planningen gevonden voor deze periode.
                            </p>
                        </div>
                    @else
                        <div class="px-6 py-2 divide-y divide-[#191D38]/10">
                            @foreach ($planningItems as $r)
                                @php
                                    $pill = $statusPill($r->status ?? null);

                                    $dateText = $r->shoot_date
                                        ? Carbon::parse($r->shoot_date)->locale('nl')->translatedFormat('D j M.')
                                        : '-';

                                    $name = trim(($r->address ?? '') . ' | ' . ($r->package ?? ''));
                                    $name = trim($name, " |");
                                    if ($name === '') $name = '-';

                                    $location = trim(($r->address ?? '') . ', ' . ($r->postcode ?? '') . ' ' . ($r->city ?? ''));
                                    $location = trim($location, " ,");

                                    $client = trim(($r->contact_first_name ?? '') . ' ' . ($r->contact_last_name ?? ''));
                                    $client = $client !== '' ? $client : '-';

                                    $slot = $slotLabel($r->shoot_slot ?? null);
                                @endphp

                                <div class="py-4 grid grid-cols-[0.9fr_2.2fr_1.2fr_0.9fr_2.4fr_1.4fr_1.2fr_0.9fr] items-center gap-6">
                                    {{-- Datum --}}
                                    <div class="text-[#191D38] font-semibold text-sm">
                                        {{ $dateText }}
                                    </div>

                                    {{-- Naam --}}
                                    <div class="min-w-0">
                                        <div class="text-[#191D38] font-semibold text-sm truncate">
                                            {{ $name }}
                                        </div>
                                        <div class="text-[#191D38]/60 text-xs font-semibold mt-1 truncate">
                                            Aanvraag #{{ $r->id }}
                                        </div>
                                    </div>

                                    {{-- Fotograaf (nog niet in model) --}}
                                    <div class="min-w-0">
                                        <div class="text-[#191D38] font-semibold text-sm truncate">
                                            Nog niet toegewezen
                                        </div>
                                        <div class="text-[#191D38]/60 text-xs font-semibold mt-1 truncate">
                                            Toewijzen volgt
                                        </div>
                                    </div>

                                    {{-- Dienst --}}
                                    <div class="text-[#191D38] font-semibold text-sm">
                                        onboarding
                                    </div>

                                    {{-- Locatie --}}
                                    <div class="min-w-0">
                                        <div class="text-[#191D38] font-semibold text-sm truncate">
                                            <i class="fa-solid fa-location-dot text-[#191D38]/50 mr-2"></i>
                                            {{ $location !== '' ? $location : '-' }}
                                        </div>
                                    </div>

                                    {{-- Klant --}}
                                    <div class="text-[#191D38] font-semibold text-sm truncate">
                                        {{ $client }}
                                    </div>

                                    {{-- Tijdslot (compact + NL) --}}
                                    <div class="min-w-0">
                                        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl">
                                            <i class="fa-solid fa-clock text-[#191D38]/50 text-xs"></i>
                                            <span class="text-xs font-semibold text-[#191D38]">
                                                {{ $slot }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Status --}}
                                    <div>
                                        <span class="{{ $pill['class'] }} inline-flex items-center justify-center px-3 py-1.5 rounded-full text-xs font-semibold">
                                            {{ $pill['label'] }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endif

            {{-- QC / TEAM placeholders --}}
            @if ($section === 'qc')
                <div class="flex-1 min-h-0 bg-[#191D38]/5 rounded-2xl p-8 flex items-center justify-center">
                    <p class="text-[#191D38]/70 text-sm font-semibold">Kwaliteitscontrole komt hier.</p>
                </div>
            @endif

            @if ($section === 'team')
                <div class="flex-1 min-h-0 bg-[#191D38]/5 rounded-2xl p-8 flex items-center justify-center">
                    <p class="text-[#191D38]/70 text-sm font-semibold">Team komt hier.</p>
                </div>
            @endif

        </div>
    </div>
@endsection
