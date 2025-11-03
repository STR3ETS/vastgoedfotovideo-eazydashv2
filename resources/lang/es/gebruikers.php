<?php
// resources/lang/es/gebruikers.php

return [
    'page_title' => 'Usuarios',

    'tabs' => [
        'klanten'      => 'Clientes',
        'medewerkers'  => 'Empleados',
        'bedrijven'    => 'Empresas',
    ],

    'bedrijven' => [
        'empty' => 'No se encontraron empresas.',
    ],

    'search' => [
        'placeholder' => 'Buscar por nombre…',
    ],

    'add' => [
        'tooltip'      => 'Crear',
        'klant'        => 'Cliente',
        'medewerker'   => 'Empleado',
        'bedrijf'      => 'Compañía',
    ],

    'create' => [
        'title_generic'     => 'Nuevo usuario',
        'title_klant'       => 'Nuevo cliente',
        'title_medewerker'  => 'Nuevo empleado',
        'title_bedrijf'     => 'Nueva empresa',
        'fields' => [
            'name'   => 'Nombre',
            'email'  => 'Correo electrónico',
            'rol'    => 'Rol',
            'company_name'  => 'Nombre de empresa',
        ],
        'placeholder' => [
            'name'  => 'Nombre y apellido',
            'email' => 'correo@ejemplo.es',
            'company_name'  => 'Nombre de empresa',
        ],
        'roles' => [
            'medewerker' => 'Empleado',
            'admin'      => 'Administrador',
        ],
        'save'   => 'Guardar',
        'cancel' => 'Cancelar',
    ],

    'detail' => [
        'fields' => [
            'name'  => 'Nombre',
            'email' => 'Correo electrónico',
            'rol'   => 'Rol',
        ],
        'save' => 'Guardar',
    ],

    'list' => [
        'empty' => 'Aún no hay resultados…',
    ],

    'confirm' => [
        'title'       => '¿Estás seguro?',
        'text'        => '¿Seguro que quieres eliminar este usuario?',
        'description' => 'Después de eliminarlo, no se puede deshacer.',
        'yes'         => 'Sí, estoy seguro',
        'no'          => 'Cancelar',
        'tooltip_delete' => 'Eliminar',
    ],

    'fab' => [
        'close' => 'Cerrar',
        'prev'  => 'Anterior',
        'next'  => 'Siguiente',
    ],

    'errors' => [
        'no_permission_create' => 'No tienes permiso para crear usuarios.',
        'no_permission_delete' => 'No tienes permiso para eliminar.',
        'csrf'                 => 'Sesión caducada (CSRF). Actualiza la página e inténtalo de nuevo.',
        'delete_failed'        => 'No se pudo eliminar. Inténtalo de nuevo.',
    ],

    'actions' => [
        'delete_company' => 'Eliminar',
        'assign_user_button' => 'Haz clic aquí para conectar con una persona',
        'assign_user_admin' => 'Asignar como administrador de la empresa',
        'unassign_user_admin' => 'Revocar los derechos de administrador de la empresa',
        'unassign_user_company' => 'Desconectar a la persona de la empresa',
    ],
];
