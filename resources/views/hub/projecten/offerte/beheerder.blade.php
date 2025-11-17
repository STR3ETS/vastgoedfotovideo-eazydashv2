@extends('hub.projecten.offerte.layouts.guest')

@section('content')

@php
    /** @var \App\Models\Offerte $offerte */
    $offerteDate   = $offerte->created_at ?? now();  // offertedatum
    $vervalDatum   = $offerteDate->copy()->addMonthNoOverflow(); // +1 maand
    $offerteNummer = $offerte->number
        ?? ('OF-' . $offerteDate->format('Ym') . str_pad($offerte->id ?? 1, 4, '0', STR_PAD_LEFT));
@endphp

<div class="w-full fixed z-50 top-0 left-0 bg-white border-b border-b-gray-200 p-4 min-h-[61px] flex items-center">
    <div class="max-w-6xl w-full mx-auto flex items-center justify-between gap-2">
        <div class="flex items-center gap-4 relative">
            <a href="{{ route('offerte.download', $offerte->public_uuid) }}"
            class="w-7 h-7 bg-gray-200 hover:bg-gray-300 rounded-full flex items-center justify-center transition duration-200 relative group cursor-pointer">
                <i class="fa-solid fa-download text-[#215558] text-xs"></i>
                <div class="flex items-center p-2 rounded-xl bg-white border border-gray-200 shadow-md absolute left-full top-1/2 ml-2 -translate-y-1/2 opacity-0 invisible translate-x-1 pointer-events-none group-hover:opacity-100 group-hover:visible group-hover:translate-x-0 group-hover:pointer-events-auto transition-all duration-200 ease-out z-10">
                    <p class="text-[#215558] text-[11px] font-semibold whitespace-nowrap">
                        Downloaden als PDF
                    </p>
                </div>
            </a>
            <p class="caveat-font text-[#215558] text-lg animate-pulse">Download hier de offerte!</p>
            <svg class="absolute h-fit -left-10.5 rotate-[-45deg] -top-7 w-[30px]" width="177" height="265" viewBox="0 0 177 265" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M31.6609 252.913C26.9982 250.913 22.3461 248.492 17.4805 247.088C15.266 246.47 11.1171 246.988 10.217 248.494C8.08411 251.854 10.6409 254.327 14.0978 255.293C21.7134 257.419 29.3655 259.384 37.0227 261.4C38.6394 261.816 40.381 261.905 41.8624 262.544C48.8485 265.729 51.589 264.249 51.6232 256.693C51.6948 246.877 51.7664 237.061 51.1101 227.318C50.9342 225.028 48.1646 221.475 46.1268 221.048C41.838 220.165 40.7551 224.049 40.6913 227.622C40.6113 232.613 40.7392 237.584 40.8094 244.081C31.5548 232.362 25.3988 220.65 21.0472 208.075C3.36551 157.123 10.508 108.155 34.2655 60.992C41.1492 47.3499 51.8891 37.1 67.8978 34.4025C71.6673 33.7645 75.5975 34.2119 80.6821 34.1246C71.8231 46.9145 65.6677 59.4348 63.7071 73.4778C62.6592 80.8731 62.209 88.4712 62.7883 95.8617C63.5022 104.603 67.3931 112.03 76.5993 114.836C85.9718 117.731 94.073 114.72 100.476 107.84C110.145 97.3823 114.054 84.4564 114.19 70.543C114.389 53.5809 107.61 39.8323 93.0418 29.5878C116.93 6.38264 144.856 -0.0207063 176.447 6.11382C176.552 5.57892 176.651 4.99207 176.755 4.45717C174.852 3.8076 173.038 2.99186 171.052 2.5604C141.042 -4.04644 114.348 2.60123 91.2768 22.945C88.3332 25.5463 86.0505 26.3509 82.0166 25.3893C67.1178 21.8392 53.5625 25.9706 42.3548 35.7427C35.6875 41.5477 29.238 48.485 25.2888 56.2745C-0.071148 106.325 -7.95309 158.409 11.3911 212.342C16.4024 226.267 24.5595 239.092 31.5672 253.027L31.6609 252.913ZM86.514 37.3197C113.807 53.0095 106.726 91.0091 92.2112 103.419C82.6314 111.612 71.9181 107.383 70.3013 94.3259C67.7364 73.3384 74.1647 54.6013 86.5088 37.2678L86.514 37.3197Z" fill="#215558"></path>
            </svg>
        </div>
        <div class="flex items-center gap-2">
            <p id="offerte-countdown"
            data-expiry="{{ $vervalDatum->toIso8601String() }}"
            class="px-2 py-0.5 text-xs bg-green-200 text-green-700 font-semibold rounded-full w-fit">
                00:00:00:00
            </p>

            <p class="px-2 py-0.5 text-xs bg-gray-200 text-gray-700 font-semibold rounded-full w-fit">
                Beschikbaar tot: {{ $vervalDatum->format('d/m/Y H:i') }}
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

                <h1 class="text-2xl -mt-2 text-[#215558] font-black leading-tight shrink-0 max-w-[50%]">
                    {{ data_get($offerte->generated, 'headline', 'Website & online groei voor ' . ($project->company ?? 'jouw bedrijf')) }}
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
                    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                        {{ data_get($offerte->generated, 'summary_paragraph') }}
                    </p>
                    @php
                        $summaryBullets = data_get($offerte->generated, 'summary_bullets', []);
                    @endphp
                    @if(!empty($summaryBullets))
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($summaryBullets as $bullet)
                                <span class="w-full px-2.5 py-0.5 text-xs bg-[#215558]/20 text-[#215558] font-semibold rounded-full flex items-center gap-2 leading-tighter">
                                    <i class="fa-solid fa-check fa-xs mt-0.5"></i>
                                    {{ $bullet }}
                                </span>
                            @endforeach
                        </div>
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
                        @php
                            $strongPoints = data_get($offerte->generated, 'strong_points', []);
                        @endphp
                        <ul class="text-xs grid gap-2">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Sterke punten</p>
                            </li>

                            @foreach($strongPoints as $point)
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                                    <p class="text-xs text-[#215558] font-semibold leading-tight">
                                        {{ $point }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                        @php
                            $improvementPoints = data_get($offerte->generated, 'improvement_points', []);
                        @endphp
                        <ul class="text-xs grid gap-2">
                            <li>
                                <p class="text-sm text-[#215558] font-black leading-tight truncate">Verbeterpunten</p>
                            </li>

                            @foreach($improvementPoints as $point)
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                                    <p class="text-xs text-[#215558] font-semibold leading-tight">
                                        {{ $point }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

<hr class="border-gray-200">

{{-- Scope & deliverables --}}
<div class="grid gap-4">
    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Wat je van ons krijgt</p>
    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
        In deze offerte leveren we een complete online oplossing waarmee jullie merk professioneel, snel en schaalbaar online kan groeien. Hieronder de belangrijkste onderdelen op een rij.
    </p>
    <div class="flex flex-col gap-2">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Volledig maatwerk ontwerp afgestemd op jullie merk, doelgroep en positionering.
                </p>
            </div>
            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Conversiegerichte pagina’s (bijvoorbeeld: Home, Diensten, Over ons, Contact).
                </p>
            </div>
            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Technische basis voor SEO (laadsnelheid, structuur, metadata, basis redirects).
                </p>
            </div>
            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Koppelingen met belangrijke tools (bijvoorbeeld: betaalprovider, e-mailmarketing, statistieken).
                </p>
            </div>
            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Gebruiksvriendelijk beheer zodat jullie zelf content, producten en pagina’s kunnen beheren.
                </p>
            </div>
            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
        </div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-check fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Begeleiding bij livegang en korte training in het gebruik van de omgeving.
                </p>
            </div>
            <div class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold text-green-700 bg-green-100">Inbegrepen</div>
        </div>
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
        <div class="text-xs grid gap-2 h-fit">
            <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Fase 1 – Strategie & kick-off</p>
            <p class="text-xs text-[#215558] font-semibold leading-tight">
                Gezamenlijke sessie(s) om doelen, doelgroep, positionering en functionaliteiten scherp te krijgen. We vertalen dit naar een concreet plan van aanpak.
            </p>
        </div>

        <div class="text-xs grid gap-2 h-fit">
            <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Fase 2 – Design & concept</p>
            <p class="text-xs text-[#215558] font-semibold leading-tight">
                Uitwerking van het visuele ontwerp (desktop & mobiel), inclusief feedbackronde(s). Na akkoord zetten we het design door naar de bouw.
            </p>
        </div>

        <div class="text-xs grid gap-2 h-fit">
            <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Fase 3 – Bouw & inrichting</p>
            <p class="text-xs text-[#215558] font-semibold leading-tight">
                Technische realisatie, contentinvoer en koppelingen (betaalprovider, formulieren, tracking). We leveren een testomgeving op om samen door te lopen.
            </p>
        </div>

        <div class="text-xs grid gap-2 h-fit">
            <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">Fase 4 – Testen, livegang & nazorg</p>
            <p class="text-xs text-[#215558] font-semibold leading-tight">
                Laatste checks, livegang en overdracht. Eventuele puntjes op de i verwerken we na livegang in overleg.
            </p>
        </div>
    </div>
</div>

<hr class="border-gray-200">

{{-- Pagina-structuur --}}
@php
    $pageStructure       = data_get($offerte->generated, 'page_structure', []);
    $pageStructurePages  = data_get($pageStructure, 'pages', []);
    $pageStructureSummary = data_get($pageStructure, 'summary');
@endphp

@if(!empty($pageStructurePages))
    <div class="grid gap-4">
        <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">
            Voorstel paginastructuur
        </p>

        @if($pageStructureSummary)
            <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                {{ $pageStructureSummary }}
            </p>
        @endif

        <div class="grid grid-cols-1 gap-4">
            @foreach($pageStructurePages as $page)
                <div class="border border-gray-200 rounded-2xl p-4 text-xs grid gap-4">
                    <div class="flex flex-col gap-2">
                        <p class="text-sm text-[#215558] font-black leading-tight truncate shrink-0">
                            {{ data_get($page, 'title') }}
                        </p>

                        @if(data_get($page, 'goal'))
                            <span class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
                                {{ data_get($page, 'goal') }}
                            </span>
                        @endif
                    </div>

                    @php
                        $sections = data_get($page, 'key_sections', []);
                    @endphp

                    @if(!empty($sections))
                        <ul class="grid gap-1">
                            @foreach($sections as $section)
                                <li class="flex items-center gap-2">
                                    <i class="fa-solid fa-circle text-[5px] text-[#215558]"></i>
                                    <p class="text-xs text-[#215558] font-semibold leading-tight">
                                        {{ $section }}
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <hr class="border-gray-200">
@endif

{{-- Investering --}}
<div class="grid gap-4">
    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Investering</p>
    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
        Hieronder zie je de investering uitgesplitst. Bedragen zijn exclusief btw en gebaseerd op de beschreven scope en het best passende pakket.
    </p>

    @php
        $investment   = data_get($offerte->generated, 'investment', []);
        $rows         = data_get($investment, 'rows', []);
        $packageName  = data_get($investment, 'package_name');
        $whyPackage   = data_get($investment, 'why_this_package');
        $totalSetup   = data_get($investment, 'total_setup_amount');
        $totalMonthly = data_get($investment, 'total_monthly_amount');
    @endphp

    @if(!empty($rows))
        <div class="border border-gray-200 rounded-2xl divide-y divide-gray-200 overflow-hidden">
            @foreach($rows as $row)
                <div class="flex items-center justify-between px-4 py-3 text-xs">
                    <p class="text-[#215558] font-semibold">
                        {{ data_get($row, 'label') }}
                    </p>
                    <p class="text-[#215558] font-black">
                        {{ data_get($row, 'amount') }}
                    </p>
                </div>
            @endforeach

            {{-- Totaalregels uit de JSON (al als nette tekst aangeleverd) --}}
            @if($totalSetup)
                <div class="flex items-center justify-between px-4 py-3 bg-[#215558]/5">
                    <p class="text-xs text-[#215558] font-black">Totaal eenmalig</p>
                    <p class="text-sm text-[#215558] font-black">{{ $totalSetup }}</p>
                </div>
            @endif

            @if($totalMonthly)
                <div class="flex items-center justify-between px-4 py-3 bg-[#215558]/5">
                    <p class="text-xs text-[#215558] font-black">Per maand</p>
                    <p class="text-sm text-[#215558] font-black">{{ $totalMonthly }}</p>
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

<hr class="border-gray-200">

{{-- Doelen & KPI's --}}
<div class="grid gap-4">
    <p class="text-xl text-[#215558] font-black leading-tight truncate shrink-0">Doelen & KPI's</p>
    <p class="text-xs text-[#215558] font-semibold leading-tight max-w-[75%] shrink-0">
        We denken niet alleen in pixels, maar vooral in resultaat. Samen bepalen we concrete doelen en sturen we op meetbare KPI’s.
    </p>

    <div class="grid grid-cols-2 gap-4 text-xs">
        <ul class="grid gap-2">
            <li class="flex items-center gap-2">
                <i class="fa-solid fa-bullseye fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Meer relevante bezoekers via organische zoekresultaten (SEO).
                </p>
            </li>
            <li class="flex items-center gap-2">
                <i class="fa-solid fa-bullseye fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Stijging in conversies (aanvragen, bestellingen of afspraken).
                </p>
            </li>
        </ul>
        <ul class="grid gap-2">
            <li class="flex items-center gap-2">
                <i class="fa-solid fa-bullseye fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Betere inzichtelijkheid in prestaties via duidelijke rapportages en dashboards.
                </p>
            </li>
            <li class="flex items-center gap-2">
                <i class="fa-solid fa-bullseye fa-xs mt-0.5 text-[#215558]"></i>
                <p class="text-xs text-[#215558] font-semibold leading-tight">
                    Kortere doorlooptijd van eerste bezoek tot klant.
                </p>
            </li>
        </ul>
    </div>
</div>

            </div>
            <div class="bg-white rounded-2xl p-6 border border-gray-200"></div>
        </div>
    </div>
</div>

{{-- Bottom bar (nog leeg) --}}
<div class="w-full fixed z-50 bottom-0 left-0 bg-white border-b border-b-gray-200 p-4">
    <div class="max-w-6xl mx-auto flex items-center gap-2">
        <a href="#"
           class="bg-[#0F9B9F] hover:bg-[#215558] cursor-pointer text-center text-white text-base font-semibold px-6 py-3 rounded-full transition duration-300">
            Offerte ondertekenen
        </a>
        <a href="#"
           class="bg-gray-200 hover:bg-gray-300 text-gray-700 cursor-pointer font-semibold px-6 py-3 rounded-full transition duration-300">
            Bellen met een medewerker
        </a>
    </div>
</div>

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
@endsection
