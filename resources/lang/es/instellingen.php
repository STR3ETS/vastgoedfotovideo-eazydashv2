<?php

return [
    'title' => 'Ajustes',
    'search_placeholder' => 'Buscar por nombre…',

    'tabs' => [
        'personal'  => 'Datos personales',
        'company'   => 'Datos de la empresa',
        'team'      => 'Gestionar e invitar integrantes',
        'billing'   => 'Suscripción y pago',
        'documents' => 'Documentos',
    ],

    'fields' => [
        'name'  => 'Nombre',
        'email' => 'Correo electrónico',
        'lang'  => 'Idioma',
    ],

    'actions' => [
        'save' => 'Guardar',
    ],

    'flash' => [
        'saved' => 'Ajustes guardados.',
    ],

    'company' => [
        'section' => [
            'basic'        => 'Datos de la empresa',
            'contact'      => 'Contacto',
            'address'      => 'Dirección',
            'registration' => 'Registros',
        ],

        'fields' => [
            'name'         => 'Nombre de la empresa',
            'country_code' => 'País de establecimiento',
            'website'      => 'Sitio web',
            'email'        => 'Correo electrónico',
            'phone'        => 'Número de teléfono',
            'street'       => 'Calle',
            'house_number' => 'Número',
            'postal_code'  => 'Código postal',
            'city'         => 'Ciudad',
            'kvk_number'   => 'Número KVK',
            'vat_number'   => 'Número de IVA',
            'trade_name'   => 'Nombre comercial',
            'legal_form'   => 'Forma jurídica',
        ],

        'placeholders' => [
            'country'    => 'Selecciona país',
            'legal_form' => 'Selecciona forma jurídica',
        ],

        'legal_forms' => [
            'eenmanszaak' => 'Empresa individual',
            'vof'         => 'Sociedad colectiva',
            'cv'          => 'Sociedad comanditaria',
            'bv'          => 'Sociedad de responsabilidad limitada (SRL)',
            'nv'          => 'Sociedad anónima (SA)',
            'stichting'   => 'Fundación',
            'vereniging'  => 'Asociación',
            'cooperatie'  => 'Cooperativa',
        ],

        'countries' => [
            'NL' => 'Países Bajos',
            'BE' => 'Bélgica',
            'DE' => 'Alemania',
            'FR' => 'Francia',
            'ES' => 'España',
            'UK' => 'Reino Unido',
            'US' => 'Estados Unidos',
        ],
    ],

    'invite' => [
        'placeholders' => [
            'email' => 'Introduzca una dirección de correo electrónico',
        ],
        'fields' => [
            'email' => 'Correo electrónico',
        ],
        'actions' => [
            'send' => 'Enviar invitación',
        ],
        'messages' => [
            'sent'      => 'Invitación enviada a :email.',
            'already'   => 'Este usuario ya fue invitado o es miembro del equipo.',
            'expired'   => 'Esta invitación ha expirado o no es válida.',
            'accepted'  => '¡Bienvenido al equipo de :company!',
            'error'     => 'Se produjo un error al enviar la invitación.',
        ],
        'accept' => [
            'title'       => 'Aceptar invitación del equipo',
            'subtitle'    => 'Cree su cuenta para unirse a :company.',
            'fields' => [
                'name'     => 'Nombre',
                'email'    => 'Correo electrónico',
                'password' => 'Contraseña',
            ],
            'actions' => [
                'create'   => 'Crear cuenta',
                'decline'  => 'Rechazar invitación',
            ],
        ],
        'email' => [
            'subject'     => 'Invitación a :company',
            'intro'       => 'Has sido invitado/a a unirte al equipo de :company. Haz clic en el botón de abajo para crear tu cuenta.',
            'cta'         => 'Aceptar invitación',
            'footer_days' => 'Este enlace expira en :days días.',
        ],
        'you' => 'Tu cuenta',
    ],
];
