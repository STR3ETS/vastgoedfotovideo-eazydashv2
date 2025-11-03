<?php
// resources/lang/en/gebruikers.php

return [
    'page_title' => 'Users',

    'tabs' => [
        'klanten'      => 'Customers',
        'medewerkers'  => 'Staff',
        'bedrijven'    => 'Companies',
    ],

    'bedrijven' => [
        'empty' => 'No companies found.',
    ],

    'search' => [
        'placeholder' => 'Search by name…',
    ],

    'add' => [
        'tooltip'      => 'Create',
        'klant'        => 'Customer',
        'medewerker'   => 'Staff member',
        'bedrijf'      => 'Company',
    ],

    'create' => [
        'title_generic'     => 'New user',
        'title_klant'       => 'New customer',
        'title_medewerker'  => 'New staff member',
        'title_bedrijf'     => 'New company',
        'fields' => [
            'name'   => 'Name',
            'email'  => 'Email',
            'rol'    => 'Role',
            'company_name'  => 'Company name',
        ],
        'placeholder' => [
            'name'  => 'First and last name',
            'email' => 'mail@example.com',
            'company_name'  => 'Company name',
        ],
        'roles' => [
            'medewerker' => 'Staff',
            'admin'      => 'Admin',
        ],
        'save'   => 'Save',
        'cancel' => 'Cancel',
    ],

    'detail' => [
        'fields' => [
            'name'  => 'Name',
            'email' => 'Email',
            'rol'   => 'Role',
        ],
        'save' => 'Save',
    ],

    'list' => [
        'empty' => 'No results yet…',
    ],

    'confirm' => [
        'title'       => 'Are you sure?',
        'text'        => 'Are you sure you want to delete this user?',
        'description' => 'After deletion, this action cannot be undone.',
        'yes'         => 'Yes, I’m sure',
        'no'          => 'Cancel',
        'tooltip_delete' => 'Delete',
    ],

    'fab' => [
        'close' => 'Close',
        'prev'  => 'Previous',
        'next'  => 'Next',
    ],

    'errors' => [
        'no_permission_create' => 'You don’t have permission to create users.',
        'no_permission_delete' => 'You don’t have permission to delete.',
        'csrf'                 => 'Session expired (CSRF). Refresh and try again.',
        'delete_failed'        => 'Delete failed. Please try again.',
    ],

    'actions' => [
        'delete_company' => 'Delete',
        'assign_user_button' => 'Click here to link a person',
        'assign_user_admin' => 'Assign as company admin',
        'unassign_user_admin' => 'Revoke company admin rights',
        'unassign_user_company' => 'Disconnect person from company',
    ],
];
