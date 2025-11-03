<?php
// resources/lang/fr/gebruikers.php

return [
    'page_title' => 'Utilisateurs',

    'tabs' => [
        'klanten'      => 'Clients',
        'medewerkers'  => 'Employés',
        'bedrijven'    => 'Entreprises',
    ],

    'bedrijven' => [
        'empty' => 'Aucune entreprise trouvée.',
    ],

    'search' => [
        'placeholder' => 'Rechercher par nom…',
    ],

    'add' => [
        'tooltip'      => 'Créer',
        'klant'        => 'Client',
        'medewerker'   => 'Employé',
        'bedrijf'      => 'Entreprise',
    ],

    'create' => [
        'title_generic'     => 'Nouvel utilisateur',
        'title_klant'       => 'Nouveau client',
        'title_medewerker'  => 'Nouvel employé',
        'title_bedrijf'     => 'Nouvelle entreprise',
        'fields' => [
            'name'   => 'Nom',
            'email'  => 'E-mail',
            'rol'    => 'Rôle',
            'company_name'  => "Nom de l'entreprise",
        ],
        'placeholder' => [
            'name'  => 'Nom et prénom',
            'email' => 'mail@exemple.fr',
            'company_name'  => "Nom de l'entreprise",
        ],
        'roles' => [
            'medewerker' => 'Employé',
            'admin'      => 'Administrateur',
        ],
        'save'   => 'Enregistrer',
        'cancel' => 'Annuler',
    ],

    'detail' => [
        'fields' => [
            'name'  => 'Nom',
            'email' => 'E-mail',
            'rol'   => 'Rôle',
        ],
        'save' => 'Enregistrer',
    ],

    'list' => [
        'empty' => 'Aucun résultat pour le moment…',
    ],

    'confirm' => [
        'title'       => 'Êtes-vous sûr ?',
        'text'        => 'Voulez-vous vraiment supprimer cet utilisateur ?',
        'description' => 'Après la suppression, cette action est irréversible.',
        'yes'         => 'Oui, je suis sûr',
        'no'          => 'Annuler',
        'tooltip_delete' => 'Supprimer',
    ],

    'fab' => [
        'close' => 'Fermer',
        'prev'  => 'Précédent',
        'next'  => 'Suivant',
    ],

    'errors' => [
        'no_permission_create' => 'Vous n’avez pas l’autorisation de créer des utilisateurs.',
        'no_permission_delete' => 'Vous n’avez pas l’autorisation de supprimer.',
        'csrf'                 => 'Session expirée (CSRF). Actualisez la page et réessayez.',
        'delete_failed'        => 'Échec de la suppression. Réessayez.',
    ],

    'actions' => [
        'delete_company' => 'Supprimer',
        'assign_user_button' => 'Cliquez ici pour lier une personne',
        'assign_user_admin' => "Désigner comme administrateur de l'entreprise",
        'unassign_user_admin' => "Révoquer les droits d'administrateur de l'entreprise",
        'unassign_user_company' => "Déconnecter la personne de l'entreprise",
    ],
];
