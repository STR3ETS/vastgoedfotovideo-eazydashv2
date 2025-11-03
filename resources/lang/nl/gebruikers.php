<?php
// resources/lang/nl/gebruikers.php

return [
    'page_title' => 'Gebruikers',

    'tabs' => [
        'klanten'      => 'Klanten',
        'medewerkers'  => 'Medewerkers',
        'bedrijven'    => 'Bedrijven',
    ],

    'bedrijven' => [
        'empty' => 'Geen bedrijven gevonden.',
    ],

    'search' => [
        'placeholder' => 'Zoek op naam…',
    ],

    'add' => [
        'tooltip'      => 'Aanmaken',
        'klant'        => 'Klant',
        'medewerker'   => 'Medewerker',
        'bedrijf'      => 'Bedrijf',
    ],

    'create' => [
        'title_generic'     => 'Nieuwe gebruiker',
        'title_klant'       => 'Nieuwe klant',
        'title_medewerker'  => 'Nieuwe medewerker',
        'title_bedrijf'     => 'Nieuw bedrijf',
        'fields' => [
            'name'   => 'Naam',
            'email'  => 'E-mail',
            'rol'    => 'Rol',
            'company_name'  => 'Bedrijfsnaam',
        ],
        'placeholder' => [
            'name'  => 'Voor- en achternaam',
            'email' => 'mail@voorbeeld.nl',
            'company_name'  => 'Bedrijfsnaam',
        ],
        'roles' => [
            'medewerker' => 'Medewerker',
            'admin'      => 'Admin',
        ],
        'save'   => 'Opslaan',
        'cancel' => 'Annuleren',
    ],

    'detail' => [
        'fields' => [
            'name'  => 'Naam',
            'email' => 'E-mail',
            'rol'   => 'Rol',
        ],
        'save' => 'Opslaan',
    ],

    'list' => [
        'empty' => 'Nog geen resultaten...',
    ],

    'confirm' => [
        'title'       => 'Weet je het zeker?',
        'text'        => 'Weet je zeker dat je deze gebruiker wilt verwijderen?',
        'description' => 'Na het verwijderen is er geen mogelijkheid meer om deze actie terug te draaien.',
        'yes'         => 'Ja, ik weet het zeker',
        'no'          => 'Annuleren',
        'tooltip_delete' => 'Verwijderen',
    ],

    'fab' => [
        'close' => 'Sluiten',
        'prev'  => 'Vorige',
        'next'  => 'Volgende',
    ],

    'errors' => [
        'no_permission_create' => 'Je hebt geen rechten om gebruikers aan te maken.',
        'no_permission_delete' => 'Je hebt geen rechten om te verwijderen.',
        'csrf'                 => 'Sessie verlopen (CSRF). Vernieuw de pagina en probeer opnieuw.',
        'delete_failed'        => 'Verwijderen is mislukt. Probeer het nogmaals.',
    ],

    'actions' => [
        'delete_company' => 'Verwijderen',
        'assign_user_button' => 'Klik hier om een persoon te koppelen',
        'assign_user_admin' => 'Wijs toe als bedrijfsadmin',
        'unassign_user_admin' => 'Trek bedrijfsadmin-rechten in',
        'unassign_user_company' => 'Ontkoppel persoon van het bedrijf',
    ],
];
