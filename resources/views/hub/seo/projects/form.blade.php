@extends('hub.layouts.app')

@section('content')
    <div class="col-span-5 flex-1 min-h-0">
        <div class="w-full p-8 bg-white border border-gray-200 rounded-4xl h-full min-h-0">
            <div class="h-full min-h-0 overflow-y-auto pr-3">
                {{-- Header --}}
                <div class="w-full flex items-center justify-between gap-2 min-w-0 mb-6">
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500 mb-1">
                            SEO projecten
                        </p>
                        <h3 class="text-base text-[#215558] font-bold leading-tight truncate">
                            {{ $isEdit ? 'SEO project bewerken' : 'Nieuw SEO project' }}
                        </h3>
                        <p class="text-xs text-gray-500 mt-1">
                            Koppel een klant, domein en doelen zodat je SEO traject duidelijk is voor iedereen.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('support.seo.projects.index') }}"
                           class="px-4 py-2 text-xs font-semibold rounded-full border border-gray-200 text-[#215558] bg-white hover:bg-gray-100 transition">
                            Terug naar overzicht
                        </a>
                        @if($isEdit)
                            <a href="{{ route('support.seo.projects.show', $project) }}"
                               class="px-4 py-2 text-xs font-semibold rounded-full bg-[#f3f8f8] text-[#215558] hover:bg-[#e5f1f1] transition">
                                Bekijk project
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Status melding --}}
                @if (session('status'))
                    <div class="mb-4 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-200 text-[11px] text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 px-4 py-3 rounded-2xl bg-red-50 border border-red-200 text-[11px] text-red-700">
                        <p class="font-semibold mb-1">Er ging iets mis bij het opslaan.</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php
                    $formAction = $isEdit
                        ? route('support.seo.projects.update', $project)
                        : route('support.seo.projects.store');
                    $formMethod = $isEdit ? 'PATCH' : 'POST';
                @endphp

                <form method="POST" action="{{ $formAction }}" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    @csrf
                    @if($isEdit)
                        @method('PATCH')
                    @endif

                    {{-- Linker kolom: basisgegevens --}}
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-[#f3f8f8] rounded-4xl p-6">
                            <h4 class="text-sm font-bold text-[#215558] mb-4">
                                Basisgegevens
                            </h4>

                            {{-- Company --}}
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-[#215558] mb-1">
                                    Klant / bedrijf
                                    <span class="text-red-500">*</span>
                                </label>
                                <select
                                    name="company_id"
                                    class="w-full px-3 py-2 rounded-2xl border text-xs font-semibold text-[#215558] border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition"
                                >
                                    <option value="">Maak een keuze...</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}"
                                            @selected(old('company_id', $project->company_id) == $company->id)>
                                            {{ $company->name ?? ('Bedrijf #' . $company->id) }}
                                            @if($company->website)
                                                ({{ $company->website }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Projectnaam --}}
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-[#215558] mb-1">
                                    Naam van het traject (optioneel)
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name', $project->name) }}"
                                    placeholder="Bijvoorbeeld: Top Woning Ontruiming SEO traject"
                                    class="w-full px-3 py-2 rounded-2xl border text-xs font-semibold text-[#215558] border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition"
                                >
                            </div>

                            {{-- Domein --}}
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-[#215558] mb-1">
                                    Primair domein
                                    <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="domain"
                                    value="{{ old('domain', $project->domain) }}"
                                    placeholder="bijvoorbeeld: www.top-woningontruimingen.nl"
                                    class="w-full px-3 py-2 rounded-2xl border text-xs font-semibold text-[#215558] border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition"
                                >
                                <p class="text-[10px] text-gray-500 mt-1">
                                    Vul het hoofd-domein in dat je wilt monitoren en optimaliseren.
                                </p>
                            </div>

                            {{-- SERanking project id --}}
                            <div class="mb-2">
                                <label class="block text-xs font-semibold text-[#215558] mb-1">
                                    SERanking project ID (optioneel)
                                </label>
                                <input
                                    type="text"
                                    name="seranking_project_id"
                                    value="{{ old('seranking_project_id', $project->seranking_project_id) }}"
                                    placeholder="Bijvoorbeeld: 123456"
                                    class="w-full px-3 py-2 rounded-2xl border text-xs font-semibold text-[#215558] border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition"
                                >
                                <p class="text-[10px] text-gray-500 mt-1">
                                    Als deze gekoppeld is, kan de tool straks automatisch data uit SERanking ophalen.
                                </p>
                            </div>
                        </div>

                        {{-- Doelen en focus --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-6">
                            <h4 class="text-sm font-bold text-[#215558] mb-4">
                                Doelen en focus
                            </h4>

                            {{-- Regio's --}}
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-[#215558] mb-1">
                                    Belangrijkste regio's
                                </label>
                                <textarea
                                    name="regions_text"
                                    rows="2"
                                    placeholder="Eén per regel, bijvoorbeeld:&#10;Arnhem&#10;Gelderland"
                                    class="w-full px-3 py-2 rounded-2xl border text-xs font-semibold text-[#215558] border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition resize-none"
                                >{{ old('regions_text', $regionsText) }}</textarea>
                                <p class="text-[10px] text-gray-500 mt-1">
                                    Gebruik één regio per regel, handig voor lokale SEO.
                                </p>
                            </div>

                            {{-- Business doelen --}}
                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-[#215558] mb-1">
                                    Zakelijke doelen
                                </label>
                                <textarea
                                    name="business_goals_text"
                                    rows="3"
                                    placeholder="Eén doel per regel, bijvoorbeeld:&#10;Meer aanvragen voor woningontruiming in regio Arnhem&#10;Beter zichtbaar worden op merknaam"
                                    class="w-full px-3 py-2 rounded-2xl border text-xs font-semibold text-[#215558] border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition resize-none"
                                >{{ old('business_goals_text', $businessGoalsText) }}</textarea>
                                <p class="text-[10px] text-gray-500 mt-1">
                                    Dit zijn de echte bedrijfsdoelen, zodat iedereen weet waar je naartoe werkt.
                                </p>
                            </div>

                            {{-- Belangrijkste zoekwoorden --}}
                            <div class="mb-2">
                                <label class="block text-xs font-semibold text-[#215558] mb-1">
                                    Belangrijkste zoekwoorden
                                </label>
                                <textarea
                                    name="primary_keywords_text"
                                    rows="3"
                                    placeholder="Eén zoekwoord per regel, bijvoorbeeld:&#10;woningontruiming arnhem&#10;woning ontruimen gelderland"
                                    class="w-full px-3 py-2 rounded-2xl border text-xs font-semibold text-[#215558] border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition resize-none"
                                >{{ old('primary_keywords_text', $primaryKeywordsText) }}</textarea>
                                <p class="text-[10px] text-gray-500 mt-1">
                                    Deze lijst gebruiken we later om MCP prompts, keyword gaps en quick wins op te baseren.
                                </p>
                            </div>
                        </div>

                        {{-- Belangrijkste pagina's --}}
                        <div class="bg-[#f3f8f8] rounded-4xl p-6">
                            <h4 class="text-sm font-bold text-[#215558] mb-4">
                                Belangrijkste pagina's
                            </h4>

                            <div class="mb-4">
                                <label class="block text-xs font-semibold text-[#215558] mb-1">
                                    Pagina's die echt belangrijk zijn
                                </label>
                                <textarea
                                    name="main_pages_text"
                                    rows="4"
                                    placeholder="/woningontruiming-arnhem | Woningontruiming Arnhem&#10;/woningontruiming-nijmegen | Woningontruiming Nijmegen"
                                    class="w-full px-3 py-2 rounded-2xl border text-xs font-semibold text-[#215558] border-gray-200 bg-white outline-none focus:border-[#3b8b8f] transition resize-none"
                                >{{ old('main_pages_text', $mainPagesText) }}</textarea>
                                <p class="text-[10px] text-gray-500 mt-1">
                                    Gebruik per regel het formaat: <span class="font-semibold">/pad | Label</span>.  
                                    Bijvoorbeeld: <code class="text-[10px] bg-white px-1 py-0.5 rounded">/diensten/woningontruiming | Woningontruiming</code>.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Rechter kolom: samenvatting en opslaan --}}
                    <div class="space-y-4">
                        <div class="bg-[#f3f8f8] rounded-4xl p-6">
                            <h4 class="text-sm font-bold text-[#215558] mb-4">
                                Samenvatting
                            </h4>

                            <div class="space-y-3 text-xs text-[#215558] font-semibold">
                                <div class="flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center">
                                        <i class="fa-solid fa-building text-[11px] text-[#215558]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-gray-500 mb-0.5">
                                            Klant
                                        </p>
                                        <p>
                                            @php
                                                $companySelected = $companies->firstWhere('id', old('company_id', $project->company_id));
                                            @endphp
                                            @if($companySelected)
                                                {{ $companySelected->name ?? ('Bedrijf #' . $companySelected->id) }}
                                            @else
                                                Nog geen klant gekozen
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center">
                                        <i class="fa-solid fa-globe text-[11px] text-[#215558]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-gray-500 mb-0.5">
                                            Domein
                                        </p>
                                        <p>
                                            {{ old('domain', $project->domain) ?: 'Nog geen domein ingevuld' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center">
                                        <i class="fa-solid fa-location-dot text-[11px] text-[#215558]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-gray-500 mb-0.5">
                                            Regio's
                                        </p>
                                        @if(trim(old('regions_text', $regionsText)) !== '')
                                            <ul class="list-disc list-inside space-y-0.5">
                                                @foreach(explode("\n", trim(old('regions_text', $regionsText))) as $line)
                                                    @if(trim($line) !== '')
                                                        <li>{{ trim($line) }}</li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @else
                                            <p>Geen specifieke regio's ingesteld.</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-start gap-2">
                                    <div class="w-6 h-6 rounded-full bg-white flex items-center justify-center">
                                        <i class="fa-solid fa-key text-[11px] text-[#215558]"></i>
                                    </div>
                                    <div>
                                        <p class="text-[11px] text-gray-500 mb-0.5">
                                            Belangrijkste zoekwoorden
                                        </p>
                                        @if(trim(old('primary_keywords_text', $primaryKeywordsText)) !== '')
                                            <ul class="list-disc list-inside space-y-0.5">
                                                @foreach(explode("\n", trim(old('primary_keywords_text', $primaryKeywordsText))) as $line)
                                                    @if(trim($line) !== '')
                                                        <li>{{ trim($line) }}</li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @else
                                            <p>Nog geen zoekwoorden ingevuld.</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-[#f3f8f8] rounded-4xl p-6">
                            <h4 class="text-sm font-bold text-[#215558] mb-3">
                                Opslaan
                            </h4>
                            <p class="text-[11px] text-gray-500 mb-4">
                                Dit maakt of werkt het SEO project bij. Later koppelen we hier MCP prompts,
                                SERanking data en concrete taken aan.
                            </p>

                            <button
                                type="submit"
                                class="w-full px-4 py-2.5 rounded-full text-xs font-semibold text-white bg-[#0F9B9F] hover:bg-[#215558] transition"
                            >
                                {{ $isEdit ? 'Wijzigingen opslaan' : 'SEO project aanmaken' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
