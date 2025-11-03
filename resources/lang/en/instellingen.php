<?php

return [
    'title' => 'Settings',
    'search_placeholder' => 'Search by name…',

    'tabs' => [
        'personal'  => 'Personal information',
        'company'   => 'Company information',
        'team'      => 'Manage & invite team members',
        'billing'   => 'Subscription & billing',
        'documents' => 'Documents',
    ],

    'fields' => [
        'name'  => 'Name',
        'email' => 'Email',
        'lang'  => 'Language',
    ],

    'actions' => [
        'save' => 'Save',
    ],

    'flash' => [
        'saved' => 'Settings saved.',
    ],

    'company' => [
        'section' => [
            'basic'        => 'Company details',
            'contact'      => 'Contact',
            'address'      => 'Address',
            'registration' => 'Registrations',
        ],

        'fields' => [
            'name'         => 'Company name',
            'country_code' => 'Country of establishment',
            'website'      => 'Website',
            'email'        => 'Email address',
            'phone'        => 'Phone number',
            'street'       => 'Street',
            'house_number' => 'House number',
            'postal_code'  => 'Postal code',
            'city'         => 'City',
            'kvk_number'   => 'KVK number',
            'vat_number'   => 'VAT number',
            'trade_name'   => 'Trade name',
            'legal_form'   => 'Legal form',
        ],

        'placeholders' => [
            'country'    => 'Select country',
            'legal_form' => 'Select legal form',
        ],

        'legal_forms' => [
            'eenmanszaak' => 'Sole proprietorship',
            'vof'         => 'General partnership (GP)',
            'cv'          => 'Limited partnership (LP)',
            'bv'          => 'Private limited company (BV)',
            'nv'          => 'Public limited company (NV)',
            'stichting'   => 'Foundation',
            'vereniging'  => 'Association',
            'cooperatie'  => 'Cooperative',
        ],

        'countries' => [
            'NL' => 'Netherlands',
            'BE' => 'Belgium',
            'DE' => 'Germany',
            'FR' => 'France',
            'ES' => 'Spain',
            'UK' => 'United Kingdom',
            'US' => 'United States',
        ],
    ],

    'invite' => [
        'placeholders' => [
            'email' => 'Please enter an email address',
        ],
        'fields' => [
            'email' => 'Email address',
        ],
        'actions' => [
            'send' => 'Send invite',
        ],
        'messages' => [
            'sent'      => 'Invitation sent to :email.',
            'already'   => 'This user is already invited or part of the team.',
            'expired'   => 'This invitation has expired or is invalid.',
            'accepted'  => 'Welcome to the :company team!',
            'error'     => 'Something went wrong while sending the invite.',
        ],
        'accept' => [
            'title'       => 'Accept Team Invitation',
            'subtitle'    => 'Create your account to join :company.',
            'fields' => [
                'name'     => 'Name',
                'email'    => 'Email address',
                'password' => 'Password',
            ],
            'actions' => [
                'create'   => 'Create account',
                'decline'  => 'Decline invitation',
            ],
        ],
        'email' => [
            'subject'     => 'Invitation to :company',
            'intro'       => 'You have been invited to join :company’s team. Click the button below to create your account.',
            'cta'         => 'Accept invitation',
            'footer_days' => 'This link expires in :days days.',
        ],
        'you' => 'Your account',
    ],
];
