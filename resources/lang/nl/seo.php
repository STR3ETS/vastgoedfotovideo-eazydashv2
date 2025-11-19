<?php

return [

    'page_title' => 'SEO audits',

    'filters' => [
        'company'        => 'Klant / bedrijf',
        'all_companies'  => 'Alle bedrijven',
        'status'         => 'Status',
        'all_statuses'   => 'Alle statussen',
        'apply'          => 'Filters toepassen',
        'reset'          => 'Reset',
    ],

    'new_audit' => [
        'title'              => 'Nieuwe SEO audit starten',
        'subtitle'           => 'Kies een klant en type audit. De analyse draait op de achtergrond.',
        'company'            => 'Klant / bedrijf',
        'company_placeholder'=> 'Kies een bedrijf',
        'domain'             => 'Domein (optioneel)',
        'domain_placeholder' => 'Bijvoorbeeld: voorbeeld.nl',
        'domain_help'        => 'Laat leeg om automatisch het domein van de klant te gebruiken (indien bekend).',
        'type'               => 'Type audit',
        'country'            => 'Land',
        'locale'             => 'Taal / locale',
        'start'              => 'Start SEO audit',
    ],

    'list' => [
        'title'        => 'Auditgeschiedenis',
        'count_suffix' => 'audits',
        'empty'        => 'Er zijn nog geen SEO audits uitgevoerd.',
    ],

    'detail' => [
        'title'        => 'Laatste audit',
        'subtitle'     => 'Samenvatting van de meest recent uitgevoerde audit.',
        'overall_score'=> 'Totaalscore',
        'sections'     => 'Belangrijkste onderdelen',
        'issues_found' => 'gevonden verbeterpunten',
        'no_sections'  => 'Zodra de koppeling met SE Ranking werkt, verschijnen hier de scores per onderdeel.',
        'empty_title'  => 'Nog geen audits',
        'empty_text'   => 'Start links een eerste SEO audit voor een klant. Hier verschijnt daarna een samenvatting.',
    ],

];
