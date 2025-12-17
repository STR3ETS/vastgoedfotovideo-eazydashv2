<?php

return [
    // Página y títulos
    'page_title'     => 'Proyectos',
    'statuses_title' => 'Estados',

    // Estados
    'statuses' => [
        'preview'          => 'Vista previa',
        'waiting_customer' => 'En espera del cliente',
        'preview_approved' => 'Vista previa aprobada',
        'offerte'          => 'Cotización',
    ],

    'status_counts' => [
        'singular' => ':count proyecto',
        'plural'   => ':count proyectos',
    ],

    // Lista
    'empty'           => 'Aún no hay proyectos.',
    'unknown_company' => 'Empresa desconocida',

    // Sección de vista previa
    'preview' => [
        'section_title'       => 'URL de vista previa',
        'view_preview_button' => 'Ver vista previa',
        'add_button_tooltip'  => 'Agregar o cambiar la URL de vista previa',
        'current_label'       => 'Vista previa actual',
        'none'                => 'Aún no se ha establecido una URL de vista previa.',
        'url_label'           => 'URL de vista previa',
        'url_placeholder'     => 'URL de vista previa de Sitejet',
        'save'                => 'Guardar',
        'saving'              => 'Guardando…',
        'open_button'         => 'Abrir vista previa',
        'save_success'        => '¡Listo! La URL de vista previa se ha añadido al proyecto.',
        'save_error'          => 'Error al guardar la URL de vista previa.',
    ],

    // Datos de la solicitud (de aanvraag_websites)
    'request' => [
        'section_title'       => 'Datos de la solicitud',
        'id'                  => 'ID de solicitud',
        'choice'              => 'Tipo de solicitud',
        'company'             => 'Empresa',
        'contact_name'        => 'Persona de contacto',
        'contact_email'       => 'Correo electrónico',
        'contact_phone'       => 'Teléfono',
        'created_at'          => 'Creado el',
        'status'              => 'Estado',
        'intake_at'           => 'Intake el',
        'intake_duration'     => 'Duración del intake (minutos)',
        'intake_done'         => 'Intake completado',
        'intake_completed_at' => 'Intake completado el',
        'yes'                 => 'Sí',
        'no'                  => 'No',
    ],

    // Toasts (para JS)
    'toast' => [
        'status_update_success' => '¡Éxito! El estado del proyecto se ha actualizado correctamente.',
        'status_update_error'   => '¡Error! El estado del proyecto no se ha podido cambiar correctamente.',
    ],
];
