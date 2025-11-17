<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mapping van SERanking issues naar “begrijpelijke” issues
    |--------------------------------------------------------------------------
    |
    | - key = interne code (uniek in jouw systeem)
    | - seranking_match = array van stukjes tekst die in de SERanking issue-naam
    |   kunnen voorkomen (lowercase match).
    | - category = technisch thema
    | - impact / effort = relatieve inschatting
    | - owner = wie pakt dit normaal op
    |
    */

    'images_4xx' => [
        'label'           => 'Afbeeldingen die niet laden (4XX)',
        'seranking_match' => ['4xx images', 'images (not found)'],
        'category'        => 'techniek',
        'impact'          => 'hoog',
        'effort'          => 'middel',
        'owner'           => 'developer',
        'explanation'     => 'Er zijn links naar afbeeldingen die niet meer bestaan. Dit geeft “kapotte” beelden en kan crawlproblemen veroorzaken.',
        'what_to_do'      => [
            'Controleer de lijst met betrokken pagina’s in SERanking.',
            'Vervang of verwijder de kapotte afbeeldingslinks in het CMS.',
            'Upload ontbrekende afbeeldingen of hergebruik alternatieve beelden.',
        ],
    ],

    'http_4xx' => [
        'label'           => 'Pagina’s met 4XX HTTP statuscodes',
        'seranking_match' => ['4xx http status codes', 'http status 4xx'],
        'category'        => 'techniek',
        'impact'          => 'hoog',
        'effort'          => 'middel',
        'owner'           => 'developer',
        'explanation'     => 'Er zijn pagina’s die een 4XX statuscode geven (zoals 404). Dit blokkeert indexatie en zorgt voor slechte gebruikerservaring.',
        'what_to_do'      => [
            'Controleer of de URL nog gebruikt moet worden.',
            'Herstel de pagina of maak een 301-redirect naar een juiste pagina.',
            'Verwijder interne links naar niet-bestaande URL’s.',
        ],
    ],

    'no_inbound_links' => [
        'label'           => 'Pagina’s zonder interne links',
        'seranking_match' => ['no inbound links'],
        'category'        => 'links',
        'impact'          => 'middel',
        'effort'          => 'laag',
        'owner'           => 'content',
        'explanation'     => 'Deze pagina’s krijgen geen interne linkkracht. Google vindt ze moeilijker en gebruikers komen er minder snel.',
        'what_to_do'      => [
            'Voeg vanuit belangrijke pagina’s links toe naar deze pagina’s.',
            'Zorg dat de belangrijkste pagina’s in menu’s of overzichtspagina’s terugkomen.',
        ],
    ],

    'image_too_big' => [
        'label'           => 'Afbeeldingen zijn te groot',
        'seranking_match' => ['image too big'],
        'category'        => 'snelheid',
        'impact'          => 'middel',
        'effort'          => 'middel',
        'owner'           => 'developer',
        'explanation'     => 'Grote afbeeldingen vertragen de laadtijd en kunnen Core Web Vitals negatief beïnvloeden.',
        'what_to_do'      => [
            'Optimaliseer afbeeldingen (compressie, juiste formaat).',
            'Gebruik moderne formaten zoals WebP indien mogelijk.',
            'Controleer lazy-loading en responsieve varianten.',
        ],
    ],

    'description_missing' => [
        'label'           => 'Meta descriptions ontbreken',
        'seranking_match' => ['description missing'],
        'category'        => 'content',
        'impact'          => 'middel',
        'effort'          => 'laag',
        'owner'           => 'content',
        'explanation'     => 'Pagina’s zonder meta description laten kansen liggen op een goede doorklikratio in Google.',
        'what_to_do'      => [
            'Schrijf per belangrijke pagina een unieke, overtuigende meta description.',
            'Gebruik zoekwoorden op een natuurlijke manier.',
        ],
    ],

    'alt_text_missing' => [
        'label'           => 'Alt-teksten ontbreken',
        'seranking_match' => ['alt text missing'],
        'category'        => 'content',
        'impact'          => 'middel',
        'effort'          => 'laag',
        'owner'           => 'content',
        'explanation'     => 'Afbeeldingen zonder alt-tekst zijn minder toegankelijk en moeilijker te begrijpen voor zoekmachines.',
        'what_to_do'      => [
            'Geef belangrijke afbeeldingen een beschrijvende alt-tekst.',
            'Voorkom keyword stuffing; beschrijf wat er te zien is.',
        ],
    ],

    'slow_page_loading' => [
        'label'           => 'Langzame paginasnelheid',
        'seranking_match' => ['slow page loading speed'],
        'category'        => 'snelheid',
        'impact'          => 'hoog',
        'effort'          => 'middel',
        'owner'           => 'developer',
        'explanation'     => 'Trage pagina’s leiden tot afhakers en slechtere rankings, vooral op mobiel.',
        'what_to_do'      => [
            'Optimaliseer afbeeldingen en scripts.',
            'Controleer caching en hosting-instellingen.',
            'Los render-blocking resources op.',
        ],
    ],

    'cls_lab' => [
        'label'           => 'Cumulative Layout Shift (CLS) in lab-omgeving',
        'seranking_match' => ['cumulative layout shift', 'cls in a lab environment'],
        'category'        => 'snelheid',
        'impact'          => 'middel',
        'effort'          => 'middel',
        'owner'           => 'developer',
        'explanation'     => 'Elementen op de pagina verspringen tijdens het laden. Dit geeft een onrustige ervaring.',
        'what_to_do'      => [
            'Geef media (afbeeldingen/video’s) vaste hoogte/breedte.',
            'Laad fonts performant in en voorkom grote layout-verschuivingen.',
        ],
    ],

];
