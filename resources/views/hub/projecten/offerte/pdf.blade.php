<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Offerte {{ $offerteNummer ?? '' }}</title>

    <style>
        @page {
            margin: 20mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: sans-serif;
            font-size: 11px;
            color: #215558;
            /* background-color: #f5f5f7; */
        }

        .wrapper {
            width: 100%;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding-top: 10px;
        }

        .card {
            background: #ffffff;
            border-radius: 16px;
            /* border: 1px solid #e5e7eb; */
            /* padding: 24px 28px; */
        }

        .row {
            width: 100%;
        }

        .row:after {
            content: "";
            display: block;
            clear: both;
        }

        .col-6 {
            float: left;
            width: 50%;
        }

        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }

        .heading-xs {
            font-size: 11px;
            font-weight: 700;
            margin: 0;
        }

        .heading-sm {
            font-size: 13px;
            font-weight: 800;
            margin: 0;
        }

        .heading-md {
            font-size: 16px;
            font-weight: 800;
            margin: 0;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .text-sm {
            font-size: 10px;
            line-height: 1.4;
            margin: 0;
        }

        .text-xs {
            font-size: 9px;
            line-height: 1.4;
            margin: 0;
        }

        .divider {
            border-top: 1px solid #e5e7eb;
            margin: 20px 0;
        }

        .badge-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 9px;
            font-weight: 600;
            border-radius: 999px;
            background: rgba(33, 85, 88, 0.08);
            margin: 2px 4px 2px 0;
        }

        .bullet-list {
            margin: 6px 0 0;
            padding-left: 12px;
        }

        .bullet-list li {
            margin-bottom: 4px;
        }

        .bullet {
            margin-right: 4px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        .table th,
        .table td {
            border-top: 1px solid #e5e7eb;
            padding: 6px 0;
            font-size: 10px;
            text-align: left;
        }

        .table th:last-child,
        .table td:last-child {
            text-align: right;
        }

        .pill {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 600;
            background: #bbf7d0;
            color: #166534;
        }

        .section {
            margin-top: 18px;
            page-break-inside: avoid;
        }

        .kpi-cols {
            width: 100%;
        }

        .kpi-cols td {
            padding-right: 16px;
            vertical-align: top;
        }

        .small-muted {
            font-size: 8px;
            color: #6b7280;
            margin-top: 4px;
        }
    </style>
</head>
<body>
@php
    /** @var \App\Models\Offerte $offerte */
    $offerteDate   = $offerteDate ?? ($offerte->created_at ?? now());
    $vervalDatum   = $vervalDatum ?? $offerteDate->copy()->addMonthNoOverflow();
    $offerteNummer = $offerteNummer
        ?? ($offerte->number
            ?? ('OF-' . $offerteDate->format('Ym') . str_pad($offerte->id ?? 1, 4, '0', STR_PAD_LEFT)));

    $summaryBullets       = data_get($offerte->generated, 'summary_bullets', []);
    $strongPoints         = data_get($offerte->generated, 'strong_points', []);
    $improvementPoints    = data_get($offerte->generated, 'improvement_points', []);
    $pageStructure        = data_get($offerte->generated, 'page_structure', []);
    $pageStructurePages   = data_get($pageStructure, 'pages', []);
    $pageStructureSummary = data_get($pageStructure, 'summary');
    $investment           = data_get($offerte->generated, 'investment', []);
    $rows                 = data_get($investment, 'rows', []);
    $totalSetup           = data_get($investment, 'total_setup_amount');
    $totalMonthly         = data_get($investment, 'total_monthly_amount');
@endphp

<div class="wrapper">
    <div class="container">
        <div class="card">

            {{-- HEADER --}}
            <div class="row">
                <div class="col-6">
                    <p class="heading-sm">Offerte</p>
                    <p class="heading-md mt-1">
                        {{ data_get($offerte->generated, 'headline', 'Website & online groei voor ' . ($project->company ?? 'jouw bedrijf')) }}
                    </p>
                </div>
                <div class="col-6" style="text-align:right;">
                    <p class="heading-sm" style="color:#21c2d3;">
                        eazy<span style="color:#19203c;">online</span>
                    </p>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-6">
                    <p class="heading-xs">Offertenummer</p>
                    <p class="text-sm">{{ $offerteNummer }}</p>

                    <p class="heading-xs mt-3">Offertedatum</p>
                    <p class="text-sm">{{ $offerteDate->format('d/m/Y') }}</p>

                    <p class="heading-xs mt-3">Vervaldatum</p>
                    <p class="text-sm">{{ $vervalDatum->format('d/m/Y') }}</p>
                </div>
                <div class="col-6">
                    <p class="heading-xs">eazyonline</p>
                    <p class="text-sm">Mercatorweg 28</p>
                    <p class="text-sm">6827 DC Arnhem</p>
                    <p class="text-sm">KVK: 67228550</p>
                    <p class="text-sm">BTW: NL864926856B01</p>

                    <p class="heading-xs mt-3">Offerte voor</p>
                    <p class="text-sm">{{ $project->company ?? 'Relatie' }}</p>
                    @if($project->address)
                        <p class="text-sm">{{ $project->address }}</p>
                    @else
                        <p class="text-sm">Adres onbekend</p>
                    @endif
                </div>
            </div>

            <div class="divider"></div>

            {{-- SAMENVATTING --}}
            <div class="section">
                <p class="section-title">Samenvatting van het voorstel</p>
                <p class="text-sm">
                    {{ data_get($offerte->generated, 'summary_paragraph') }}
                </p>

                @if(!empty($summaryBullets))
                    <ul class="badge-list mt-3">
                        @foreach($summaryBullets as $bullet)
                            <li class="badge">• {{ $bullet }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="divider"></div>

            {{-- OVER EAZYONLINE + REVIEWS --}}
            <div class="section">
                <p class="section-title">Over Eazyonline</p>
                <p class="text-sm">
                    We helpen ondernemers groeien met resultaatgerichte websites en online marketing. Met meer dan 300 projecten
                    voor o.a. AfroBros, The Grind en Face Experts weten we wat werkt voor conversiegerichte webshops.
                </p>

                <table class="kpi-cols mt-3">
                    <tr>
                        <td>
                            <p class="text-xs"><strong>★★★★★ Roy Koenders</strong><br>Eigenaar 2BeFit Coaching</p>
                            <p class="text-xs mt-2">
                                Van idee tot eindproduct: Eazy leverde een strak, modern en uniek design dat onze visie perfect weerspiegelt.
                            </p>
                        </td>
                        <td>
                            <p class="text-xs"><strong>★★★★★ Baris Yildirim</strong><br>Eigenaar Barbaros Detailing</p>
                            <p class="text-xs mt-2">
                                Binnen no-time hadden we een op maat gemaakte website die precies laat zien waar ons bedrijf voor staat. Supertevreden.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:10px;">
                            <p class="text-xs"><strong>★★★★★ Donny Roelvink</strong><br>Eigenaar The Grind</p>
                            <p class="text-xs mt-2">
                                Eazy heeft elke versie van onze website naar een hoger niveau getild. Ze snappen exact wat je als ondernemer nodig hebt.
                            </p>
                        </td>
                        <td style="padding-top:10px;">
                            <p class="text-xs"><strong>★★★★★ Wouter Smith</strong><br>Eigenaar KapotSterk</p>
                            <p class="text-xs mt-2">
                                Samenwerken met Eazy voelt als een gedeeld avontuur. Ze denken altijd mee en bouwen écht mee aan ons merk.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding-top:10px;">
                            <p class="text-xs"><strong>★★★★★ Nienke Roseboom</strong><br>Eigenaresse Huisje Kaatsheuvel</p>
                            <p class="text-xs mt-2">
                                Vanaf dag één goede communicatie, snelle updates en een team dat je écht meeneemt in het proces. Heel professioneel.
                            </p>
                        </td>
                        <td style="padding-top:10px;">
                            <p class="text-xs"><strong>★★★★★ Bas &amp; David</strong><br>Eigenaren BlowerTechnic</p>
                            <p class="text-xs mt-2">
                                Onze oude websites voldeden niet meer aan onze visie. Eazy ontwikkelde een volledig nieuw concept dat onze verwachtingen overtrof.
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            {{-- ANALYSE & UITDAGINGEN --}}
            <div class="section">
                <p class="section-title">Analyse &amp; uitdagingen</p>
                <table class="kpi-cols">
                    <tr>
                        <td width="50%">
                            <p class="heading-xs">Sterke punten</p>
                            @if(!empty($strongPoints))
                                <ul class="bullet-list">
                                    @foreach($strongPoints as $point)
                                        <li><span class="bullet">•</span><span class="text-xs">{{ $point }}</span></li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                        <td width="50%">
                            <p class="heading-xs">Verbeterpunten</p>
                            @if(!empty($improvementPoints))
                                <ul class="bullet-list">
                                    @foreach($improvementPoints as $point)
                                        <li><span class="bullet">•</span><span class="text-xs">{{ $point }}</span></li>
                                    @endforeach
                                </ul>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            <div class="divider"></div>

            {{-- WAT JE VAN ONS KRIJGT --}}
            <div class="section">
                <p class="section-title">Wat je van ons krijgt</p>
                <p class="text-sm">
                    In deze offerte leveren we een complete online oplossing waarmee jullie merk professioneel, snel en schaalbaar
                    online kan groeien. Hieronder de belangrijkste onderdelen op een rij.
                </p>

                <table class="table">
                    <tbody>
                    <tr>
                        <td>Volledig maatwerk ontwerp afgestemd op jullie merk, doelgroep en positionering.</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    <tr>
                        <td>Conversiegerichte pagina’s (bijvoorbeeld: Home, Diensten, Over ons, Contact).</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    <tr>
                        <td>Technische basis voor SEO (laadsnelheid, structuur, metadata, basis redirects).</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    <tr>
                        <td>Koppelingen met belangrijke tools (bijvoorbeeld: betaalprovider, e-mailmarketing, statistieken).</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    <tr>
                        <td>Gebruiksvriendelijk beheer zodat jullie zelf content, producten en pagina’s kunnen beheren.</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    <tr>
                        <td>Begeleiding bij livegang en korte training in het gebruik van de omgeving.</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="divider"></div>

            {{-- AANPAK & PLANNING --}}
            <div class="section">
                <p class="section-title">Aanpak &amp; planning</p>
                <p class="text-sm">
                    We werken met een duidelijke, voorspelbare aanpak. Zo weet je precies wat je wanneer kunt verwachten en welke
                    input we op welk moment nodig hebben.
                </p>

                <table class="kpi-cols mt-3">
                    <tr>
                        <td width="50%">
                            <p class="heading-xs">Fase 1 – Strategie &amp; kick-off</p>
                            <p class="text-xs mt-1">
                                Gezamenlijke sessie(s) om doelen, doelgroep, positionering en functionaliteiten scherp te krijgen.
                                We vertalen dit naar een concreet plan van aanpak.
                            </p>
                        </td>
                        <td width="50%">
                            <p class="heading-xs">Fase 2 – Design &amp; concept</p>
                            <p class="text-xs mt-1">
                                Uitwerking van het visuele ontwerp (desktop &amp; mobiel), inclusief feedbackronde(s).
                                Na akkoord zetten we het design door naar de bouw.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%" style="padding-top:10px;">
                            <p class="heading-xs">Fase 3 – Bouw &amp; inrichting</p>
                            <p class="text-xs mt-1">
                                Technische realisatie, contentinvoer en koppelingen (betaalprovider, formulieren, tracking).
                                We leveren een testomgeving op om samen door te lopen.
                            </p>
                        </td>
                        <td width="50%" style="padding-top:10px;">
                            <p class="heading-xs">Fase 4 – Testen, livegang &amp; nazorg</p>
                            <p class="text-xs mt-1">
                                Laatste checks, livegang en overdracht. Eventuele puntjes op de i verwerken we na livegang in overleg.
                            </p>
                        </td>
                    </tr>
                </table>
            </div>

            {{-- PAGINA-STRUCTUUR --}}
            @if(!empty($pageStructurePages))
                <div class="divider"></div>

                <div class="section">
                    <p class="section-title">Voorstel paginastructuur</p>

                    @if($pageStructureSummary)
                        <p class="text-sm">
                            {{ $pageStructureSummary }}
                        </p>
                    @endif

                    @foreach($pageStructurePages as $page)
                        <div class="section" style="margin-top:10px;">
                            <p class="heading-xs">
                                {{ data_get($page, 'title') }}
                            </p>

                            @if(data_get($page, 'goal'))
                                <p class="text-xs mt-1">{{ data_get($page, 'goal') }}</p>
                            @endif

                            @php $sections = data_get($page, 'key_sections', []); @endphp

                            @if(!empty($sections))
                                <ul class="bullet-list">
                                    @foreach($sections as $section)
                                        <li>
                                            <span class="bullet">•</span>
                                            <span class="text-xs">{{ $section }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="divider"></div>

            {{-- INVESTERING --}}
            <div class="section">
                <p class="section-title">Investering</p>
                <p class="text-sm">
                    Hieronder zie je de investering uitgesplitst. Bedragen zijn exclusief btw en gebaseerd op de beschreven scope
                    en het best passende pakket.
                </p>

                @if(!empty($rows))
                    <table class="table">
                        <tbody>
                        @foreach($rows as $row)
                            <tr>
                                <td>{{ data_get($row, 'label') }}</td>
                                <td>{{ data_get($row, 'amount') }}</td>
                            </tr>
                        @endforeach

                        @if($totalSetup)
                            <tr>
                                <th>Totaal eenmalig</th>
                                <th>{{ $totalSetup }}</th>
                            </tr>
                        @endif

                        @if($totalMonthly)
                            <tr>
                                <th>Per maand</th>
                                <th>{{ $totalMonthly }}</th>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                @else
                    <p class="text-xs mt-2">
                        De exacte investering wordt toegevoegd zodra de scope en het pakket definitief zijn.
                    </p>
                @endif

                <p class="text-xs mt-2">
                    Extra pagina's nodig? Geen probleem! Voor € 195,- bouwen wij een extra pagina, volledig naar wens.
                </p>
                <p class="small-muted">
                    * Bovenstaande bedragen zijn indicatief en worden definitief op basis van de gekozen opties
                    en eventuele aanvullende wensen.
                </p>
            </div>

            <div class="divider"></div>

            {{-- SUPPORT & ONDERHOUD --}}
            <div class="section">
                <p class="section-title">Support, onderhoud &amp; groei</p>
                <p class="text-sm">
                    Na livegang laten we je niet los. We zorgen dat de techniek veilig, snel en up-to-date blijft,
                    én dat je altijd bij ons terechtkunt met vragen of ideeën.
                </p>

                <table class="table">
                    <tbody>
                    <tr>
                        <td>Beveiligde hostingomgeving, monitoring en basis back-ups.</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    <tr>
                        <td>Technische updates en onderhoud van de website / webshop.</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    <tr>
                        <td>Toegang tot ons supportportaal voor vragen en wijzigingsverzoeken.</td>
                        <td><span class="pill">Inbegrepen</span></td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="divider"></div>

            {{-- RANDVOORWAARDEN --}}
            <div class="section">
                <p class="section-title">Randvoorwaarden &amp; scope</p>
                <p class="text-sm">
                    Om de samenwerking soepel te laten verlopen, maken we duidelijke afspraken over scope, oplevering en verantwoordelijkheden.
                </p>

                <ul class="bullet-list">
                    <li>
                        <span class="bullet">•</span>
                        <span class="text-xs">
                            Deze offerte is gebaseerd op de besproken wensen en uitgangspunten. Grote wijzigingen in scope
                            kunnen invloed hebben op planning en investering.
                        </span>
                    </li>
                    <li>
                        <span class="bullet">•</span>
                        <span class="text-xs">
                            Content (teksten, foto’s, video’s) wordt aangeleverd door de opdrachtgever, tenzij anders overeengekomen.
                        </span>
                    </li>
                    <li>
                        <span class="bullet">•</span>
                        <span class="text-xs">
                            Extra werkzaamheden buiten de afgesproken scope vallen onder meerwerk en worden vooraf afgestemd
                            op basis van ons uurtarief.
                        </span>
                    </li>
                    <li>
                        <span class="bullet">•</span>
                        <span class="text-xs">
                            Op deze offerte zijn de algemene voorwaarden van Eazyonline van toepassing.
                        </span>
                    </li>
                </ul>
            </div>

            <div class="divider"></div>

            {{-- DOELEN & KPI'S --}}
            <div class="section">
                <p class="section-title">Doelen &amp; KPI's</p>
                <p class="text-sm">
                    We denken niet alleen in pixels, maar vooral in resultaat. Samen bepalen we concrete doelen en sturen we op meetbare KPI’s.
                </p>

                <ul class="bullet-list">
                    <li><span class="bullet">•</span><span class="text-xs">Meer relevante bezoekers via organische zoekresultaten (SEO).</span></li>
                    <li><span class="bullet">•</span><span class="text-xs">Stijging in conversies (aanvragen, bestellingen of afspraken).</span></li>
                    <li><span class="bullet">•</span><span class="text-xs">Betere inzichtelijkheid in prestaties via duidelijke rapportages en dashboards.</span></li>
                    <li><span class="bullet">•</span><span class="text-xs">Kortere doorlooptijd van eerste bezoek tot klant.</span></li>
                </ul>
            </div>

        </div>
    </div>
</div>
</body>
</html>