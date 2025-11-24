@extends('hub.projecten.offerte.layouts.guest')

@section('content')

@php
    /** @var \App\Models\Offerte $offerte */
    $offerteDate   = $offerte->created_at ?? now();  // offertedatum
    $vervalDatum   = $offerteDate->copy()->addMonthNoOverflow(); // +1 maand
    $offerteNummer = $offerte->number
        ?? ('OF-' . $offerteDate->format('Ym') . str_pad($offerte->id ?? 1, 4, '0', STR_PAD_LEFT));

    $overrides = $offerte->content_overrides ?? [];

    $headline = data_get($overrides, 'headline')
        ?? data_get(
            $offerte->generated,
            'headline',
            'Website & online groei voor ' . ($project->company ?? 'jouw bedrijf')
        );
    
    $summaryParagraph = data_get($overrides, 'summary_paragraph')
        ?? data_get($offerte->generated, 'summary_paragraph');

    $summaryBullets = data_get($overrides, 'summary_bullets')
        ?? data_get($offerte->generated, 'summary_bullets', []);

    $strongPoints = data_get($overrides, 'strong_points')
        ?? data_get($offerte->generated, 'strong_points', []);

    $improvementPoints = data_get($overrides, 'improvement_points')
        ?? data_get($offerte->generated, 'improvement_points', []);

    $scopeIntro = data_get($overrides, 'scope_intro')
        ?? 'In deze offerte leveren we een complete online oplossing waarmee jullie merk professioneel, snel en schaalbaar online kan groeien. Hieronder de belangrijkste onderdelen op een rij.';

    $scopeItems = data_get($overrides, 'scope_items')
        ?? data_get($offerte->generated, 'scope_items', [
            'Volledig maatwerk ontwerp afgestemd op jullie merk, doelgroep en positionering.',
            'Conversiegerichte pagina’s (bijvoorbeeld: Home, Diensten, Over ons, Contact).',
            'Technische basis voor SEO (laadsnelheid, structuur, metadata, basis redirects).',
            'Koppelingen met belangrijke tools (bijvoorbeeld: betaalprovider, e-mailmarketing, statistieken).',
            'Gebruiksvriendelijk beheer zodat jullie zelf content, producten en pagina’s kunnen beheren.',
            'Begeleiding bij livegang en korte training in het gebruik van de omgeving.',
    ]);

    $goalsIntro = data_get($overrides, 'goals_intro')
        ?? 'We denken niet alleen in pixels, maar vooral in resultaat. Samen bepalen we concrete doelen en sturen we op meetbare KPI’s.';

    $goalsItems = data_get($overrides, 'goals_items')
        ?? data_get($offerte->generated, 'goals_items', [
            'Meer relevante bezoekers via organische zoekresultaten (SEO).',
            'Stijging in conversies (aanvragen, bestellingen of afspraken).',
            'Betere inzichtelijkheid in prestaties via duidelijke rapportages en dashboards.',
            'Kortere doorlooptijd van eerste bezoek tot klant.',
    ]);

    $approachPhases = data_get($overrides, 'approach_phases', [
        [
            'title' => 'Fase 1 – Strategie & kick-off',
            'text'  => 'Gezamenlijke sessie(s) om doelen, doelgroep, positionering en functionaliteiten scherp te krijgen. We vertalen dit naar een concreet plan van aanpak.',
        ],
        [
            'title' => 'Fase 2 – Design & concept',
            'text'  => 'Uitwerking van het visuele ontwerp (desktop & mobiel), inclusief feedbackronde(s). Na akkoord zetten we het design door naar de bouw.',
        ],
        [
            'title' => 'Fase 3 – Bouw & inrichting',
            'text'  => 'Technische realisatie, contentinvoer en koppelingen (betaalprovider, formulieren, tracking). We leveren een testomgeving op om samen door te lopen.',
        ],
        [
            'title' => 'Fase 4 – Testen, livegang & nazorg',
            'text'  => 'Laatste checks, livegang en overdracht. Eventuele puntjes op de i verwerken we na livegang in overleg.',
        ],
    ]);

    $investment = data_get($overrides, 'investment')
        ?? data_get($offerte->generated, 'investment', []);
@endphp
<style>
    .js-inline-edit {
        cursor: pointer;
        /* white-space: pre-line; */
        text-decoration: none;
    }
</style>
@php
    $isSent = !is_null($offerte->sent_at);
@endphp
<div class="w-full fixed z-50 top-0 left-0 bg-white border-b border-b-gray-200 p-4 min-h-[61px] flex items-center">
    <div class="max-w-6xl w-full mx-auto flex items-center justify-between gap-2">
        <div class="flex items-center gap-4 relative">
            <a href="#"
            id="regenerate-offerte-button"
            class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer">
                <i class="fa-solid fa-arrow-rotate-right text-[#215558] text-xs"></i>
                <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-full top-1/2 ml-2 -translate-y-1/2 opacity-0 invisible translate-x-1 pointer-events-none group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto transition-all duration-200 ease-out z-10">
                    <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                        Offerte opnieuw genereren
                    </p>
                </div>
            </a>
        </div>
        <div class="flex items-center gap-2">
            <p
                class="px-2 py-0.5 text-xs font-semibold rounded-full w-fit {{ $isSent ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}"
                data-offerte-edit-status
            >
                {{ $isSent
                    ? 'Offerte is opgestuurd naar de klant'
                    : 'Je bent de offerte aan het bewerken'
                }}
            </p>
        </div>
    </div>
</div>

<div class="w-full px-6 mt-[88px] mb-[88px]">
    <div class="max-w-6xl mx-auto grid grid-cols-3 gap-10">
        <div class="col-span-3 grid grid-cols-3 gap-6">
            {{-- HOOFDKADER --}}
            <div class="col-span-2 bg-white rounded-2xl p-6 border border-gray-200 grid gap-6 overflow-hidden">
                <div class="flex items-center justify-between">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Offerte</p>
                    <h2 class="text-xl font-bold">
                        <span class="text-[#21c2d3] leading-tight">eazy</span><span class="text-[#19203c] relative">online</span>
                    </h2>
                </div>

                <h1
                    class="js-inline-edit text-2xl -mt-2 text-[#215558] font-black leading-tight shrink-0 max-w-[75%] break-all p-4 bg-gray-100 transition rounded-2xl"
                    contenteditable="true"
                    spellcheck="false"
                    data-inline-key="headline"
                >
                    {{ $headline }}
                </h1>

                <div class="flex items-start justify-between">
                    <div class="grid gap-4">
                        <ul class="text-xs grid gap-1">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Offertenummer</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">
                                    {{ $offerteNummer }}
                                </p>
                            </li>
                        </ul>
                        <ul class="text-xs grid gap-1">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Offertedatum</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">
                                    {{ $offerteDate->format('d/m/Y') }}
                                </p>
                            </li>
                        </ul>
                        <ul class="text-xs grid gap-1">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Vervaldatum</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">
                                    {{ $vervalDatum->format('d/m/Y') }}
                                </p>
                            </li>
                        </ul>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <ul class="text-xs grid gap-1 h-fit">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">eazyonline</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">Mercatorweg 28</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">6827 DC Arnhem</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">KVK: 67228550</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">BTW: NL864926856B01</p>
                            </li>
                        </ul>

                        <ul class="text-xs grid gap-1 h-fit">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Offerte voor</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">Eazyonline</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">Mercatorweg 28</p>
                            </li>
                            <li>
                                <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">6827 DC Arnhem</p>
                            </li>
                        </ul>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Samenvatting --}}
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">
                        Samenvatting van het voorstel
                    </p>
                    <p
                        class="js-inline-edit text-xs text-[#215558] font-semibold leading-tight max-w-[75%] break-all p-4 bg-gray-100 transition rounded-2xl shrink-0"
                        contenteditable="true"
                        spellcheck="false"
                        data-inline-key="summary_paragraph"
                    >
                        {{ $summaryParagraph }}
                    </p>
                    @if(!empty($summaryBullets))
                        <div class="grid grid-cols-2 gap-2" id="summary-bullets-list">
                            @foreach($summaryBullets as $idx => $bullet)
                                <span
                                    class="w-full px-2.5 py-1 text-xs bg-[#215558]/20 text-[#215558] font-semibold rounded-xl flex items-center gap-2 leading-tighter"
                                    data-summary-bullet-item
                                >
                                    <i class="fa-solid fa-check fa-xs mt-0.5"></i>
                                    <span
                                        class="js-inline-edit min-w-[293px] max-w-[293px] p-2 break-all bg-gray-100 transition rounded-xl"
                                        contenteditable="true"
                                        spellcheck="false"
                                        data-inline-key="summary_bullets.{{ $idx }}"
                                    >
                                        {{ $bullet }}
                                    </span>
                                    <button
                                        type="button"
                                        class="text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer mt-0.5"
                                        data-delete-summary-bullet
                                    >
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                </span>
                            @endforeach
                        </div>
                        {{-- + knop om een nieuwe samenvattings-bullet toe te voegen --}}
                        <button
                            type="button"
                            class="text-start text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer -mt-2"
                            data-add-summary-bullet
                        >
                            <span>Samenvattingspunt toevoegen</span>
                        </button>
                    @endif
                </div>

                <hr class="border-gray-200">

                {{-- Over Eazyonline + Reviews --}}
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Over Eazyonline</p>
                    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                        We helpen ondernemers groeien met resultaatgerichte websites en online marketing. Met meer dan 300 projecten voor o.a. AfroBros, The Grind en Face Experts weten we wat werkt voor conversiegerichte webshops.
                    </p>

                    <div class="flex items-center gap-2">
                        <a href="#" class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center w-fit text-white text-sm font-semibold px-4 py-1.5 rounded-full transition duration-300">
                            Bekijk ons portfolio
                        </a>
                    </div>

                    {{-- Reviews slider --}}
                    <div>
                        <div
                            x-data="reviewsCarousel()"
                            x-init="start()"
                            class="relative"
                            x-on:mouseenter="pause()"
                            x-on:mouseleave="start()"
                        >
                            <div class="overflow-hidden rounded-2xl">
                                <div
                                    x-ref="track"
                                    class="flex transition-transform duration-700 ease-in-out"
                                    :style="translateStyle()"
                                >
                                    {{-- FRAME 1 --}}
                                    <div class="shrink-0 basis-full px-1">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            {{-- Review 1 --}}
                                            <div class="bg-white h-full rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                                <div class="flex items-center gap-1">
                                                    @for ($i = 0; $i < 5; $i++)
                                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                                    @endfor
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <div class="w-8 h-8 rounded-full bg-cover bg-center"
                                                         style="background-image: url('/assets/eazyonline/projecten/profielfotos/2befit.webp')"></div>
                                                    <div>
                                                        <p class="text-sm text-[#215558] font-bold">Roy Koenders</p>
                                                        <p class="text-xs text-[#215558] font-semibold">Eigenaar 2BeFit Coaching</p>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-[#215558] font-medium">
                                                    Van idee tot eindproduct: Eazy leverde een strak, modern en uniek design dat onze visie perfect weerspiegelt.
                                                </p>
                                            </div>

                                            {{-- Review 2 --}}
                                            <div class="bg-white h-full rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                                <div class="flex items-center gap-1">
                                                    @for ($i = 0; $i < 5; $i++)
                                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                                    @endfor
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <div class="w-8 h-8 rounded-full bg-cover bg-center"
                                                         style="background-image: url('/assets/eazyonline/projecten/profielfotos/barbarosdetailing.webp')"></div>
                                                    <div>
                                                        <p class="text-sm text-[#215558] font-bold">Baris Yildirim</p>
                                                        <p class="text-xs text-[#215558] font-semibold">Eigenaar Babaros Detailing</p>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-[#215558] font-medium">
                                                    Binnen no-time hadden we een op maat gemaakte website die precies laat zien waar ons bedrijf voor staat. Supertevreden.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FRAME 2 --}}
                                    <div class="shrink-0 basis-full px-1">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            {{-- Review 3 --}}
                                            <div class="bg-white h-full rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                                <div class="flex items-center gap-1">
                                                    @for ($i = 0; $i < 5; $i++)
                                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                                    @endfor
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <div class="w-8 h-8 rounded-full bg-cover bg-center"
                                                         style="background-image: url('/assets/eazyonline/projecten/profielfotos/thegrind.webp')"></div>
                                                    <div>
                                                        <p class="text-sm text-[#215558] font-bold">Donny Roelvink</p>
                                                        <p class="text-xs text-[#215558] font-semibold">Eigenaar The Grind</p>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-[#215558] font-medium">
                                                    Eazy heeft elke versie van onze website naar een hoger niveau getild. Ze snappen exact wat je als ondernemer nodig hebt.
                                                </p>
                                            </div>

                                            {{-- Review 4 --}}
                                            <div class="bg-white h-full rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                                <div class="flex items-center gap-1">
                                                    @for ($i = 0; $i < 5; $i++)
                                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                                    @endfor
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <div class="w-8 h-8 rounded-full bg-cover bg-center"
                                                         style="background-image: url('/assets/eazyonline/projecten/profielfotos/kapotsterk.webp')"></div>
                                                    <div>
                                                        <p class="text-sm text-[#215558] font-bold">Wouter Smith</p>
                                                        <p class="text-xs text-[#215558] font-semibold">Eigenaar KapotSterk</p>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-[#215558] font-medium">
                                                    Samenwerken met Eazy voelt als een gedeeld avontuur. Ze denken altijd mee en bouwen écht mee aan ons merk.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- FRAME 3 --}}
                                    <div class="shrink-0 basis-full px-1">
                                        <div class="grid gap-4 md:grid-cols-2">
                                            {{-- Review 5 --}}
                                            <div class="bg-white h-full rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                                <div class="flex items-center gap-1">
                                                    @for ($i = 0; $i < 5; $i++)
                                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                                    @endfor
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <div class="w-8 h-8 rounded-full bg-cover bg-center"
                                                         style="background-image: url('/assets/eazyonline/projecten/profielfotos/huisjekaatsheuvel.webp')"></div>
                                                    <div>
                                                        <p class="text-sm text-[#215558] font-bold">Nienke Roseboom</p>
                                                        <p class="text-xs text-[#215558] font-semibold">Eigenaresse Huisje Kaatsheuvel</p>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-[#215558] font-medium">
                                                    Vanaf dag één goede communicatie, snelle updates en een team dat je écht meeneemt in het proces. Heel professioneel.
                                                </p>
                                            </div>

                                            {{-- Review 6 --}}
                                            <div class="bg-white h-full rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                                                <div class="flex items-center gap-1">
                                                    @for ($i = 0; $i < 5; $i++)
                                                        <i class="fa-solid fa-star fa-xs text-amber-500"></i>
                                                    @endfor
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <div class="w-8 h-8 rounded-full bg-cover bg-center"
                                                         style="background-image: url('/assets/eazyonline/projecten/profielfotos/blowertechnic.webp')"></div>
                                                    <div>
                                                        <p class="text-sm text-[#215558] font-bold">Bas &amp; David</p>
                                                        <p class="text-xs text-[#215558] font-semibold">Eigenaren BlowerTechnic</p>
                                                    </div>
                                                </div>
                                                <p class="text-xs text-[#215558] font-medium">
                                                    Onze oude websites voldeden niet meer aan onze visie. Eazy ontwikkelde een volledig nieuw concept dat onze verwachtingen overtrof.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Analyse & Uitdagingen</p>
                    <div class="grid grid-cols-2 gap-4">
                        <ul class="text-xs grid gap-2 h-fit" id="strong-points-list">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Sterke punten</p>
                            </li>

                            @foreach($strongPoints as $idx => $point)
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                                    <p
                                        class="js-inline-edit min-w-[305px] max-w-[305px] text-xs break-all text-[#215558] font-semibold leading-tight p-2 bg-gray-100 transition rounded-xl"
                                        contenteditable="true"
                                        spellcheck="false"
                                        data-inline-key="strong_points.{{ $idx }}"
                                    >
                                        {{ $point }}
                                    </p>
                                    <button
                                        type="button"
                                        class="ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer mt-0.5"
                                        data-delete-strong-point
                                    >
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                </li>
                            @endforeach

                            {{-- + knop om een nieuw sterk punt toe te voegen --}}
                            <li>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer"
                                    data-add-strong-point
                                >
                                    <span>Sterk punt toevoegen</span>
                                </button>
                            </li>
                        </ul>
                        <ul class="text-xs grid gap-2 h-fit" id="improvement-points-list">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate">Verbeterpunten</p>
                            </li>

                            @foreach($improvementPoints as $idx => $point)
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                                    <p
                                        class="js-inline-edit min-w-[305px] max-w-[305px] text-xs break-all text-[#215558] font-semibold leading-tight p-2 bg-gray-100 transition rounded-xl"
                                        contenteditable="true"
                                        spellcheck="false"
                                        data-inline-key="improvement_points.{{ $idx }}"
                                    >
                                        {{ $point }}
                                    </p>
                                    <button
                                        type="button"
                                        class="ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer mt-0.5"
                                        data-delete-improvement-point
                                    >
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                </li>
                            @endforeach

                            {{-- + knop om een nieuw verbeterpunt toe te voegen --}}
                            <li>
                                <button
                                    type="button"
                                    class="flex items-center gap-2 text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer"
                                    data-add-improvement-point
                                >
                                    <span>Verbeterpunt toevoegen</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Scope & deliverables --}}
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Wat je van ons krijgt</p>
                    <p
                        class="js-inline-edit text-xs text-[#215558] font-semibold leading-tight max-w-[75%] p-4 bg-gray-100 transition rounded-2xl shrink-0"
                        contenteditable="true"
                        spellcheck="false"
                        data-inline-key="scope_intro"
                    >
                        {{ $scopeIntro }}
                    </p>
                    <div class="flex flex-col gap-2" id="scope-items-list">
                        @foreach($scopeItems as $idx => $item)
                            <div class="flex items-center justify-between" data-scope-item>
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                                    <p
                                        class="js-inline-edit min-w-[530px] max-w-[530px] break-all text-xs text-[#215558] font-semibold p-2 bg-gray-100 transition rounded-xl leading-tight"
                                        contenteditable="true"
                                        spellcheck="false"
                                        data-inline-key="scope_items.{{ $idx }}"
                                    >
                                        {{ $item }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">
                                        Inbegrepen
                                    </div>
                                    <button
                                        type="button"
                                        class="ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer mt-0.5"
                                        data-delete-scope-item
                                    >
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach

                        {{-- + knop om een nieuw scope-onderdeel toe te voegen --}}
                        <button
                            type="button"
                            class="text-start text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer"
                            data-add-scope-item
                        >
                            <span>Scopepunt toevoegen</span>
                        </button>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Doelen & KPI's --}}
                @php
                    $goalsItemsLeft  = array_slice($goalsItems, 0, 2);
                    $goalsItemsRight = array_slice($goalsItems, 2);
                @endphp
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Doelen & KPI's</p>
                    <p
                        class="js-inline-edit text-xs text-[#215558] font-semibold leading-tight max-w-[75%] p-4 bg-gray-100 transition rounded-2xl shrink-0"
                        contenteditable="true"
                        spellcheck="false"
                        data-inline-key="goals_intro"
                    >
                        {{ $goalsIntro }}
                    </p>
                    <div class="text-xs">
                        {{-- Lijst met doelen/KPI's --}}
                        <ul class="grid gap-2" id="goals-items-list">
                            @foreach($goalsItems as $idx => $item)
                                <li class="w-full flex items-center gap-2 h-fit" data-goal-item>
                                    <i class="fa-solid fa-bullseye fa-xs mt-0.5 text-[#215558]"></i>
                                    <p
                                        class="js-inline-edit min-w-[500px] max-w-[500px] break-all text-xs text-[#215558] font-semibold leading-tight p-2 bg-gray-100 transition rounded-xl"
                                        contenteditable="true"
                                        spellcheck="false"
                                        data-inline-key="goals_items.{{ $idx }}"
                                    >
                                        {{ $item }}
                                    </p>
                                    <button
                                        type="button"
                                        class="ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer mt-0.5"
                                        data-delete-goal-item
                                    >
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        {{-- + knop om nieuw doel/KPI toe te voegen --}}
                        <button
                            type="button"
                            class="mt-2 text-start text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer"
                            data-add-goal-item
                        >
                            <span>Doel of KPI toevoegen</span>
                        </button>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Aanpak & planning --}}
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Aanpak & planning</p>
                    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                        We werken met een duidelijke, voorspelbare aanpak. Zo weet je precies wat je wanneer kunt verwachten en welke input we op welk moment nodig hebben.
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        @foreach($approachPhases as $idx => $phase)
                            <div class="text-xs grid gap-2 h-fit">
                                <p
                                    class="js-inline-edit text-sm text-[#215558] font-black leading-tight truncate p-2 bg-gray-100 transition rounded-xl shrink-0"
                                    contenteditable="true"
                                    spellcheck="false"
                                    data-inline-key="approach_phases.{{ $idx }}.title"
                                >
                                    {{ data_get($phase, 'title') }}
                                </p>

                                <p
                                    class="js-inline-edit break-all text-xs text-[#215558] font-semibold p-2 bg-gray-100 transition rounded-xl leading-tight"
                                    contenteditable="true"
                                    spellcheck="false"
                                    data-inline-key="approach_phases.{{ $idx }}.text"
                                >
                                    {{ data_get($phase, 'text') }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Pagina-structuur --}}
                @php
                    $pageStructure = array_key_exists('page_structure', $overrides ?? [])
                        ? $overrides['page_structure']
                        : data_get($offerte->generated, 'page_structure', []);

                    $pageStructurePages   = data_get($pageStructure, 'pages', []);
                    $pageStructureSummary = data_get($pageStructure, 'summary');
                @endphp

                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">
                        Voorstel paginastructuur
                    </p>

                    {{-- Samenvattende tekst boven de pagina’s --}}
                    <p
                        class="js-inline-edit text-xs text-[#215558] font-semibold leading-tight max-w-[75%] p-4 bg-gray-100 transition rounded-2xl shrink-0"
                        contenteditable="true"
                        spellcheck="false"
                        data-inline-key="page_structure.summary"
                    >
                        {{ $pageStructureSummary ?? '' }}
                    </p>

                    <div class="grid grid-cols-1 gap-4" id="page-structure-pages-list">
                        @foreach($pageStructurePages as $pageIndex => $page)
                            <div
                                class="border border-gray-200 rounded-2xl p-4 text-xs grid gap-4"
                                data-page-structure-page
                                data-page-index="{{ $pageIndex }}"
                            >
                                <div class="w-full flex items-start justify-between gap-2">
                                    <div class="w-full flex flex-col gap-2">
                                        <p
                                            class="js-inline-edit text-sm text-[#215558] font-black leading-tight truncate p-2 bg-gray-100 transition rounded-xl shrink-0"
                                            contenteditable="true"
                                            spellcheck="false"
                                            data-inline-key="page_structure.pages.{{ $pageIndex }}.title"
                                        >
                                            {{ data_get($page, 'title') }}
                                        </p>

                                        <span
                                            class="js-inline-edit text-xs text-[#215558] font-semibold leading-tight max-w-[75%] p-2 bg-gray-100 transition rounded-xl shrink-0 break-all"
                                            contenteditable="true"
                                            spellcheck="false"
                                            data-inline-key="page_structure.pages.{{ $pageIndex }}.goal"
                                        >
                                            {{ data_get($page, 'goal') }}
                                        </span>
                                    </div>

                                    <button
                                        type="button"
                                        class="ml-2 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer"
                                        data-delete-page-structure-page
                                    >
                                        <i class="fa-solid fa-minus"></i>
                                    </button>
                                </div>

                                @php
                                    $sections = data_get($page, 'key_sections', []);
                                @endphp

                                <ul class="grid gap-1" data-page-sections-list>
                                    @foreach($sections as $sectionIndex => $section)
                                        <li class="flex items-center gap-2" data-page-section-item>
                                            <i class="fa-solid fa-circle text-[5px] text-[#215558]"></i>
                                            <p
                                                class="js-inline-edit text-xs text-[#215558] font-semibold leading-tight p-2 bg-gray-100 transition rounded-xl break-all"
                                                contenteditable="true"
                                                spellcheck="false"
                                                data-inline-key="page_structure.pages.{{ $pageIndex }}.key_sections.{{ $sectionIndex }}"
                                            >
                                                {{ $section }}
                                            </p>
                                            <button
                                                type="button"
                                                class="text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer"
                                                data-delete-page-section
                                            >
                                                <i class="fa-solid fa-minus"></i>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>

                                <button
                                    type="button"
                                    class="-mt-2 text-start text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer"
                                    data-add-page-section
                                >
                                    Sectie toevoegen
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <button
                        type="button"
                        class="-mt-2 text-start text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer"
                        data-add-page-structure-page
                    >
                        Pagina toevoegen
                    </button>
                </div>

                <hr class="border-gray-200">

                {{-- Investering --}}
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Investering</p>
                    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                        Hieronder zie je de investering uitgesplitst. Bedragen zijn exclusief btw en gebaseerd op de beschreven scope en het best passende pakket.
                    </p>

                    @php
                        $rows             = data_get($investment, 'rows', []);
                        $packageName      = data_get($investment, 'package_name');
                        $whyPackage       = data_get($investment, 'why_this_package');

                        // Tekstlabels (bijv. "€ 2.950,- eenmalig") – kun je later gebruiken in PDF
                        $totalSetupLabel   = data_get($investment, 'total_setup_amount');
                        $totalMonthlyLabel = data_get($investment, 'total_monthly_amount');

                        // Numerieke waardes voor inline inputs
                        $setupPriceEur    = data_get($investment, 'setup_price_eur');
                        $monthlyPriceEur  = data_get($investment, 'monthly_price_eur');

                        // Booleans om te bepalen of de rijen getoond moeten worden
                        $hasSetupTotal   = $setupPriceEur !== null || !empty($totalSetupLabel);
                        $hasMonthlyTotal = $monthlyPriceEur !== null || !empty($totalMonthlyLabel);
                    @endphp

                    @if(!empty($rows))
                        <div class="border border-gray-200 rounded-2xl divide-y divide-gray-200 overflow-hidden">
                            <div class="grid gap-2 py-3">
                                <div id="investment-rows-list" class="grid gap-2">
                                    @foreach($rows as $rowIndex => $row)
                                        <div
                                            class="flex items-center justify-between px-4 text-xs"
                                            data-investment-row
                                        >
                                            <p
                                                class="js-inline-edit max-w-[200px] p-2 rounded-xl bg-gray-100 transition text-[#215558] font-semibold leading-tight"
                                                contenteditable="true"
                                                spellcheck="false"
                                                data-inline-key="investment.rows.{{ $rowIndex }}.label"
                                            >
                                                {{ data_get($row, 'label') }}
                                            </p>

                                            <div class="flex items-center gap-2">
                                                <p
                                                    class="js-inline-edit max-w-[375px] p-2 rounded-xl bg-gray-100 transition text-[#215558] font-black leading-tight"
                                                    contenteditable="true"
                                                    spellcheck="false"
                                                    data-inline-key="investment.rows.{{ $rowIndex }}.amount"
                                                >
                                                    {{ data_get($row, 'amount') }}
                                                </p>

                                                <button
                                                    type="button"
                                                    class="ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer"
                                                    data-delete-investment-row
                                                >
                                                    <i class="fa-solid fa-minus"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <button
                                    type="button"
                                    class="ml-5 text-start text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer"
                                    data-add-investment-row
                                >
                                    Regel toevoegen
                                </button>
                            </div>

                            {{-- Totaalregels ook inline bewerkbaar --}}
                            @if($hasSetupTotal)
                                <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                                    <p class="text-xs text-[#215558] font-black">Totaal eenmalig</p>
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-[#215558] font-black">€</span>
                                        <input
                                            type="number"
                                            step="1"
                                            class="js-inline-number w-28 text-right text-sm text-[#215558] font-black p-2 rounded-xl bg-gray-100 border border-transparent focus:outline-none focus:border-[#0F9B9F] focus:ring-1 focus:ring-[#0F9B9F] transition"
                                            value="{{ $setupPriceEur !== null ? $setupPriceEur : '' }}"
                                            data-number-key="investment.setup_price_eur"
                                        >
                                    </div>
                                </div>
                            @endif
                            @if($hasMonthlyTotal)
                                <div class="flex items-center justify-between px-4 py-3 bg-gray-50">
                                    <p class="text-xs text-[#215558] font-black">Per maand</p>
                                    <div class="flex items-center gap-1">
                                        <span class="text-xs text-[#215558] font-black">€</span>
                                        <input
                                            type="number"
                                            step="1"
                                            class="js-inline-number w-28 text-right text-sm text-[#215558] font-black p-2 rounded-xl bg-gray-100 border border-transparent focus:outline-none focus:border-[#0F9B9F] focus:ring-1 focus:ring-[#0F9B9F] transition"
                                            value="{{ $monthlyPriceEur !== null ? $monthlyPriceEur : '' }}"
                                            data-number-key="investment.monthly_price_eur"
                                        >
                                    </div>
                                </div>
                            @endif
                        </div>
                    @else
                        {{-- Fallback: oude statische tabel als er (nog) geen investment is --}}
                        <div class="border border-gray-200 rounded-2xl divide-y divide-gray-200 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 text-xs">
                                <p class="text-[#215558] font-semibold">Webdesign &amp; ontwikkeling</p>
                                <p class="text-[#215558] font-black">€ 0.000,- eenmalig</p>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3 text-xs">
                                <p class="text-[#215558] font-semibold">Technische inrichting &amp; koppelingen</p>
                                <p class="text-[#215558] font-black">€ 0.000,- eenmalig</p>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3 text-xs">
                                <p class="text-[#215558] font-semibold">SEO-basis &amp; optimalisatie key pages</p>
                                <p class="text-[#215558] font-black">€ 0.000,- eenmalig</p>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3 text-xs">
                                <p class="text-[#215558] font-semibold">Hosting, onderhoud &amp; support</p>
                                <p class="text-[#215558] font-black">€ 000,- per maand</p>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3 bg-[#215558]/5">
                                <p class="text-xs text-[#215558] font-black">Totaal eenmalig</p>
                                <p class="text-sm text-[#215558] font-black">€ 0.000,-</p>
                            </div>
                            <div class="flex items-center justify-between px-4 py-3 bg-[#215558]/5">
                                <p class="text-xs text-[#215558] font-black">Per maand</p>
                                <p class="text-sm text-[#215558] font-black">€ 000,-</p>
                            </div>
                        </div>
                    @endif

                    <p class="text-[11px] text-[#215558] font-bold leading-tight">
                        Extra pagina's nodig? Geen probleem!<br>Voor € 195,- bouwen wij een extra pagina, volledig naar wens.
                    </p>
                    <p class="text-[11px] text-[#215558] font-semibold leading-tight opacity-60">
                        * Bovenstaande bedragen zijn indicatief en worden definitief op basis van de gekozen opties en eventuele aanvullende wensen.
                    </p>
                </div>

                <hr class="border-gray-200">

                {{-- Support & onderhoud --}}
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Support, onderhoud & groei</p>
                    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                        Na livegang laten we je niet los. We zorgen dat de techniek veilig, snel en up-to-date blijft, én dat je altijd bij ons terechtkunt met vragen of ideeën.
                    </p>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Beveiligde hostingomgeving, monitoring en basis back-ups.
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Technische updates en onderhoud van de website / webshop.
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Toegang tot ons supportportaal voor vragen en wijzigingsverzoeken.
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Van start met Eazyonline --}}
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Vervolgstappen</p>
                    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. A ea expedita eveniet facere corporis earum debitis voluptatibus et, perferendis accusamus! Et sit necessitatibus ea quos suscipit, ex sapiente facilis expedita.
                    </p>
                    <div class="flex flex-col gap-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Offerte tekenen
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-orange-700 bg-orange-100">Hier ben je nu</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    50% aanbetaling (of nader afgesproken)
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-gray-700 bg-gray-100">Volgend</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Content aanleveren via ons formulier
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-gray-700 bg-gray-100">Volgend</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Project inplannen
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-gray-700 bg-gray-100">Volgend</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Website wordt gebouwd
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-gray-700 bg-gray-100">Volgend</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Revisie-ronde / Final
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-gray-700 bg-gray-100">Volgend</div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Livegang
                                </p>
                            </div>
                            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-gray-700 bg-gray-100">Volgend</div>
                        </div>
                        <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0 mt-2">
                            Lorem ipsum dolor sit amet, consectetur adipisicing elit. A ea expedita eveniet facere corporis earum debitis voluptatibus et, perferendis accusamus! Et sit necessitatibus ea quos suscipit, ex sapiente facilis expedita.
                        </p>
                    </div>
                </div>


                <hr class="border-gray-200">

                {{-- Randvoorwaarden & scope --}}
                <div class="grid gap-4">
                    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Randvoorwaarden & scope</p>
                    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                        Om de samenwerking soepel te laten verlopen, maken we duidelijke afspraken over scope, oplevering en verantwoordelijkheden.
                    </p>

                    <div class="grid grid-cols-2 gap-4">
                        <ul class="text-xs grid gap-2">
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info fa-xs mt-0.5 text-[#215558]"></i>
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Deze offerte is gebaseerd op de besproken wensen en uitgangspunten. Grote wijzigingen in scope kunnen invloed hebben op planning en investering.
                                </p>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info fa-xs mt-0.5 text-[#215558]"></i>
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Content (teksten, foto’s, video’s) wordt aangeleverd door de opdrachtgever, tenzij anders overeengekomen.
                                </p>
                            </li>
                        </ul>

                        <ul class="text-xs grid gap-2">
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info fa-xs mt-0.5 text-[#215558]"></i>
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Extra werkzaamheden buiten de afgesproken scope vallen onder meerwerk en worden vooraf afgestemd op basis van ons uurtarief.
                                </p>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-info fa-xs mt-0.5 text-[#215558]"></i>
                                <p class="text-xs text-[#215558] font-semibold leading-tight">
                                    Op deze offerte zijn de algemene voorwaarden van Eazyonline van toepassing.
                                </p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="h-fit sticky top-[88px] bg-white rounded-2xl p-6 border border-gray-200 flex flex-col gap-4">
                @php
                    $status = $offerte->status ?? 'concept';

                    $statusConfig = [
                        'concept' => [
                            'label' => 'Concept',
                            'bg'    => 'bg-cyan-100',
                            'text'  => 'text-cyan-700',
                        ],
                        'pending' => [
                            'label' => 'Te ondertekenen',
                            'bg'    => 'bg-orange-100',
                            'text'  => 'text-orange-700',
                        ],
                        'signed' => [
                            'label' => 'Getekend',
                            'bg'    => 'bg-emerald-100',
                            'text'  => 'text-emerald-700',
                        ],
                        'expired' => [
                            'label' => 'Verlopen',
                            'bg'    => 'bg-red-100',
                            'text'  => 'text-red-700',
                        ],
                    ];

                    $statusCfg = $statusConfig[$status] ?? $statusConfig['concept'];

                    $companyName   = $project->company ?? 'Nog geen bedrijfsnaam';
                    $contactName   = $project->contact_name ?? null;
                    $contactEmail  = $project->contact_email ?? $project->email ?? null;
                    $contactPhone  = $project->contact_phone ?? $project->phone ?? null;

                    $setupPriceEur     = data_get($investment, 'setup_price_eur');
                    $monthlyPriceEur   = data_get($investment, 'monthly_price_eur');
                    $totalSetupLabel   = data_get($investment, 'total_setup_amount');
                    $totalMonthlyLabel = data_get($investment, 'total_monthly_amount');

                    $timeline = [];

                    // Altijd: aangemaakt (heeft altijd een datum)
                    if ($offerte->created_at) {
                        $timeline[] = [
                            'key'           => 'created',
                            'label'         => 'Offerte aangemaakt',
                            'statusClasses' => 'bg-cyan-100 text-cyan-700',
                            'at'            => $offerte->created_at,
                        ];
                    }

                    // Alleen tonen als er echt een wijziging is geweest
                    if ($offerte->updated_at && $offerte->updated_at->gt($offerte->created_at)) {
                        $timeline[] = [
                            'key'           => 'updated',
                            'label'         => 'Offerte laatst bewerkt',
                            'statusClasses' => 'bg-orange-100 text-orange-700',
                            'at'            => $offerte->updated_at,
                        ];
                    }

                    // Alleen toevoegen als sent_at gevuld is
                    if ($offerte->sent_at) {
                        $timeline[] = [
                            'key'           => 'sent',
                            'label'         => 'Verzonden naar klant',
                            'statusClasses' => 'bg-emerald-100 text-emerald-700',
                            'at'            => $offerte->sent_at,
                        ];
                    }
                @endphp

                {{-- Header: klant & status --}}
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-base text-[#215558] font-black leading-tight truncate shrink-0">Snel overzicht</p>
                    </div>
                    <span
                        class="px-2.5 py-0.5 rounded-full font-semibold text-[11px] {{ $statusCfg['bg'] }} {{ $statusCfg['text'] }}"
                        data-offerte-status-label
                    >
                        {{ $statusCfg['label'] }}
                    </span>
                </div>

                {{-- Klant & contactpersoon --}}
                <div class="rounded-2xl bg-gray-50 px-3 py-3 text-xs text-[#215558] font-semibold grid">
                    <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0 mb-1">Offerte voor</p>
                    <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">{{ $companyName }}</p>

                    @if($contactName)
                        <div class="w-full flex items-center gap-2 mt-3">
                            <i class="min-w-[15px] fa-solid fa-user text-[#215558] text-xs"></i>
                            <p class="text-xs font-semibold text-[#215558] truncate">
                                {{ $contactName }}
                            </p>
                        </div>
                    @endif

                    <div class="flex flex-col gap-1">
                        @if($contactEmail)
                            <div class="w-full flex items-center gap-2 mt-1">
                                <i class="min-w-[15px] fa-solid fa-paper-plane text-[#215558] text-[11px]"></i>
                                <p class="text-xs font-semibold text-[#215558] truncate">
                                {{ $contactEmail }}
                                </p>
                            </div>
                        @endif
                        @if($contactPhone)
                            <div class="w-full flex items-center gap-2">
                                <i class="min-w-[15px] fa-solid fa-phone text-[#215558] text-[11px]"></i>
                                <p class="text-xs font-semibold text-[#215558] truncate">
                                    {{ $contactPhone }}
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Financieel overzicht --}}
                <div class="grid grid-cols-1 gap-2 text-xs">
                    <div class="border border-gray-200 rounded-2xl p-3 flex flex-col gap-1">
                        <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Totaal eenmalig</p>
                        <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">
                            @if(!is_null($setupPriceEur))
                                € {{ number_format($setupPriceEur, 0, ',', '.') }},
                            @elseif($totalSetupLabel)
                                {{ $totalSetupLabel }}
                            @else
                                n.n.b.
                            @endif
                        </p>
                    </div>
                    <div class="border border-gray-200 rounded-2xl p-3 flex flex-col gap-1">
                        <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Per maand</p>
                        <p class="text-xs text-[#215558] font-semibold leading-tight truncate shrink-0">
                            @if(!is_null($monthlyPriceEur))
                                € {{ number_format($monthlyPriceEur, 0, ',', '.') }},
                            @elseif($totalMonthlyLabel)
                                {{ $totalMonthlyLabel }}
                            @else
                                n.n.b.
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Tijdlijn --}}
                <div class="pt-3 border-t border-gray-200">
                    <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0 mb-2">Tijdlijn</p>
                    <ul class="space-y-1.5" data-timeline-list>
                        @foreach($timeline as $item)
                            <li
                                class="flex items-center justify-between gap-2 text-[11px] text-[#215558]"
                                @if($item['key'] === 'sent') data-timeline-sent="1" @endif
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-semibold leading-tight"
                                        data-timeline-date="{{ $item['key'] }}"
                                    >
                                        @if($item['at'])
                                            {{ \Illuminate\Support\Carbon::parse($item['at'])->format('d-m-Y H:i') }}
                                        @endif
                                    </span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full font-semibold text-[11px] {{ $item['statusClasses'] }}">
                                    {{ $item['label'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Bottom bar --}}
@php
    $isSent = !is_null($offerte->sent_at);
@endphp
<div class="w-full fixed z-50 bottom-0 left-0 bg-white border-b border-b-gray-200 p-4">
    <div class="max-w-6xl mx-auto flex items-center gap-3">
        {{-- Versturen --}}
        <a href="#"
           id="send-offerte-button"
           @if($isSent) data-disabled="1" @endif
           class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300
                  {{ $isSent ? 'opacity-50 cursor-not-allowed pointer-events-none hover:bg-[#0F9B9F]' : '' }}">
            Offerte goedkeuren en opsturen
        </a>

        {{-- Intrekken – alleen tonen als hij al verstuurd is --}}
        <a href="#"
           id="revoke-offerte-button"
           class="bg-red-500 hover:bg-red-600 cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300
                  {{ $isSent ? '' : 'hidden' }}">
            Offerte intrekken
        </a>
    </div>
</div>

<div id="toast-container"
     class="fixed bottom-6 right-6 z-[9999] space-y-2 pointer-events-none">
</div>

<div
    id="regenerate-overlay"
    class="fixed inset-0 z-[9998] bg-black/25 backdrop-blur-sm flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300"
>
    <div class="w-full max-w-xs md:max-w-sm bg-white rounded-2xl shadow-xl border border-gray-200 p-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-arrow-rotate-right text-emerald-600"></i>
            </div>
            <div class="flex-1">
                <p class="text-base font-black text-[#215558]">
                    Offerte wordt opnieuw gegenereerd
                </p>
                <p class="mt-1 text-sm text-[#215558] opacity-80">
                    Even geduld, het systeem is druk bezig met het opnieuw te genereren van de offerte.
                </p>
            </div>
        </div>

        <div class="space-y-2 mt-4">
            <div class="w-full h-3 rounded-full bg-gray-200 overflow-hidden">
                <div
                    id="regenerate-progress-bar"
                    class="h-full rounded-full bg-[#0F9B9F] transition-all duration-200"
                    style="width: 0%;"
                ></div>
            </div>
            <p class="text-[11px] text-[#215558]/80 font-semibold text-right">
                <span id="regenerate-progress-text">0</span>% geladen
            </p>
        </div>
    </div>
</div>

<div
    id="regenerate-confirm-overlay"
    class="fixed inset-0 z-[9998] bg-black/25 flex items-center justify-center opacity-0 pointer-events-none transition-opacity duration-300"
>
    <div class="w-full max-w-xs md:max-w-sm bg-white rounded-2xl shadow-xl border border-gray-200 p-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-red-600"></i>
            </div>
            <div class="flex-1">
                <p class="text-base font-black text-[#215558]">
                    Offerte opnieuw genereren
                </p>
                <p class="mt-1 text-sm text-[#215558]">
                    Weet je zeker dat je de offerte opnieuw wilt genereren? Alle wijzigingen zullen vervallen.
                </p>
            </div>
        </div>
        <div class="mt-4 flex items-center justify-end gap-2">
            <button
                type="button"
                class="bg-red-500 hover:bg-red-600 cursor-pointer text-center w-full text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300 disabled:opacity-60"
                data-regenerate-confirm
            >
                Ja, ik weet het zeker
            </button>
            <button
                type="button"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 cursor-pointer font-semibold px-6 py-3 rounded-full transition duration-300"
                data-regenerate-cancel
            >
                Annuleren
            </button>
        </div>
    </div>
</div>

<script>
    window.showToast = function (message, type = 'success') {
        const container = document.getElementById('toast-container');
        if (!container) return;

        const el = document.createElement('div');
        el.className = [
            'pointer-events-auto',
            'w-72',
            'rounded-2xl',
            'border',
            'border-gray-200',
            'px-3',
            'py-2.5',
            'bg-white',
            'shadow-lg',
            'flex',
            'items-start',
            'gap-2',
            'transform-gpu',
            'translate-y-3',
            'opacity-0',
            'transition-all',
            'duration-300'
        ].join(' ');

        const isError = type === 'error';

        const iconHtml = isError
            ? `
            <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-red-100 flex items-center justify-center">
              <i class="fa-solid fa-xmark text-[11px] text-red-500 mb-0.5"></i>
            </div>
          `
            : `
            <div class="mt-0.5 flex-shrink-0 w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center">
              <i class="fa-solid fa-check text-[11px] text-emerald-600 mb-0.5"></i>
            </div>
          `;

        el.innerHTML = `
          ${iconHtml}
          <div class="mt-0.5 text-xs font-semibold text-[#215558] leading-snug">
            ${message}
          </div>
        `;

        container.appendChild(el);

        requestAnimationFrame(() => {
            el.classList.remove('translate-y-3', 'opacity-0');
        });

        setTimeout(() => {
            el.classList.add('opacity-0', 'translate-y-3');
            el.addEventListener(
                'transitionend',
                () => {
                    el.remove();
                },
                { once: true }
            );
        }, 3000);
    };
</script>

{{-- Alpine component voor slider --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('reviewsCarousel', () => ({
        index: 0,
        total: 0,
        timer: null,

        translateStyle() {
            return `transform: translateX(-${this.index * 100}%);`;
        },

        tick() {
            if (this.total <= 1) return;
            this.index = (this.index + 1) % this.total;
        },

        start() {
            this.total = this.$refs.track.children.length || 0;

            this.pause();
            if (this.total > 1) {
                this.timer = setInterval(() => this.tick(), 5000);
            }
        },

        pause() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        }
    }));
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const el = document.getElementById('offerte-countdown');
    if (!el) return;

    const expiryStr = el.dataset.expiry;
    if (!expiryStr) return;

    const expiry = new Date(expiryStr);
    if (isNaN(expiry.getTime())) return;

    function pad(num) {
        return String(num).padStart(2, '0');
    }

    function updateCountdown() {
        const now  = new Date();
        let diff   = Math.floor((expiry - now) / 1000); // seconden

        if (diff <= 0) {
            el.textContent = '00:00:00:00';
            el.classList.remove('bg-green-200', 'text-green-700');
            el.classList.add('bg-red-200', 'text-red-700');
            return;
        }

        const days    = Math.floor(diff / (24 * 60 * 60));
        diff          = diff % (24 * 60 * 60);
        const hours   = Math.floor(diff / (60 * 60));
        diff          = diff % (60 * 60);
        const minutes = Math.floor(diff / 60);
        const seconds = diff % 60;

        // Formaat: DD:HH:MM:SS
        el.textContent = `${pad(days)}:${pad(hours)}:${pad(minutes)}:${pad(seconds)}`;
    }

    // Init + interval
    updateCountdown();
    setInterval(updateCountdown, 1000);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isReadOnlyInitial = @json(!is_null($offerte->sent_at));

    // Globale helper om de offerte in read-only te zetten
    window.makeOfferteReadOnly = function () {
        // Alle inline teksten uitschakelen
        const editableEls = document.querySelectorAll('.js-inline-edit');
        editableEls.forEach(function (el) {
            el.removeAttribute('contenteditable');
            el.classList.remove('cursor-text');
            el.classList.add('opacity-60');
        });

        // Numerieke inputs (totaal eenmalig / per maand) disablen
        const numberInputs = document.querySelectorAll('.js-inline-number');
        numberInputs.forEach(function (input) {
            input.setAttribute('disabled', 'disabled');
            input.classList.add('bg-gray-50', 'cursor-not-allowed', 'opacity-60');
        });

        // Alle + / – knoppen uitschakelen
        const buttons = document.querySelectorAll([
            '[data-add-strong-point]',
            '[data-add-improvement-point]',
            '[data-add-summary-bullet]',
            '[data-add-scope-item]',
            '[data-add-goal-item]',
            '[data-add-page-structure-page]',
            '[data-add-page-section]',
            '[data-delete-investment-row]',
            '[data-delete-page-section]',
            '[data-delete-page-structure-page]',
            '[data-delete-goal-item]',
            '[data-delete-scope-item]',
            '[data-delete-summary-bullet]',
            '[data-delete-strong-point]',
            '[data-delete-improvement-point]'
        ].join(','));

        buttons.forEach(function (btn) {
            btn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        });

        // Regenerate-knop ook uitschakelen als hij bestaat
        const regenBtn = document.getElementById('regenerate-offerte-button');
        if (regenBtn) {
            regenBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        }
    };

    // Als sent_at al gevuld is bij het laden → alles direct read-only
    if (isReadOnlyInitial) {
        window.makeOfferteReadOnly();
        return; // geen inline events meer koppelen
    }

    // ✏️ Endpoint voor inline opslaan
    const endpoint = @json(route('offerte.inline-update', $offerte->public_uuid));

    function attachInline(el) {
        if (!el || el.dataset.inlineAttached === '1') return;
        el.dataset.inlineAttached = '1';

        // 🔑 Strip éénmalig de whitespace/newlines aan begin/eind uit de DOM,
        // zodat de indent uit Blade geen lege regel bovenaan geeft
        el.textContent = el.textContent.trim();

        el.classList.add('cursor-text');

        el.addEventListener('focus', function () {
            el.classList.add('outline-none', 'bg-gray-200');
        });

        el.addEventListener('blur', function () {
            el.classList.remove('bg-gray-200');
            saveInline(el);
        });

        el.addEventListener('keydown', function (e) {
            if (
                e.key === 'Enter' &&
                !e.shiftKey &&
                !e.altKey &&
                !e.ctrlKey &&
                !e.metaKey
            ) {
                e.preventDefault();
                el.blur();
            }
            // Shift+Enter laten we gewoon door
        });
    }

    const editableEls = document.querySelectorAll('.js-inline-edit');
    editableEls.forEach(attachInline);

    // Numeric inputs voor totaalbedragen (eenmalig / per maand)
    const numberInputs = document.querySelectorAll('.js-inline-number');
    numberInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            saveInlineNumber(input);
        });
        input.addEventListener('blur', function () {
            saveInlineNumber(input);
        });
    });

    function saveInlineNumber(input) {
        const key = input.dataset.numberKey;
        if (!key) return;

        const value = input.value === '' ? null : input.value;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({ key, value }),
        })
            .then(function (res) {
                if (!res.ok) throw new Error('Save failed');
                return res.json();
            })
            .then(function () {
                if (window.showToast) {
                    window.showToast('Investering bijgewerkt.', 'success');
                }
            })
            .catch(function () {
                if (window.showToast) {
                    window.showToast('Opslaan mislukt. Probeer het opnieuw.', 'error');
                }
            });
    }

    function saveInline(el) {
        const key = el.dataset.inlineKey;
        if (!key) return;

        const value = el.innerText.trim();
        el.dataset.saving = '1';

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({ key, value }),
        })
            .then(function (res) {
                if (!res.ok) throw new Error('Save failed');
                return res.json();
            })
            .then(function () {
                el.classList.remove('ring-1');

                if (window.showToast) {
                    window.showToast('Gelukt! De tekst is succesvol bijgewerkt en staat op de offerte van de klant.', 'success');
                }
            })
            .catch(function () {
                el.classList.remove('ring-1');

                if (window.showToast) {
                    window.showToast('Opslaan mislukt. Probeer het opnieuw.', 'error');
                } else {
                    alert('Opslaan mislukt.');
                }
            })
            .finally(function () {
                delete el.dataset.saving;
            });
    }

    function nextIndex(prefix) {
        const existing = document.querySelectorAll('[data-inline-key^="' + prefix + '."]');
        return existing.length;
    }

    // ➕ Nieuw sterk punt
    const addStrongBtn = document.querySelector('[data-add-strong-point]');
    if (addStrongBtn) {
        addStrongBtn.addEventListener('click', function () {
            const list = document.getElementById('strong-points-list');
            if (!list) return;

            const idx = nextIndex('strong_points');

            const li = document.createElement('li');
            li.className = 'w-full flex items-center gap-2';

            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-check fa-xs mt-0.5 text-[#215558]';

            const p = document.createElement('p');
            p.className = 'js-inline-edit min-w-[305px] max-w-[305px] break-all text-xs text-[#215558] font-semibold leading-tight p-2 bg-gray-100 transition rounded-xl';
            p.contentEditable = 'true';
            p.dataset.inlineKey = 'strong_points.' + idx;

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'ml-1 text-[10px] text-red-600 hover:text-red-700';
            delBtn.dataset.deleteStrongPoint = '1';
            delBtn.innerHTML = '<i class="fa-solid fa-minus"></i>';

            li.appendChild(icon);
            li.appendChild(p);
            li.appendChild(delBtn);

            // Voeg toe vóór de laatste li (die met de + knop)
            const lastLi = list.querySelector('li:last-child');
            list.insertBefore(li, lastLi);

            attachInline(p);
            p.focus();
        });
    }

    // ➕ Nieuw verbeterpunt
    const addImprovementBtn = document.querySelector('[data-add-improvement-point]');
    if (addImprovementBtn) {
        addImprovementBtn.addEventListener('click', function () {
            const list = document.getElementById('improvement-points-list');
            if (!list) return;

            const idx = nextIndex('improvement_points');

            const li = document.createElement('li');
            li.className = 'w-full flex items-center gap-2';

            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-check fa-xs mt-0.5 text-[#215558]';

            const p = document.createElement('p');
            p.className = 'js-inline-edit min-w-[305px] max-w-[305px] break-all text-xs text-[#215558] font-semibold leading-tight p-2 bg-gray-100 transition rounded-xl';
            p.contentEditable = 'true';
            p.dataset.inlineKey = 'improvement_points.' + idx;

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'ml-1 text-[10px] text-red-600 hover:text-red-700';
            delBtn.dataset.deleteImprovementPoint = '1';
            delBtn.innerHTML = '<i class="fa-solid fa-minus"></i>';

            li.appendChild(icon);
            li.appendChild(p);
            li.appendChild(delBtn);

            const lastLi = list.querySelector('li:last-child');
            list.insertBefore(li, lastLi);

            attachInline(p);
            p.focus();
        });
    }

    // ➕ Nieuwe samenvattings-bullet
    const addSummaryBtn = document.querySelector('[data-add-summary-bullet]');
    if (addSummaryBtn) {
        addSummaryBtn.addEventListener('click', function () {
            const list = document.getElementById('summary-bullets-list');
            if (!list) return;

            const idx = nextIndex('summary_bullets');

            const wrapper = document.createElement('span');
            wrapper.className = 'w-full px-2.5 py-1 text-xs bg-[#215558]/20 text-[#215558] font-semibold rounded-xl flex items-center gap-2 leading-tighter';
            wrapper.dataset.summaryBulletItem = '1';

            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-check fa-xs mt-0.5 text-[#215558]';

            const span = document.createElement('span');
            span.className = 'js-inline-edit min-w-[293px] max-w-[293px] break-all p-2 bg-gray-100 transition rounded-xl';
            span.contentEditable = 'true';
            span.dataset.inlineKey = 'summary_bullets.' + idx;

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer mt-0.5';
            delBtn.dataset.deleteSummaryBullet = '1';
            delBtn.innerHTML = '<i class="fa-solid fa-minus"></i>';

            wrapper.appendChild(icon);
            wrapper.appendChild(span);
            wrapper.appendChild(delBtn);

            // Voeg toe vóór de laatste knop (de + button zelf)
            const lastChild = list.querySelector('[data-add-summary-bullet]');
            list.insertBefore(wrapper, lastChild);

            attachInline(span);
            span.focus();
        });
    }

    // ➕ Nieuw scope-item ("Wat je van ons krijgt")
    const addScopeBtn = document.querySelector('[data-add-scope-item]');
    if (addScopeBtn) {
        addScopeBtn.addEventListener('click', function () {
            const list = document.getElementById('scope-items-list');
            if (!list) return;

            const idx = nextIndex('scope_items');

            const row = document.createElement('div');
            row.className = 'flex items-center justify-between';
            row.dataset.scopeItem = '1';

            const left = document.createElement('div');
            left.className = 'flex items-center gap-2';

            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-check fa-xs mt-0.5 text-[#215558]';

            const p = document.createElement('p');
            p.className = 'js-inline-edit min-w-[530px] max-w-[530px] break-all text-xs text-[#215558] font-semibold p-2 bg-gray-100 transition rounded-xl leading-tight';
            p.contentEditable = 'true';
            p.dataset.inlineKey = 'scope_items.' + idx;

            left.appendChild(icon);
            left.appendChild(p);

            const right = document.createElement('div');
            right.className = 'flex items-center gap-2';

            const badge = document.createElement('div');
            badge.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100';
            badge.innerText = 'Inbegrepen';

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer mt-0.5';
            delBtn.dataset.deleteScopeItem = '1';
            delBtn.innerHTML = '<i class="fa-solid fa-minus"></i>';

            right.appendChild(badge);
            right.appendChild(delBtn);

            row.appendChild(left);
            row.appendChild(right);

            // Voeg nieuw item toe vóór de add-knop zelf
            const addButton = list.querySelector('[data-add-scope-item]');
            list.insertBefore(row, addButton);

            attachInline(p);
            p.focus();
        });
    }

    // ➕ Nieuw doel / KPI
    const addGoalBtn = document.querySelector('[data-add-goal-item]');
    if (addGoalBtn) {
        addGoalBtn.addEventListener('click', function () {
            const list = document.getElementById('goals-items-list');
            if (!list) return;

            const idx = nextIndex('goals_items');

            const li = document.createElement('li');
            li.className = 'w-full flex items-center gap-2';
            li.dataset.goalItem = '1';

            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-bullseye fa-xs mt-0.5 text-[#215558]';

            const p = document.createElement('p');
            p.className = 'js-inline-edit min-w-[500px] max-w-[500px] break-all text-xs text-[#215558] font-semibold leading-tight p-2 bg-gray-100 transition rounded-xl';
            p.contentEditable = 'true';
            p.dataset.inlineKey = 'goals_items.' + idx;

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer mt-0.5';
            delBtn.dataset.deleteGoalItem = '1';
            delBtn.innerHTML = '<i class="fa-solid fa-minus"></i>';

            li.appendChild(icon);
            li.appendChild(p);
            li.appendChild(delBtn);

            list.appendChild(li);

            attachInline(p);
            p.focus();
        });
    }

    // ➕ Nieuwe pagina in "Voorstel paginastructuur"
    const addPageStructBtn = document.querySelector('[data-add-page-structure-page]');
    if (addPageStructBtn) {
        addPageStructBtn.addEventListener('click', function () {
            const list = document.getElementById('page-structure-pages-list');
            if (!list) return;

            // Bepaal volgende page-index op basis van bestaande kaarten
            let maxIndex = -1;
            list.querySelectorAll('[data-page-structure-page]').forEach((card) => {
                const val = parseInt(card.getAttribute('data-page-index') || '-1', 10);
                if (!isNaN(val) && val > maxIndex) {
                    maxIndex = val;
                }
            });
            const pageIndex = maxIndex + 1;

            const card = document.createElement('div');
            card.className = 'border border-gray-200 rounded-2xl p-4 text-xs grid gap-4';
            card.dataset.pageStructurePage = '1';
            card.setAttribute('data-page-index', String(pageIndex));

            card.innerHTML = `
                <div class="flex items-start justify-between gap-2">
                    <div class="flex flex-col gap-2">
                        <p
                            class="js-inline-edit text-sm text-[#215558] font-black leading-tight truncate p-2 bg-gray-100 transition rounded-xl shrink-0"
                            contenteditable="true"
                            spellcheck="false"
                            data-inline-key="page_structure.pages.${pageIndex}.title"
                        ></p>

                        <span
                            class="js-inline-edit text-xs text-[#215558] font-semibold leading-tight max-w-[75%] p-2 bg-gray-100 transition rounded-xl shrink-0 break-all"
                            contenteditable="true"
                            spellcheck="false"
                            data-inline-key="page_structure.pages.${pageIndex}.goal"
                        ></span>
                    </div>

                    <button
                        type="button"
                        class="ml-2 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer"
                        data-delete-page-structure-page
                    >
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>

                <ul class="grid gap-1" data-page-sections-list></ul>

                <button
                    type="button"
                    class="mt-1 text-start text-[11px] text-[#215558] font-semibold opacity-70 hover:opacity-100 transition cursor-pointer"
                    data-add-page-section
                >
                    Sectie toevoegen
                </button>
            `;

            list.appendChild(card);

            // Inline editing activeren op title/goal
            card.querySelectorAll('.js-inline-edit').forEach(attachInline);
        });
    }

    // ➕ Nieuwe sectie binnen een pagina in "Voorstel paginastructuur"
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-add-page-section]');
        if (!btn) return;

        const card = btn.closest('[data-page-structure-page]');
        if (!card) return;

        const list = card.querySelector('[data-page-sections-list]');
        if (!list) return;

        const pageIndex = card.getAttribute('data-page-index');
        if (pageIndex === null) return;

        const prefix = 'page_structure.pages.' + pageIndex + '.key_sections';
        const idx    = nextIndex(prefix);

        const li = document.createElement('li');
        li.className = 'flex items-center gap-2';
        li.dataset.pageSectionItem = '1';
        li.innerHTML = `
            <i class="fa-solid fa-circle text-[5px] text-[#215558]"></i>
            <p
                class="js-inline-edit text-xs text-[#215558] font-semibold leading-tight p-2 bg-gray-100 transition rounded-xl break-all"
                contenteditable="true"
                spellcheck="false"
                data-inline-key="${prefix}.${idx}"
            ></p>
            <button
                type="button"
                class="text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer"
                data-delete-page-section
            >
                <i class="fa-solid fa-minus"></i>
            </button>
        `;

        list.appendChild(li);

        const p = li.querySelector('.js-inline-edit');
        if (p) {
            attachInline(p);
            p.focus();
        }
    });

    // ➕ Nieuwe investering-regel
    const addInvestmentBtn = document.querySelector('[data-add-investment-row]');
    if (addInvestmentBtn) {
        addInvestmentBtn.addEventListener('click', function () {
            const list = document.getElementById('investment-rows-list');
            if (!list) return;

            // Volgende index = aantal huidige rijen
            const currentRows = list.querySelectorAll('[data-investment-row]');
            const idx = currentRows.length;

            const row = document.createElement('div');
            row.className = 'flex items-center justify-between px-4 text-xs';
            row.dataset.investmentRow = '1';

            row.innerHTML = `
                <p
                    class="js-inline-edit max-w-[200px] p-2 rounded-xl bg-gray-100 transition text-[#215558] font-semibold leading-tight"
                    contenteditable="true"
                    spellcheck="false"
                    data-inline-key="investment.rows.${idx}.label"
                ></p>
                <div class="flex items-center gap-2">
                    <p
                        class="js-inline-edit max-w-[375px] p-2 rounded-xl bg-gray-100 transition text-[#215558] font-black leading-tight"
                        contenteditable="true"
                        spellcheck="false"
                        data-inline-key="investment.rows.${idx}.amount"
                    ></p>
                    <button
                        type="button"
                        class="ml-1 text-[10px] text-red-600/50 hover:text-red-600 transition cursor-pointer"
                        data-delete-investment-row
                    >
                        <i class="fa-solid fa-minus"></i>
                    </button>
                </div>
            `;

            list.appendChild(row);

            row.querySelectorAll('.js-inline-edit').forEach(attachInline);
            const firstField = row.querySelector('.js-inline-edit');
            if (firstField) firstField.focus();
        });
    }

    // 🗑 Verwijderen van een investering-regel
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-delete-investment-row]');
        if (!btn) return;

        const row = btn.closest('[data-investment-row]');
        if (!row) return;

        const editable = row.querySelector('.js-inline-edit[data-inline-key^="investment.rows."]');
        const key = editable ? editable.dataset.inlineKey : null;

        let deletedIndex = null;
        if (key) {
            const parts = key.split('.');
            deletedIndex = parseInt(parts[2] || '0', 10); // investment.rows.{index}.label
        }

        row.remove();

        if (deletedIndex === null) return;

        // Herindexeer DOM-keys zodat ze weer 0,1,2,... zijn
        const list = document.getElementById('investment-rows-list');
        if (list) {
            list.querySelectorAll('[data-investment-row]').forEach((rowEl) => {
                rowEl
                    .querySelectorAll('.js-inline-edit[data-inline-key^="investment.rows."]')
                    .forEach((field) => {
                        const k = field.dataset.inlineKey || '';
                        const segments = k.split('.');
                        const idx = parseInt(segments[2] || '0', 10);

                        if (idx > deletedIndex) {
                            segments[2] = String(idx - 1);
                            field.dataset.inlineKey = segments.join('.');
                        }
                    });
            });
        }

        // Server laten wissen met key op het rijniveau
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({ key: 'investment.rows.' + deletedIndex, _delete: true }),
        }).catch(() => {});
    });

    // 🗑 Sectie binnen pagina verwijderen
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-delete-page-section]');
        if (!btn) return;

        const item = btn.closest('[data-page-section-item]');
        if (!item) return;

        const editable = item.querySelector('.js-inline-edit');
        const key      = editable ? editable.dataset.inlineKey : null;

        item.remove();

        if (!key) return;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({ key, _delete: true }),
        }).catch(() => {});
    });

    // 🗑 Gehele pagina uit "Voorstel paginastructuur" verwijderen
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-delete-page-structure-page]');
        if (!btn) return;

        const card = btn.closest('[data-page-structure-page]');
        if (!card) return;

        const pageIndex = card.getAttribute('data-page-index');
        if (pageIndex === null) return;

        const key = 'page_structure.pages.' + pageIndex;

        card.remove();

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({ key, _delete: true }),
        }).catch(() => {});
    });

    // 🗑 Verwijderen van een doel / KPI
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-delete-goal-item]');
        if (!btn) return;

        const li = btn.closest('[data-goal-item]');
        if (!li) return;

        const p = li.querySelector('.js-inline-edit');
        const key = p ? p.dataset.inlineKey : null;

        li.remove();

        if (!key) return;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content'),
            },
            body: JSON.stringify({ key, _delete: true }),
        }).catch(() => {});
    });

    // 🗑 Verwijderen van een scope-item
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-delete-scope-item]');
        if (!btn) return;

        const row = btn.closest('[data-scope-item]');
        if (!row) return;

        const editable = row.querySelector('.js-inline-edit');
        const key = editable ? editable.dataset.inlineKey : null;

        row.remove();

        if (!key) return;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ key, _delete: true }),
        }).catch(() => {});
    });

    // 🗑 Verwijderen van een samenvattings-bullet
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-delete-summary-bullet]');
        if (!btn) return;

        const item = btn.closest('[data-summary-bullet-item]');
        if (!item) return;

        const editable = item.querySelector('.js-inline-edit');
        const key = editable ? editable.dataset.inlineKey : null;

        // Haal meteen uit de DOM
        item.remove();

        if (!key) return;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ key, _delete: true }),
        }).catch(() => {});
    });

    // 🗑 Verwijderen van een sterk punt
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-delete-strong-point]');
        if (!btn) return;

        const li = btn.closest('li');
        if (!li) return;

        const p = li.querySelector('.js-inline-edit');
        const key = p ? p.dataset.inlineKey : null;

        // Direct uit de DOM halen voor UX
        li.remove();

        if (!key) return;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ key, _delete: true }),
        }).catch(() => {});
    });

    // 🗑 Verwijderen van een verbeterpunt
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-delete-improvement-point]');
        if (!btn) return;

        const li = btn.closest('li');
        if (!li) return;

        const p = li.querySelector('.js-inline-edit');
        const key = p ? p.dataset.inlineKey : null;

        li.remove();

        if (!key) return;

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ key, _delete: true }),
        }).catch(() => {});
    });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn             = document.getElementById('regenerate-offerte-button');
    const overlay         = document.getElementById('regenerate-overlay');
    const bar             = document.getElementById('regenerate-progress-bar');
    const barText         = document.getElementById('regenerate-progress-text');
    const confirmOverlay  = document.getElementById('regenerate-confirm-overlay');
    const confirmBtn      = confirmOverlay ? confirmOverlay.querySelector('[data-regenerate-confirm]') : null;
    const cancelBtn       = confirmOverlay ? confirmOverlay.querySelector('[data-regenerate-cancel]') : null;

    if (!btn) return;

    const endpoint = @json(route('offerte.regenerate', $offerte->public_uuid));
    const csrf = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : null;

    let progress      = 0;
    let progressTimer = null;
    let isRunning     = false;

    function setOverlayVisible(visible) {
        if (!overlay) return;

        if (visible) {
            overlay.classList.remove('pointer-events-none', 'opacity-0');
            overlay.classList.add('opacity-100');
        } else {
            overlay.classList.add('opacity-0', 'pointer-events-none');
            overlay.classList.remove('opacity-100');
        }
    }

    function setConfirmVisible(visible) {
        if (!confirmOverlay) return;

        if (visible) {
            confirmOverlay.classList.remove('pointer-events-none', 'opacity-0');
            confirmOverlay.classList.add('opacity-100');
        } else {
            confirmOverlay.classList.add('opacity-0', 'pointer-events-none');
            confirmOverlay.classList.remove('opacity-100');
        }
    }

    function updateProgress(value) {
        progress = Math.max(0, Math.min(100, value));
        if (bar) {
            bar.style.width = progress + '%';
        }
        if (barText) {
            barText.textContent = progress;
        }
    }

    function startProgressAnimation() {
        updateProgress(0);

        if (progressTimer) {
            clearInterval(progressTimer);
        }

        // Progress kruipt rustig naar ~90% totdat de server klaar is
        progressTimer = setInterval(function () {
            if (progress < 90) {
                updateProgress(progress + 1);
            }
        }, 150);
    }

    function stopProgressAnimation() {
        if (progressTimer) {
            clearInterval(progressTimer);
            progressTimer = null;
        }
    }

    function startRegeneration() {
        if (isRunning) return;
        isRunning = true;

        btn.classList.add('opacity-50', 'pointer-events-none');

        setOverlayVisible(true);
        startProgressAnimation();

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
        })
            .then(function (res) {
                if (!res.ok) {
                    throw new Error('Regeneratie mislukt');
                }
                return res.json();
            })
            .then(function (data) {
                stopProgressAnimation();
                updateProgress(100);

                if (window.showToast) {
                    window.showToast(
                        data.message || 'Offerte is opnieuw gegenereerd.',
                        'success'
                    );
                }

                // Heel korte delay zodat de user de 100% even ziet,
                // daarna reload voor de nieuwe AI-tekst
                setTimeout(function () {
                    window.location.reload();
                }, 600);
            })
            .catch(function () {
                stopProgressAnimation();
                setOverlayVisible(false);

                if (window.showToast) {
                    window.showToast('Er ging iets mis bij het opnieuw genereren van de offerte.', 'error');
                } else {
                    alert('Er ging iets mis bij het opnieuw genereren van de offerte.');
                }

                btn.classList.remove('opacity-50', 'pointer-events-none');
                isRunning = false;
            });
    }

    // 🔔 Klik op de regenerate-knop -> toon custom "Weet je het zeker?" dialoog
    btn.addEventListener('click', function (e) {
        e.preventDefault();

        // Als om wat voor reden dan ook de custom overlay ontbreekt, fallback naar browser confirm
        if (!confirmOverlay) {
            if (!confirm('Weet je het zeker? Alle handmatige wijzigingen (overrides) gaan verloren.')) {
                return;
            }
            startRegeneration();
            return;
        }

        setConfirmVisible(true);
    });

    // ❌ Annuleer in custom dialoog
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            setConfirmVisible(false);
        });
    }

    // ✅ Bevestig in custom dialoog
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            setConfirmVisible(false);
            startRegeneration();
        });
    }
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const sendBtn   = document.getElementById('send-offerte-button');
    const revokeBtn = document.getElementById('revoke-offerte-button');

    if (!sendBtn) return;

    const sendEndpoint   = @json(route('offerte.send', $offerte->public_uuid));
    const revokeEndpoint = @json(route('offerte.revoke', $offerte->public_uuid));
    const csrf = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        : null;

    let isSending = false;

    // ✅ Offerte versturen
    sendBtn.addEventListener('click', function (e) {
        e.preventDefault();
        if (isSending || sendBtn.dataset.disabled) return;

        if (!confirm('Weet je zeker dat je deze offerte wilt goedkeuren en naar de klant wilt versturen?')) {
            return;
        }

        isSending = true;
        sendBtn.classList.add('opacity-60', 'pointer-events-none');

        fetch(sendEndpoint, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            body: JSON.stringify({}),
        })
        .then(function (res) {
            if (!res.ok) {
                return res.json().catch(() => ({})).then(function (data) {
                    throw new Error(data.message || 'Versturen mislukt.');
                });
            }
            return res.json();
        })
        .then(function (data) {
            if (window.showToast) {
                window.showToast(
                    data.message || 'Gelukt! De offerte is succesvol verstuurd naar de klant en de status is veranderd naar "Te ondertekenen".',
                    'success'
                );
            }

            // Statusbadge bijwerken (rechts in de sidebar)
            if (data.status) {
                const statusBadge = document.querySelector('[data-offerte-status-label]');
                if (statusBadge && data.status === 'pending') {
                    statusBadge.textContent = 'Te ondertekenen';
                    statusBadge.className =
                        'px-2.5 py-0.5 rounded-full font-semibold text-[11px] bg-orange-100 text-orange-700';
                }
            }

            // Bovenbalk-label bijwerken als de offerte nu is verstuurd
            if (data.sent_at) {
                const editBadge = document.querySelector('[data-offerte-edit-status]');
                if (editBadge) {
                    editBadge.textContent = 'Offerte is opgestuurd naar de klant';
                    editBadge.className =
                        'px-2 py-0.5 text-xs bg-green-100 text-green-700 font-semibold rounded-full w-fit';
                }
            }

            // Knop direct "definitief" disablen na succesvol versturen
            sendBtn.classList.remove('opacity-60');
            sendBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            sendBtn.setAttribute('data-disabled', '1');

            // Revoke-knop tonen zodra hij verstuurd is
            if (revokeBtn && data.sent_at) {
                revokeBtn.classList.remove('hidden');
            }

            // Tijdlijn: "Verzonden naar klant" toevoegen of bijwerken
            if (data.sent_at) {
                const list = document.querySelector('[data-timeline-list]');
                const dateObj = new Date(data.sent_at);

                const pad = (n) => String(n).padStart(2, '0');
                const formatted =
                    `${pad(dateObj.getDate())}-${pad(dateObj.getMonth() + 1)}-${dateObj.getFullYear()} ` +
                    `${pad(dateObj.getHours())}:${pad(dateObj.getMinutes())}`;

                let sentItem = document.querySelector('[data-timeline-sent]');

                if (!sentItem && list) {
                    sentItem = document.createElement('li');
                    sentItem.className =
                        'flex items-center justify-between gap-2 text-[11px] text-[#215558]';
                    sentItem.setAttribute('data-timeline-sent', '1');
                    sentItem.innerHTML = `
                        <div class="flex items-center gap-2">
                            <span class="font-semibold leading-tight" data-timeline-date="sent">
                                ${formatted}
                            </span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full font-semibold text-[11px] bg-emerald-100 text-emerald-700">
                            Verzonden naar klant
                        </span>
                    `;
                    list.appendChild(sentItem);
                } else if (sentItem) {
                    const dateSpan = sentItem.querySelector('[data-timeline-date="sent"]');
                    if (dateSpan) {
                        dateSpan.textContent = formatted;
                    }
                }

                // Na versturen: offerte read-only maken
                if (window.makeOfferteReadOnly) {
                    window.makeOfferteReadOnly();
                }
            }
        })
        .catch(function (err) {
            if (window.showToast) {
                window.showToast(err.message || 'Versturen mislukt. Probeer het opnieuw.', 'error');
            } else {
                alert(err.message || 'Versturen mislukt.');
            }
        })
        .finally(function () {
            isSending = false;

            if (!sendBtn.dataset.disabled) {
                sendBtn.classList.remove('opacity-60', 'pointer-events-none');
            } else {
                sendBtn.classList.remove('opacity-60');
            }
        });
    });

    // ❌ Offerte intrekken
    if (revokeBtn) {
        revokeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            if (isSending) return;

            if (!confirm('Weet je zeker dat je deze offerte wilt intrekken? De klant kan de offerte dan niet meer bekijken of tekenen.')) {
                return;
            }

            isSending = true;
            revokeBtn.classList.add('opacity-60', 'pointer-events-none');

            fetch(revokeEndpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({}),
            })
            .then(function (res) {
                if (!res.ok) {
                    return res.json().catch(() => ({})).then(function (data) {
                        throw new Error(data.message || 'Intrekken mislukt.');
                    });
                }
                return res.json();
            })
            .then(function (data) {
                if (window.showToast) {
                    window.showToast(
                        data.message || 'De offerte is ingetrokken. De klant heeft geen toegang meer en jij kunt hem weer bewerken.',
                        'success'
                    );
                }

                // Kleine delay zodat de toast zichtbaar is, daarna alles opnieuw laden
                setTimeout(function () {
                    window.location.reload();
                }, 700);
            })
            .catch(function (err) {
                if (window.showToast) {
                    window.showToast(err.message || 'Intrekken mislukt. Probeer het opnieuw.', 'error');
                } else {
                    alert(err.message || 'Intrekken mislukt.');
                }
            })
            .finally(function () {
                isSending = false;
                revokeBtn.classList.remove('opacity-60', 'pointer-events-none');
            });
        });
    }
});
</script>
@endsection
