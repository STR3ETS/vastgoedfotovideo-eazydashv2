<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Groepen (thema's)
    |--------------------------------------------------------------------------
    |
    | Hoofdthema's waarin we issues groeperen. Dit bepaalt hoe de medewerker
    | de problemen ziet en in welke volgorde hij of zij gaat werken.
    |
    */

    'groups' => [
        'technical' => [
            'label'       => 'Techniek',
            'description' => 'Crawlbaarheid, statuscodes, indexatie en performance.',
        ],
        'content' => [
            'label'       => 'Content en meta',
            'description' => 'Titels, descriptions, headings en duplicate content.',
        ],
        'links' => [
            'label'       => 'Interne links en autoriteit',
            'description' => 'Interne linkstructuur, dode links en backlinks.',
        ],
        'ux' => [
            'label'       => 'UX en snelheid',
            'description' => 'Laadtijd, mobielvriendelijkheid en core web vitals.',
        ],
        'other' => [
            'label'       => 'Overig',
            'description' => 'Issues die niet netjes in een categorie vallen.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Issues mapping
    |--------------------------------------------------------------------------
    |
    | Mapping van "issue keys" die in SeoAuditResult.key staan naar:
    | - groep
    | - leesbare label
    | - standaard severity en prioriteit
    | - impact score (0 tot 100)
    | - quick_win vlag
    | - lijst met actie codes (zie actions onderaan)
    |
    | Belangrijk:
    | - de keys hier moeten matchen met SeoAuditResult->key
    | - je kunt dit later eenvoudig uitbreiden
    |
    */

    'issues' => [

        // TECHNISCH

        '4xx_page' => [
            'group'            => 'technical',
            'label'            => 'Kapotte pagina’s (4xx fouten)',
            'default_severity' => 'critical',
            'default_priority' => 'high',
            'impact'           => 95,
            'quick_win'        => false,
            'actions'          => ['fix_4xx_pages', 'setup_redirects'],
        ],

        '5xx_page' => [
            'group'            => 'technical',
            'label'            => 'Serverfouten (5xx)',
            'default_severity' => 'critical',
            'default_priority' => 'high',
            'impact'           => 100,
            'quick_win'        => false,
            'actions'          => ['check_server_stability'],
        ],

        'slow_pages' => [
            'group'            => 'ux',
            'label'            => 'Langzame pagina’s',
            'default_severity' => 'warning',
            'default_priority' => 'high',
            'impact'           => 80,
            'quick_win'        => false,
            'actions'          => ['improve_page_speed'],
        ],

        'http_to_https_redirects' => [
            'group'            => 'technical',
            'label'            => 'Http naar https redirects',
            'default_severity' => 'warning',
            'default_priority' => 'medium',
            'impact'           => 60,
            'quick_win'        => true,
            'actions'          => ['enforce_https'],
        ],

        // CONTENT

        'missing_title' => [
            'group'            => 'content',
            'label'            => 'Ontbrekende title tags',
            'default_severity' => 'critical',
            'default_priority' => 'high',
            'impact'           => 90,
            'quick_win'        => true,
            'actions'          => ['write_missing_titles'],
        ],

        'duplicate_title' => [
            'group'            => 'content',
            'label'            => 'Dubbele title tags',
            'default_severity' => 'warning',
            'default_priority' => 'medium',
            'impact'           => 75,
            'quick_win'        => true,
            'actions'          => ['fix_duplicate_titles'],
        ],

        'missing_meta_description' => [
            'group'            => 'content',
            'label'            => 'Ontbrekende meta descriptions',
            'default_severity' => 'warning',
            'default_priority' => 'medium',
            'impact'           => 70,
            'quick_win'        => true,
            'actions'          => ['write_missing_meta_descriptions'],
        ],

        'duplicate_meta_description' => [
            'group'            => 'content',
            'label'            => 'Dubbele meta descriptions',
            'default_severity' => 'warning',
            'default_priority' => 'medium',
            'impact'           => 65,
            'quick_win'        => true,
            'actions'          => ['fix_duplicate_meta_descriptions'],
        ],

        'thin_content' => [
            'group'            => 'content',
            'label'            => 'Te weinig content op belangrijke pagina’s',
            'default_severity' => 'warning',
            'default_priority' => 'high',
            'impact'           => 80,
            'quick_win'        => false,
            'actions'          => ['plan_content_improvements'],
        ],

        // LINKS

        'broken_internal_links' => [
            'group'            => 'links',
            'label'            => 'Kapotte interne links',
            'default_severity' => 'warning',
            'default_priority' => 'high',
            'impact'           => 85,
            'quick_win'        => true,
            'actions'          => ['fix_broken_internal_links'],
        ],

        'missing_internal_links_to_key_pages' => [
            'group'            => 'links',
            'label'            => 'Te weinig interne links naar belangrijke pagina’s',
            'default_severity' => 'warning',
            'default_priority' => 'high',
            'impact'           => 80,
            'quick_win'        => false,
            'actions'          => ['improve_internal_link_structure'],
        ],

        'orphan_pages' => [
            'group'            => 'links',
            'label'            => 'Orphan pages (geen interne links)',
            'default_severity' => 'warning',
            'default_priority' => 'medium',
            'impact'           => 70,
            'quick_win'        => false,
            'actions'          => ['handle_orphan_pages'],
        ],

        // UX

        'not_mobile_friendly' => [
            'group'            => 'ux',
            'label'            => 'Niet mobielvriendelijke pagina’s',
            'default_severity' => 'critical',
            'default_priority' => 'high',
            'impact'           => 95,
            'quick_win'        => false,
            'actions'          => ['improve_mobile_experience'],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Acties
    |--------------------------------------------------------------------------
    |
    | Actie definities. Hier koppel je aan elke actie code:
    | - label
    | - category (technical, content, links, ux)
    | - owner (developer, content, seo, generalist)
    | - impact en effort (low, medium, high)
    | - beschrijving
    | - optionele checklist voor de medewerker
    |
    */

    'actions' => [

        'fix_4xx_pages' => [
            'label'       => 'Herstel kapotte pagina’s of stel redirects in',
            'category'    => 'technical',
            'owner'       => 'developer',
            'impact'      => 'high',
            'effort'      => 'medium',
            'description' => 'Loop alle 4xx pagina’s na en herstel de oorzaak of stel een passende redirect in naar een relevante pagina.',
            'checklist'   => [
                'Exporteer lijst met 4xx pagina’s uit SERanking',
                'Controleer of de content nog nodig is',
                'Herstel de pagina of stel een 301 redirect in',
                'Test de belangrijkste URL’s in de browser',
            ],
        ],

        'setup_redirects' => [
            'label'       => 'Redirects instellen voor verplaatste content',
            'category'    => 'technical',
            'owner'       => 'developer',
            'impact'      => 'high',
            'effort'      => 'low',
            'description' => 'Zorg dat oude URL’s netjes doorverwijzen naar de nieuwe pagina’s zodat autoriteit en bezoekers niet verloren gaan.',
            'checklist'   => [
                'Bepaal voor elke oude URL de juiste nieuwe bestemming',
                'Leg de redirect vast in het CMS of op serverniveau',
                'Controleer of er geen redirect loops ontstaan',
            ],
        ],

        'check_server_stability' => [
            'label'       => 'Onderzoek serverfouten',
            'category'    => 'technical',
            'owner'       => 'developer',
            'impact'      => 'high',
            'effort'      => 'medium',
            'description' => 'Serverfouten zorgen voor een slechte gebruikerservaring en kunnen indexatie blokkeren. Controleer logbestanden en hosting.',
            'checklist'   => [
                'Controleer server logs op 5xx fouten',
                'Stem af met hostingpartij als het structureel is',
                'Test applicatie routes en zware pagina’s',
            ],
        ],

        'improve_page_speed' => [
            'label'       => 'Laadtijd van trage pagina’s verbeteren',
            'category'    => 'ux',
            'owner'       => 'developer',
            'impact'      => 'high',
            'effort'      => 'medium',
            'description' => 'Optimaliseer afbeeldingen, scripts en caching op de belangrijkste trage pagina’s.',
            'checklist'   => [
                'Identificeer traagste pagina’s in SERanking rapport',
                'Optimaliseer afbeeldingen (webp, compressie, lazy loading)',
                'Verminder zware scripts en third party scripts',
                'Zet caching en gzip / brotli aan waar mogelijk',
            ],
        ],

        'enforce_https' => [
            'label'       => 'Https afdwingen',
            'category'    => 'technical',
            'owner'       => 'developer',
            'impact'      => 'medium',
            'effort'      => 'low',
            'description' => 'Zorg dat alle pagina’s standaard via https geladen worden, inclusief canonical en interne links.',
            'checklist'   => [
                'Controleer of er een https redirect op root staat',
                'Update hardcoded http links in templates',
                'Controleer canonical tags op https',
            ],
        ],

        'write_missing_titles' => [
            'label'       => 'Ontbrekende title tags schrijven',
            'category'    => 'content',
            'owner'       => 'content',
            'impact'      => 'high',
            'effort'      => 'medium',
            'description' => 'Schrijf unieke, zoekwoordgerichte titels voor alle pagina’s zonder title tag.',
            'checklist'   => [
                'Exporteer lijst met pagina’s zonder title',
                'Bepaal focus keyword per pagina',
                'Schrijf titels met max 55-60 tekens',
            ],
        ],

        'fix_duplicate_titles' => [
            'label'       => 'Dubbele title tags herschrijven',
            'category'    => 'content',
            'owner'       => 'content',
            'impact'      => 'medium',
            'effort'      => 'medium',
            'description' => 'Maak dubbele titels uniek, gericht op het eigen onderwerp van de pagina.',
            'checklist'   => [
                'Gropeer pagina’s met dezelfde title',
                'Bepaal per groep wat de primaire pagina is',
                'Herschrijf titels van de overige pagina’s',
            ],
        ],

        'write_missing_meta_descriptions' => [
            'label'       => 'Ontbrekende meta descriptions schrijven',
            'category'    => 'content',
            'owner'       => 'content',
            'impact'      => 'medium',
            'effort'      => 'medium',
            'description' => 'Voeg aantrekkelijke meta descriptions toe voor pagina’s zonder description om de doorklikratio te verhogen.',
            'checklist'   => [
                'Exporteer lijst met pagina’s zonder description',
                'Schrijf beschrijvingen van 130 tot 155 tekens',
                'Voeg een call to action toe waar relevant',
            ],
        ],

        'fix_duplicate_meta_descriptions' => [
            'label'       => 'Dubbele meta descriptions aanpassen',
            'category'    => 'content',
            'owner'       => 'content',
            'impact'      => 'medium',
            'effort'      => 'medium',
            'description' => 'Maak descriptions uniek zodat elke pagina een eigen boodschap heeft.',
            'checklist'   => [
                'Groeperen op duplicate description',
                'Per groep bepalen welke pagina leidend is',
                'Overige descriptions herschrijven',
            ],
        ],

        'plan_content_improvements' => [
            'label'       => 'Content uitbouwen op dunne pagina’s',
            'category'    => 'content',
            'owner'       => 'content',
            'impact'      => 'high',
            'effort'      => 'high',
            'description' => 'Breid belangrijke pagina’s met weinig content uit met unieke, relevante tekst.',
            'checklist'   => [
                'Identificeer belangrijkste dunne pagina’s',
                'Bepaal zoekintentie en gewenste structuur',
                'Brief copywriter of schrijf concept zelf',
            ],
        ],

        'fix_broken_internal_links' => [
            'label'       => 'Kapotte interne links oplossen',
            'category'    => 'links',
            'owner'       => 'developer',
            'impact'      => 'high',
            'effort'      => 'medium',
            'description' => 'Zoek en herstel interne links die naar 4xx of 5xx pagina’s verwijzen.',
            'checklist'   => [
                'Exporteer lijst met kapotte interne links',
                'Zoek in templates en content naar de URLs',
                'Pas links aan naar juiste bestemming',
            ],
        ],

        'improve_internal_link_structure' => [
            'label'       => 'Interne linkstructuur naar key pages verbeteren',
            'category'    => 'links',
            'owner'       => 'seo',
            'impact'      => 'high',
            'effort'      => 'medium',
            'description' => 'Voeg meer interne links toe naar de belangrijkste diensten en landingspagina’s.',
            'checklist'   => [
                'Definieer 5 tot 10 belangrijkste pagina’s',
                'Zoek ondersteunende pagina’s waarvandaan je kunt linken',
                'Voeg contextuele links toe met relevante anchor teksten',
            ],
        ],

        'handle_orphan_pages' => [
            'label'       => 'Orphan pages verwerken',
            'category'    => 'links',
            'owner'       => 'seo',
            'impact'      => 'medium',
            'effort'      => 'medium',
            'description' => 'Bepaal per orphan page of deze interne links moet krijgen of verwijderd moet worden.',
            'checklist'   => [
                'Exporteer lijst met orphan pagina’s',
                'Beslis per pagina: houden, samenvoegen of verwijderen',
                'Voeg links toe of stel redirects in waar nodig',
            ],
        ],

        'improve_mobile_experience' => [
            'label'       => 'Mobiele ervaring verbeteren',
            'category'    => 'ux',
            'owner'       => 'developer',
            'impact'      => 'high',
            'effort'      => 'high',
            'description' => 'Optimaliseer layout, fonts en interactie voor mobiele gebruikers.',
            'checklist'   => [
                'Test belangrijkste pagina’s op mobiel',
                'Controleer fontgrootte, buttons en spacing',
                'Los horizontale scroll en layout issues op',
            ],
        ],

    ],

];
