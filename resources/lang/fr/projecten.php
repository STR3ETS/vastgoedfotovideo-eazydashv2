<?php

return [
    // Page & titres
    'page_title'     => 'Projets',
    'statuses_title' => 'Statuts',

    // Statuts
    'statuses' => [
        'preview'          => 'Aperçu',
        'waiting_customer' => 'En attente du client',
        'offerte'          => 'Citation',
    ],

    'status_counts' => [
        'singular' => ':count projet',
        'plural'   => ':count projets',
    ],

    // Liste
    'empty'           => 'Il n’y a pas encore de projets.',
    'unknown_company' => 'Entreprise inconnue',

    // Section d’aperçu
    'preview' => [
        'section_title'       => 'URL d’aperçu',
        'view_preview_button' => 'Voir l’aperçu',
        'add_button_tooltip'  => 'Ajouter ou modifier l’URL d’aperçu',
        'current_label'       => 'Aperçu actuel',
        'none'                => 'Aucune URL d’aperçu définie pour le moment.',
        'url_label'           => 'URL d’aperçu',
        'url_placeholder'     => 'URL d’aperçu Sitejet',
        'save'                => 'Enregistrer',
        'saving'              => 'Enregistrement…',
        'open_button'         => 'Ouvrir l’aperçu',
        'save_success'        => 'Parfait ! L’URL d’aperçu a été ajoutée au projet.',
        'save_error'          => 'Échec de l’enregistrement de l’URL d’aperçu.',
    ],

    // Données de la demande (depuis aanvraag_websites)
    'request' => [
        'section_title'       => 'Données de la demande',
        'id'                  => 'ID de la demande',
        'choice'              => 'Type de demande',
        'company'             => 'Entreprise',
        'contact_name'        => 'Interlocuteur',
        'contact_email'       => 'Adresse e-mail',
        'contact_phone'       => 'Numéro de téléphone',
        'created_at'          => 'Créé le',
        'status'              => 'Statut',
        'intake_at'           => 'Intake le',
        'intake_duration'     => 'Durée de l’intake (minutes)',
        'intake_done'         => 'Intake terminé',
        'intake_completed_at' => 'Intake terminé le',
        'yes'                 => 'Oui',
        'no'                  => 'Non',
    ],

    // Toasts (pour JS)
    'toast' => [
        'status_update_success' => 'Succès ! L’état du projet a été mis à jour avec succès.',
        'status_update_error'   => 'Échec ! La modification du statut du projet a échoué.',
    ],
];
