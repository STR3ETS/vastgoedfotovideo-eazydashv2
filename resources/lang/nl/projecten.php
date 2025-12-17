<?php

return [
    // Pagina & titels
    'page_title'     => 'Projecten',
    'statuses_title' => 'Statussen',

    // Statussen
    'statuses' => [
        'preview'          => 'Preview',
        'waiting_customer' => 'Wachten op klant',
        'preview_approved' => 'Preview goedgekeurd',
        'offerte'          => 'Offerte',
    ],

    'status_counts' => [
        'singular' => ':count project',
        'plural'   => ':count projecten',
    ],

    // Lijst
    'empty'           => 'Er zijn nog geen projecten.',
    'unknown_company' => 'Onbekend bedrijf',

    // Preview-sectie
    'preview' => [
        'section_title'       => 'Preview',
        'view_preview_button' => 'Bekijk preview',
        'add_button_tooltip'  => 'Preview-URL toevoegen of wijzigen',
        'current_label'       => 'Huidige preview',
        'none'                => 'Nog geen preview-URL ingesteld.',
        'url_label'           => 'Preview-URL',
        'url_placeholder'     => 'Sitejet Preview-URL',
        'save'                => 'Opslaan',
        'saving'              => 'Bezig met opslaan...',
        'open_button'         => 'Open preview',
        'save_success'        => 'Gelukt! De preview-url is toegevoegd aan het project.',
        'save_error'          => 'Opslaan van de preview-URL is mislukt.',
    ],

    // Aanvraaggegevens (uit aanvraag_websites)
    'request' => [
        'section_title'       => 'Aanvraaggegevens',
        'id'                  => 'Aanvraag-ID',
        'choice'              => 'Soort aanvraag',
        'company'             => 'Bedrijf',
        'contact_name'        => 'Contactpersoon',
        'contact_email'       => 'E-mailadres',
        'contact_phone'       => 'Telefoonnummer',
        'created_at'          => 'Aangemaakt op',
        'status'              => 'Status',
        'intake_at'           => 'Intake op',
        'intake_duration'     => 'Intakeduur (minuten)',
        'intake_done'         => 'Intake afgerond',
        'intake_completed_at' => 'Intake afgerond op',
        'yes'                 => 'Ja',
        'no'                  => 'Nee',
    ],

    // Toasts (voor JS)
    'toast' => [
        'status_update_success' => 'Gelukt! De status van het project is succesvol aangepast.',
        'status_update_error'   => 'Mislukt! De status van het project is onsuccesvol aangepast.',
    ],
];
