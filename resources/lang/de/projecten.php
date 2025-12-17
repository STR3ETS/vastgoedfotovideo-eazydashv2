<?php

return [
    // Seite & Titel
    'page_title'     => 'Projekte',
    'statuses_title' => 'Status',

    // Status
    'statuses' => [
        'preview'          => 'Vorschau',
        'waiting_customer' => 'Warten auf den Kunden',
        'preview_approved' => 'Vorschau genehmigt',
        'offerte'          => 'Zitat',
    ],

    'status_counts' => [
        'singular' => ':count Projekt',
        'plural'   => ':count Projekte',
    ],

    // Liste
    'empty'           => 'Es gibt noch keine Projekte.',
    'unknown_company' => 'Unbekanntes Unternehmen',

    // Vorschau-Bereich
    'preview' => [
        'section_title'       => 'Vorschau-URL',
        'view_preview_button' => 'Vorschau anzeigen',
        'add_button_tooltip'  => 'Vorschau-URL hinzufügen oder ändern',
        'current_label'       => 'Aktuelle Vorschau',
        'none'                => 'Noch keine Vorschau-URL festgelegt.',
        'url_label'           => 'Vorschau-URL',
        'url_placeholder'     => 'Sitejet-Vorschau-URL',
        'save'                => 'Speichern',
        'saving'              => 'Wird gespeichert…',
        'open_button'         => 'Vorschau öffnen',
        'save_success'        => 'Erfolg! Die Vorschau-URL wurde dem Projekt hinzugefügt.',
        'save_error'          => 'Das Speichern der Vorschau-URL ist fehlgeschlagen.',
    ],

    // Anfragedaten (aus aanvraag_websites)
    'request' => [
        'section_title'       => 'Anfragedaten',
        'id'                  => 'Anfrage-ID',
        'choice'              => 'Art der Anfrage',
        'company'             => 'Unternehmen',
        'contact_name'        => 'Ansprechpartner',
        'contact_email'       => 'E-Mail-Adresse',
        'contact_phone'       => 'Telefonnummer',
        'created_at'          => 'Erstellt am',
        'status'              => 'Status',
        'intake_at'           => 'Intake am',
        'intake_duration'     => 'Intake-Dauer (Minuten)',
        'intake_done'         => 'Intake abgeschlossen',
        'intake_completed_at' => 'Intake abgeschlossen am',
        'yes'                 => 'Ja',
        'no'                  => 'Nein',
    ],

    // Toasts (für JS)
    'toast' => [
        'status_update_success' => 'Erfolg! Der Projektstatus wurde erfolgreich aktualisiert.',
        'status_update_error'   => 'Fehlgeschlagen! Der Projektstatus konnte nicht geändert werden.',
    ],
];
