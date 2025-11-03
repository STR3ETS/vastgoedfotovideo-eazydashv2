<?php

return [
    'title' => 'Instellingen',
    'search_placeholder' => 'Zoek op naam…',

    'tabs' => [
        'personal'  => 'Persoonlijke gegevens',
        'company'   => 'Bedrijfsgegevens',
        'team'      => 'Teamleden beheren & uitnodigen',
        'billing'   => 'Abonnement & betaling',
        'documents' => 'Documenten',
    ],

    'fields' => [
        'name'  => 'Naam',
        'email' => 'E-mail',
        'lang'  => 'Taal',
    ],

    'actions' => [
        'save' => 'Opslaan',
        'customize' => 'Personaliseer',
    ],

    'flash' => [
        'saved' => 'Instellingen opgeslagen.',
    ],

    'company' => [
        'section' => [
            'basic'        => 'Bedrijfsgegevens',
            'contact'      => 'Contact',
            'address'      => 'Adres',
            'registration' => 'Registraties',
        ],

        'fields' => [
            'name'         => 'Bedrijfsnaam',
            'country_code' => 'Vestigingsland',
            'website'      => 'Website',
            'email'        => 'E-mailadres',
            'phone'        => 'Telefoonnummer',
            'street'       => 'Straatnaam',
            'house_number' => 'Huisnummer',
            'postal_code'  => 'Postcode',
            'city'         => 'Stad',
            'kvk_number'   => 'KVK-nummer',
            'vat_number'   => 'BTW-nummer',
            'trade_name'   => 'Handelsnaam',
            'legal_form'   => 'Rechtsvorm',
        ],

        'placeholders' => [
            'country'    => 'Kies land',
            'legal_form' => 'Kies rechtsvorm',
        ],

        'legal_forms' => [
            'eenmanszaak' => 'Eenmanszaak',
            'vof'         => 'Vennootschap onder firma (VOF)',
            'cv'          => 'Commanditaire vennootschap (CV)',
            'bv'          => 'Besloten vennootschap (BV)',
            'nv'          => 'Naamloze vennootschap (NV)',
            'stichting'   => 'Stichting',
            'vereniging'  => 'Vereniging',
            'cooperatie'  => 'Coöperatie',
        ],

        'countries' => [
            'NL' => 'Nederland',
            'BE' => 'België',
            'DE' => 'Duitsland',
            'FR' => 'Frankrijk',
            'ES' => 'Spanje',
            'UK' => 'Verenigd Koninkrijk',
            'US' => 'Verenigde Staten',
        ],
    ],

    'invite' => [
        'placeholders' => [
            'email' => 'Voer een e-mailadres in',
        ],
        'fields' => [
            'email' => 'E-mailadres',
        ],
        'actions' => [
            'send' => 'Verstuur uitnodiging',
        ],
        'messages' => [
            'sent'      => 'Uitnodiging verzonden naar :email.',
            'already'   => 'Deze gebruiker is al uitgenodigd of lid van het team.',
            'expired'   => 'Deze uitnodiging is verlopen of ongeldig.',
            'accepted'  => 'Welkom bij het team van :company!',
            'error'     => 'Er ging iets mis bij het versturen van de uitnodiging.',
        ],
        'accept' => [
            'title'       => 'Teamuitnodiging accepteren',
            'subtitle'    => 'Maak je account aan om lid te worden van :company.',
            'fields' => [
                'name'     => 'Naam',
                'email'    => 'E-mailadres',
                'password' => 'Wachtwoord',
            ],
            'actions' => [
                'create'   => 'Account aanmaken',
                'decline'  => 'Weiger uitnodiging',
            ],
        ],
        'email' => [
            'subject'     => 'Uitnodiging voor :company',
            'intro'       => 'Je bent uitgenodigd om lid te worden van het team van :company. Klik op de knop hieronder om je account aan te maken.',
            'cta'         => 'Uitnodiging accepteren',
            'footer_days' => 'Deze link verloopt over :days dagen.',
        ],
        'you' => 'Jouw account',
    ],
];
