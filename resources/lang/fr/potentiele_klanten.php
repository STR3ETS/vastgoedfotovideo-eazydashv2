<?php

return [
    'page_title'     => 'Clients potentiels',
    'statuses_title' => 'Statuts',

    'statuses' => [
        'prospect' => 'Prospect',
        'contact'  => 'Contact',
        'intake'   => 'Entretien',
        'dead'     => 'Perdu',
        'lead'     => 'Lead',
    ],

    'status_counts' => [
        'singular' => ':count projet',
        'plural'   => ':count projets',
    ],

    'filters' => [
        'prospect' => 'Prospect',
        'contact'  => 'Contact',
        'intake'   => 'Entretien',
        'dead'     => 'Perdu',
        'lead'     => 'Lead',
    ],

    'list' => [
        'no_requests'          => 'Aucune demande trouvée pour le moment.',
        'no_results_for_state' => 'Aucun résultat pour ce statut.',
    ],

    'intake_modal' => [
        'title'          => "Planifier un entretien d'accueil",
        'date_label'     => 'Date',
        'duration_label' => 'Durée',
        'duration_30'    => '30 minutes',
        'duration_45'    => '45 minutes',
        'duration_60'    => '60 minutes',
        'cancel'         => 'Annuler',
        'confirm'        => "Confirmer et planifier l'entretien",
        'confirming'     => 'Confirmation…',
    ],

    'intake_panel' => [
        'duration_prefix' => 'Durée :',
        'duration_suffix' => 'min',
        'mark_done'       => "Marquer l'entretien comme terminé",
        'delete'          => 'Supprimer',
        'planned_badge'   => 'Planifié',
        'completed_badge' => 'Terminé',
        'delete_title'    => "Supprimer l'entretien",
        'delete_question' => "Voulez-vous vraiment supprimer cet entretien et remettre le statut sur :status ?",
        'delete_yes'      => 'Oui, supprimer',
        'delete_cancel'   => 'Annuler',
        'today'           => "Aujourd'hui",
        'tomorrow'        => 'Demain',
    ],

    'intake_questions' => [
        'section_title' => "Entretien d'accueil",
        'notes_title'   => 'Notes',
        'notes_help'    => 'Sera enregistré avec le bouton Enregistrer.',
        'notes_missing' => "Aucun champ de notes trouvé pour cet entretien.",
        'save'          => 'Enregistrer',
        'saving'        => 'Enregistrement...',
        'open_panel_tooltip' => 'Offenes Aufnahmegespräch',
    ],

    'calls' => [
        'section_title'    => 'Appels',
        'new_call_tooltip' => 'Nouvel appel',
        'new_call_title'   => 'Nouvel appel',
        'result_label'     => 'Résultat',
        'result_none'      => 'Pas de réponse',
        'result_spoken'    => 'A parlé au client',
        'note_label'       => 'Note',
        'note_placeholder' => 'Écrire une note...',
        'save'             => 'Enregistrer',
        'saving'           => 'Enregistrement...',
        'none'             => "Aucun appel enregistré pour l'instant.",
        'called_by'        => 'Appelé par : :name',
        'badge_none'       => 'Appel : Pas de réponse',
        'badge_spoken'     => 'Appel : Client joint',
        'result_choose'    => 'Choisir le résultat',
    ],

    'files' => [
        'section_title'   => 'Fichiers',
        'drop_text'       => 'Déposez les fichiers ici ou :click pour choisir',
        'drop_click'      => 'cliquez',
        'drop_help'       => 'Fichiers pris en charge : images, PDF, Word, Excel, texte, ZIP, etc.',
        'none'            => 'Aucun fichier téléversé pour le moment.',
        'uploading'       => 'Téléversement...',
        'uploaded_on'     => 'Téléversé le :date',
        'open'            => 'Ouvrir',
        'delete'          => 'Supprimer',
        'delete_title'    => 'Supprimer le fichier',
        'delete_question' => 'Voulez-vous vraiment supprimer ce fichier ?',
        'delete_yes'      => 'Oui, supprimer',
        'delete_cancel'   => 'Annuler',
    ],

    'logbook' => [
        'title'        => 'Journal',
        'empty'        => "Aucune activité pour l'instant.",
        'by'           => 'Par : :name',
        'unknown_user' => 'Inconnu',
    ],

    'choices' => [
        'new'     => 'Nouveau site internet',
        'renew'   => 'Renouveler le site Web',
        'default' => 'Demande de site Web',
    ],

    'toast' => [
        'status_intake_only_from_contact'
            => "Échec ! Le statut « Entretien » n’est autorisé que depuis le statut « Contact » (actuel : :current).",
        'status_dead_only_from_contact_or_intake'
            => "Échec ! Le statut « Perdu » n’est autorisé que depuis « Contact » ou « Entretien » (actuel : :current).",
        'status_already'
            => 'Échec ! Cette demande a déjà le statut :status.',
        'status_update_success'
            => 'Succès ! Statut mis à jour vers :status.',
        'status_update_error'
            => 'Impossible de mettre à jour le statut.',
        'intake_planned'
            => "Entretien planifié et statut mis à jour vers Entretien.",
        'intake_plan_error'
            => "Impossible de planifier l'entretien.",
        'intake_completed'
            => "Entretien marqué comme terminé.",
        'intake_complete_error'
            => "Impossible de terminer l'entretien.",
        'intake_removed'
            => "Entretien supprimé et statut remis sur Contact.",
        'intake_remove_error'
            => "Impossible de supprimer l'entretien.",
        'answer_save_success'
            => 'Réponses enregistrées avec succès.',
        'answer_save_error'
            => "Impossible d'enregistrer la réponse.",
        'call_save_success'
            => "Appel enregistré avec succès.",
        'call_save_error'
            => "Impossible d'enregistrer l'appel.",
        'file_upload_success'
            => 'Fichier ajouté avec succès à la demande.',
        'file_upload_error'
            => 'Impossible de téléverser les fichiers.',
        'file_delete_success'
            => 'Fichier supprimé avec succès.',
        'file_delete_error'
            => 'Impossible de supprimer le fichier.',
        'status_lead_requires_intake'
            => "Échec ! Le statut « Lead » n’est possible que lorsque l’entretien d’intake a été marqué comme terminé.",
    ],

    'lead_modal' => [
        'title'   => 'Convertir en projet',
        'text'    => 'Êtes-vous sûr de vouloir convertir cette demande en projet ?',
        'confirm' => 'Oui, je suis sûr(e)',
        'cancel'  => 'Annuler',
    ],

    'errors' => [
        'intake_only_from_contact'
            => "Vous pouvez passer à « Entretien » uniquement si le statut actuel est « Contact ».",
        'lead_requires_intake'
            => "Échec ! Le statut « Lead » n’est possible que lorsque l’entretien est marqué comme terminé.",
    ],
];
