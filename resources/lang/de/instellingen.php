<?php

return [
    'title' => 'Einstellungen',
    'search_placeholder' => 'Nach Namen suchen…',

    'tabs' => [
        'personal'  => 'Persönliche Daten',
        'company'   => 'Unternehmensdaten',
        'team'      => 'Teammitglieder verwalten & einladen',
        'billing'   => 'Abonnement & Zahlung',
        'documents' => 'Dokumente',
    ],

    'fields' => [
        'name'  => 'Name',
        'email' => 'E-Mail',
        'lang'  => 'Sprache',
    ],

    'actions' => [
        'save' => 'Speichern',
    ],

    'flash' => [
        'saved' => 'Einstellungen gespeichert.',
    ],

    'company' => [
        'section' => [
            'basic'        => 'Unternehmensdaten',
            'contact'      => 'Kontakt',
            'address'      => 'Adresse',
            'registration' => 'Registrierungen',
        ],

        'fields' => [
            'name'         => 'Firmenname',
            'country_code' => 'Sitzland',
            'website'      => 'Website',
            'email'        => 'E-Mail-Adresse',
            'phone'        => 'Telefonnummer',
            'street'       => 'Straße',
            'house_number' => 'Hausnummer',
            'postal_code'  => 'Postleitzahl',
            'city'         => 'Stadt',
            'kvk_number'   => 'KVK-Nummer',
            'vat_number'   => 'USt-IdNr.',
            'trade_name'   => 'Handelsname',
            'legal_form'   => 'Rechtsform',
        ],

        'placeholders' => [
            'country'    => 'Land auswählen',
            'legal_form' => 'Rechtsform auswählen',
        ],

        'legal_forms' => [
            'eenmanszaak' => 'Einzelunternehmen',
            'vof'         => 'Offene Handelsgesellschaft (OHG)',
            'cv'          => 'Kommanditgesellschaft (KG)',
            'bv'          => 'Gesellschaft mit beschränkter Haftung (GmbH)',
            'nv'          => 'Aktiengesellschaft (AG)',
            'stichting'   => 'Stiftung',
            'vereniging'  => 'Verein',
            'cooperatie'  => 'Genossenschaft',
        ],

        'countries' => [
            'NL' => 'Niederlande',
            'BE' => 'Belgien',
            'DE' => 'Deutschland',
            'FR' => 'Frankreich',
            'ES' => 'Spanien',
            'UK' => 'Vereinigtes Königreich',
            'US' => 'Vereinigte Staaten',
        ],
    ],

    'invite' => [
        'placeholders' => [
            'email' => 'Geben Sie eine E-Mail-Adresse ein',
        ],
        'fields' => [
            'email' => 'E-Mail-Adresse',
        ],
        'actions' => [
            'send' => 'Einladung senden',
        ],
        'messages' => [
            'sent'      => 'Einladung an :email gesendet.',
            'already'   => 'Dieser Benutzer wurde bereits eingeladen oder ist Mitglied des Teams.',
            'expired'   => 'Diese Einladung ist abgelaufen oder ungültig.',
            'accepted'  => 'Willkommen im Team von :company!',
            'error'     => 'Beim Senden der Einladung ist ein Fehler aufgetreten.',
        ],
        'accept' => [
            'title'       => 'Teameinladung annehmen',
            'subtitle'    => 'Erstellen Sie Ihr Konto, um dem Team von :company beizutreten.',
            'fields' => [
                'name'     => 'Name',
                'email'    => 'E-Mail-Adresse',
                'password' => 'Passwort',
            ],
            'actions' => [
                'create'   => 'Konto erstellen',
                'decline'  => 'Einladung ablehnen',
            ],
        ],
        'email' => [
            'subject'     => 'Einladung für :company',
            'intro'       => 'Du wurdest eingeladen, dem Team von :company beizutreten. Klicke auf den Button unten, um dein Konto zu erstellen.',
            'cta'         => 'Einladung annehmen',
            'footer_days' => 'Dieser Link läuft in :days Tagen ab.',
        ],
        'you' => 'Ihr Konto',
    ],
];
