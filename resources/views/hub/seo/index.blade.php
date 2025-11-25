{{-- resources/views/hub/seo/index.blade.php --}}
@extends('hub.layouts.app')

@section('content')
    @php
        // Zorg dat we altijd iets hebben, ook als controller ooit niets meegeeft
        $filters       = $filters ?? [];
        $selectedAudit = $selectedAudit ?? null;

        // Kleine helper voor health label
        function seo_health_label(?int $score): array {
            if ($score === null) {
                return ['label' => 'Onbekend', 'class' => 'bg-gray-100 text-gray-600'];
            }

            if ($score >= 80) {
                return ['label' => 'Goed', 'class' => 'bg-emerald-50 text-emerald-700'];
            }

            if ($score >= 50) {
                return ['label' => 'Matig', 'class' => 'bg-amber-50 text-amber-700'];
            }

            return ['label' => 'Slecht', 'class' => 'bg-red-50 text-red-700'];
        }
    @endphp

    {{-- LINKERKAART: nieuwe audit starten + filters --}}
    <div class="col-span-2 p-4 h-full bg-white rounded-xl flex flex-col gap-4">
        <div class="flex items-center justify-between mb-2">
            <h1 class="text-xl text-[#215558] font-black">
                {{ __('seo.page_title') ?? 'SEO audits' }}
            </h1>
        </div>

        @if (session('status'))
            <div class="mb-2 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filters --}}
        <form method="GET"
              action="{{ route('support.seo-audit.index') }}"
              class="mb-3 grid grid-cols-2 gap-3">
            <div>
                <label class="block text-xs text-[#215558] opacity-70 mb-1">Klant</label>
                <select name="company_id"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 text-sm text-[#215558] font-medium outline-none focus:border-[#3b8b8f] transition">
                    <option value="">Alle klanten</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(($filters['company_id'] ?? null) == $company->id)>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-[#215558] opacity-70 mb-1">Status</label>
                <select name="status"
                        class="w-full py-2.5 px-3 rounded-xl border border-gray-200 text-sm text-[#215558] font-medium outline-none focus:border-[#3b8b8f] transition">
                    <option value="">Alle statussen</option>
                    <option value="pending"   @selected(($filters['status'] ?? null) === 'pending')>In wachtrij</option>
                    <option value="running"   @selected(($filters['status'] ?? null) === 'running')>Bezig</option>
                    <option value="completed" @selected(($filters['status'] ?? null) === 'completed')>Afgerond</option>
                    <option value="failed"    @selected(($filters['status'] ?? null) === 'failed')>Mislukt</option>
                </select>
            </div>

            <div class="col-span-2 flex justify-end gap-2">
                <a href="{{ route('support.seo-audit.index') }}"
                   class="px-4 py-2 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-gray-700 transition">
                    Reset
                </a>
                <button type="submit"
                        class="px-4 py-2 rounded-full bg-[#0F9B9F] hover:bg-[#215558] text-xs font-semibold text-white transition cursor-pointer">
                    Filter toepassen
                </button>
            </div>
        </form>

        <hr class="border-gray-100 my-2">

        {{-- Nieuwe audit starten --}}
        <div>
            <h2 class="text-sm font-bold text-[#215558] mb-2">Nieuwe SEO audit starten</h2>

            <form method="POST"
                  action="{{ route('support.seo-audit.store') }}"
                  class="grid grid-cols-2 gap-3">
                @csrf

                <div class="col-span-2">
                    <label class="block text-xs text-[#215558] opacity-70 mb-1">Klant</label>
                    <select name="company_id"
                            class="w-full py-2.5 px-3 rounded-xl border border-gray-200 text-sm text-[#215558] font-medium outline-none focus:border-[#3b8b8f] transition"
                            required>
                        <option value="">Selecteer klant</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}"
                                    @selected(
                                        old('company_id', $filters['company_id'] ?? null) == $company->id
                                    )>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-2">
                    <label class="block text-xs text-[#215558] opacity-70 mb-1">
                        Domein (optioneel, laat leeg om het domein uit de klant te gebruiken)
                    </label>
                    <input type="text"
                           name="domain"
                           value="{{ old('domain') }}"
                           placeholder="bijvoorbeeld: eazyonline.nl"
                           class="w-full py-2.5 px-3 rounded-xl border border-gray-200 text-sm text-[#215558] font-medium outline-none focus:border-[#3b8b8f] transition">
                    @error('domain')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-[#215558] opacity-70 mb-1">Audit type</label>
                    <select name="type"
                            class="w-full py-2.5 px-3 rounded-xl border border-gray-200 text-sm text-[#215558] font-medium outline-none focus:border-[#3b8b8f] transition"
                            required>
                        <option value="full"      @selected(old('type') === 'full')>Volledige site audit</option>
                        <option value="technical" @selected(old('type') === 'technical')>Technische audit</option>
                        <option value="keywords"  @selected(old('type') === 'keywords')>Keywords focus</option>
                        <option value="backlinks" @selected(old('type') === 'backlinks')>Backlinks focus</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-[#215558] opacity-70 mb-1">Land</label>
                    <input type="text"
                           name="country"
                           value="{{ old('country', 'NL') }}"
                           class="w-full py-2.5 px-3 rounded-xl border border-gray-200 text-sm text-[#215558] font-medium outline-none focus:border-[#3b8b8f] transition">
                    @error('country')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs text-[#215558] opacity-70 mb-1">Taal</label>
                    <input type="text"
                           name="locale"
                           value="{{ old('locale', 'nl-NL') }}"
                           class="w-full py-2.5 px-3 rounded-xl border border-gray-200 text-sm text-[#215558] font-medium outline-none focus:border-[#3b8b8f] transition">
                    @error('locale')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="col-span-2 flex justify-end mt-1">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-full bg-[#0F9B9F] hover:bg-[#215558] text-sm font-semibold text-white transition cursor-pointer">
                        Audit starten
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- RECHTERKAART: lijst met audits + geselecteerde audit samenvatting --}}
    <div class="col-span-3 p-4 h-full bg-white rounded-xl flex flex-col">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm text-[#215558] font-bold">Uitgevoerde audits</p>
            @if ($audits->total() > 0)
                <p class="text-xs text-gray-500">
                    {{ $audits->total() }} audit{{ $audits->total() === 1 ? '' : 's' }} gevonden
                </p>
            @endif
        </div>

        {{-- Samenvatting van geselecteerde audit (bijvoorbeeld na het starten van een nieuwe audit) --}}
        @if($selectedAudit)
            @php
                $score = $selectedAudit->overall_score !== null ? (int) $selectedAudit->overall_score : null;
                $health = seo_health_label($score);

                // Probeer binnen de huidige pagina de vorige audit voor dezelfde klant te vinden
                $prevAudit = $audits
                    ->where('company_id', $selectedAudit->company_id)
                    ->where('id', '!=', $selectedAudit->id)
                    ->sortByDesc('created_at')
                    ->first();

                $diff = null;
                if ($prevAudit && $score !== null && $prevAudit->overall_score !== null) {
                    $diff = $score - (int) $prevAudit->overall_score;
                }
            @endphp

            <div class="mb-4 p-3 rounded-xl border border-[#e0f4f1] bg-[#f5faf9]">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <p class="text-[11px] text-[#215558] opacity-70 mb-1">
                            Laatste audit voor
                            <span class="font-semibold">{{ $selectedAudit->company->name ?? 'Onbekende klant' }}</span>
                        </p>
                        <p class="text-xs text-[#215558] font-semibold">
                            {{ $selectedAudit->domain }}
                        </p>
                        <p class="text-[11px] text-[#215558] opacity-80">
                            {{ optional($selectedAudit->created_at)->format('d-m-Y H:i') }} &middot;
                            {{ ucfirst($selectedAudit->type) }} audit
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-[11px] text-[#215558] opacity-70">
                                SEO score
                            </p>
                            @if($score !== null)
                                <p class="text-2xl font-black text-[#215558] leading-none">
                                    {{ $score }}%
                                </p>
                            @else
                                <p class="text-sm text-gray-500">nvt</p>
                            @endif
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $health['class'] }}">
                                {{ $health['label'] }}
                            </span>
                            @if(!is_null($diff))
                                <span class="text-[10px] font-medium {{ $diff >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $diff >= 0 ? '+' : '' }}{{ $diff }} punten t.o.v. vorige audit
                                </span>
                            @endif
                            <a href="{{ route('support.seo-audit.show', $selectedAudit) }}"
                               class="mt-1 inline-flex items-center px-3 py-1 rounded-full bg-[#0F9B9F] hover:bg-[#215558] text-[10px] font-semibold text-white transition">
                                Rapport bekijken
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="flex-1 overflow-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs text-gray-500 border-b border-gray-100">
                        <th class="py-2 pr-2">Klant</th>
                        <th class="py-2 pr-2">Domein</th>
                        <th class="py-2 pr-2">Type</th>
                        <th class="py-2 pr-2">Status</th>
                        <th class="py-2 pr-2">Score</th>
                        <th class="py-2 pr-2">Datum</th>
                        <th class="py-2 pr-2 text-right"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        @php
                            $rowSelected = $selectedAudit && $selectedAudit->id === $audit->id;
                            $score = $audit->overall_score !== null ? (int) $audit->overall_score : null;
                            $health = seo_health_label($score);

                            $status = $audit->status;
                            $classes = match($status) {
                                'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'running'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                'pending'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                'failed'    => 'bg-red-50 text-red-700 border-red-200',
                                default     => 'bg-gray-50 text-gray-700 border-gray-200',
                            };
                            $label = match($status) {
                                'completed' => 'Afgerond',
                                'running'   => 'Bezig',
                                'pending'   => 'In wachtrij',
                                'failed'    => 'Mislukt',
                                default     => ucfirst($status),
                            };

                            $scoreColor = $score === null
                                ? 'text-gray-400'
                                : ($score >= 80
                                    ? 'text-emerald-600'
                                    : ($score >= 50 ? 'text-amber-600' : 'text-red-600'));
                        @endphp
                        <tr class="border-b border-gray-50 transition {{ $rowSelected ? 'bg-[#f5faf9]' : 'hover:bg-gray-50/60' }}">
                            <td class="py-2 pr-2 align-top">
                                <p class="text-xs text-[#215558] font-semibold">
                                    {{ $audit->company->name ?? 'Onbekende klant' }}
                                </p>
                            </td>
                            <td class="py-2 pr-2 align-top">
                                <p class="text-xs text-gray-700 font-medium">
                                    {{ $audit->domain }}
                                </p>
                            </td>
                            <td class="py-2 pr-2 align-top">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-100 text-[10px] font-semibold text-gray-700">
                                    {{ ucfirst($audit->type) }}
                                </span>
                            </td>
                            <td class="py-2 pr-2 align-top">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-semibold {{ $classes }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="py-2 pr-2 align-top">
                                <div class="flex flex-col gap-0.5">
                                    @if ($score !== null)
                                        <span class="text-xs font-bold {{ $scoreColor }}">{{ $score }}%</span>
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] {{ $health['class'] }}">
                                            {{ $health['label'] }}
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-400">nvt</span>
                                    @endif
                                </div>
                            </td>
                            <td class="py-2 pr-2 align-top">
                                <span class="text-xs text-gray-500">
                                    {{ optional($audit->created_at)->format('d-m-Y H:i') }}
                                </span>
                            </td>
                            <td class="py-2 pl-2 align-top text-right">
                                <a href="{{ route('support.seo-audit.show', $audit) }}"
                                   class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 hover:bg-gray-200 text-xs font-semibold text-[#215558] transition">
                                    Rapport bekijken
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-xs text-gray-500">
                                Er zijn nog geen audits uitgevoerd.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $audits->links() }}
        </div>
    </div>
@endsection
