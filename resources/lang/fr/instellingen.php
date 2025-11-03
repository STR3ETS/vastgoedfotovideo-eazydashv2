<?php

return [
    'title' => 'Paramètres',
    'search_placeholder' => 'Rechercher par nom…',

    'tabs' => [
        'personal'  => 'Données personnelles',
        'company'   => "Informations de l’entreprise",
        'team'      => 'Gérer et inviter des membres',
        'billing'   => 'Abonnement et paiement',
        'documents' => 'Documents',
    ],

    'fields' => [
        'name'  => 'Nom',
        'email' => 'E-mail',
        'lang'  => 'Langue',
    ],

    'actions' => [
        'save' => 'Enregistrer',
    ],

    'flash' => [
        'saved' => 'Paramètres enregistrés.',
    ],

    'company' => [
        'section' => [
            'basic'        => 'Informations sur l’entreprise',
            'contact'      => 'Contact',
            'address'      => 'Adresse',
            'registration' => 'Immatriculations',
        ],

        'fields' => [
            'name'         => 'Raison sociale',
            'country_code' => 'Pays d’établissement',
            'website'      => 'Site web',
            'email'        => 'E-mail',
            'phone'        => 'Numéro de téléphone',
            'street'       => 'Rue',
            'house_number' => 'Numéro',
            'postal_code'  => 'Code postal',
            'city'         => 'Ville',
            'kvk_number'   => 'Numéro KVK',
            'vat_number'   => 'Numéro de TVA',
            'trade_name'   => 'Nom commercial',
            'legal_form'   => 'Forme juridique',
        ],

        'placeholders' => [
            'country'    => 'Choisir un pays',
            'legal_form' => 'Choisir une forme juridique',
        ],

        'legal_forms' => [
            'eenmanszaak' => 'Entreprise individuelle',
            'vof'         => 'Société en nom collectif (SNC)',
            'cv'          => 'Société en commandite (SCS)',
            'bv'          => 'Société à responsabilité limitée (SARL)',
            'nv'          => 'Société anonyme (SA)',
            'stichting'   => 'Fondation',
            'vereniging'  => 'Association',
            'cooperatie'  => 'Coopérative',
        ],

        'countries' => [
            'NL' => 'Pays-Bas',
            'BE' => 'Belgique',
            'DE' => 'Allemagne',
            'FR' => 'France',
            'ES' => 'Espagne',
            'UK' => 'Royaume-Uni',
            'US' => 'États-Unis',
        ],
    ],

    'invite' => [
        'placeholders' => [
            'email' => 'Entrez une adresse e-mail',
        ],
        'fields' => [
            'email' => 'E-mail',
        ],
        'actions' => [
            'send' => 'Envoyer une invitation',
        ],
        'messages' => [
            'sent'      => 'Invitation envoyée à :email.',
            'already'   => 'Cet utilisateur est déjà invité ou membre de l’équipe.',
            'expired'   => 'Cette invitation est expirée ou invalide.',
            'accepted'  => 'Bienvenue dans l’équipe de :company !',
            'error'     => 'Une erreur s’est produite lors de l’envoi de l’invitation.',
        ],
        'accept' => [
            'title'       => 'Accepter l’invitation d’équipe',
            'subtitle'    => 'Créez votre compte pour rejoindre :company.',
            'fields' => [
                'name'     => 'Nom',
                'email'    => 'E-mail',
                'password' => 'Mot de passe',
            ],
            'actions' => [
                'create'   => 'Créer un compte',
                'decline'  => 'Refuser l’invitation',
            ],
        ],
        'email' => [
            'subject'     => 'Invitation pour :company',
            'intro'       => 'Vous êtes invité(e) à rejoindre l’équipe de :company. Cliquez sur le bouton ci-dessous pour créer votre compte.',
            'cta'         => 'Accepter l’invitation',
            'footer_days' => 'Ce lien expire dans :days jours.',
        ],
        'you' => 'Votre compte',
    ],
];
