<?php
// resources/lang/de/gebruikers.php

return [
    'page_title' => 'Benutzer',

    'tabs' => [
        'klanten'      => 'Kunden',
        'medewerkers'  => 'Mitarbeiter',
        'bedrijven'    => 'Unternehmen',
    ],

    'bedrijven' => [
        'empty' => 'Keine Unternehmen gefunden.',
    ],

    'search' => [
        'placeholder' => 'Nach Name suchen…',
    ],

    'add' => [
        'tooltip'      => 'Erstellen',
        'klant'        => 'Kunde',
        'medewerker'   => 'Mitarbeiter',
        'bedrijf'      => 'Unternehmen',
    ],

    'create' => [
        'title_generic'     => 'Neuer Benutzer',
        'title_klant'       => 'Neuer Kunde',
        'title_medewerker'  => 'Neuer Mitarbeiter',
        'title_bedrijf'     => 'Neues Unternehmen',
        'fields' => [
            'name'   => 'Name',
            'email'  => 'E-Mail',
            'rol'    => 'Rolle',
            'company_name'  => 'Name der Firma',
        ],
        'placeholder' => [
            'name'  => 'Vor- und Nachname',
            'email' => 'mail@example.de',
            'company_name'  => 'Name der Firma',
        ],
        'roles' => [
            'medewerker' => 'Mitarbeiter',
            'admin'      => 'Administrator',
        ],
        'save'   => 'Speichern',
        'cancel' => 'Abbrechen',
    ],

    'detail' => [
        'fields' => [
            'name'  => 'Name',
            'email' => 'E-Mail',
            'rol'   => 'Rolle',
        ],
        'save' => 'Speichern',
    ],

    'list' => [
        'empty' => 'Noch keine Ergebnisse…',
    ],

    'confirm' => [
        'title'       => 'Sind Sie sicher?',
        'text'        => 'Möchten Sie diesen Benutzer wirklich löschen?',
        'description' => 'Nach dem Löschen kann diese Aktion nicht rückgängig gemacht werden.',
        'yes'         => 'Ja, ich bin sicher',
        'no'          => 'Abbrechen',
        'tooltip_delete' => 'Löschen',
    ],

    'fab' => [
        'close' => 'Schließen',
        'prev'  => 'Zurück',
        'next'  => 'Weiter',
    ],

    'errors' => [
        'no_permission_create' => 'Sie haben keine Berechtigung, Benutzer zu erstellen.',
        'no_permission_delete' => 'Sie haben keine Berechtigung zum Löschen.',
        'csrf'                 => 'Sitzung abgelaufen (CSRF). Seite neu laden und erneut versuchen.',
        'delete_failed'        => 'Löschen fehlgeschlagen. Bitte erneut versuchen.',
    ],

    'actions' => [
        'delete_company' => 'Löschen',
        'assign_user_button' => 'Klicken Sie hier, um eine Person zu verlinken',
        'assign_user_admin' => 'Als Firmenadministrator zuweisen',
        'unassign_user_admin' => 'Firmenadministratorrechte entziehen',
        'unassign_user_company' => 'Person vom Unternehmen trennen',
    ],
];
