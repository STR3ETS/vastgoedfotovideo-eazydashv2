<?php

return [
    'page_title'     => 'Potentiële klanten',
    'statuses_title' => 'Statussen',

    'statuses' => [
        'prospect' => 'Prospect',
        'contact'  => 'Contact',
        'intake'   => 'Intake',
        'dead'     => 'Dead',
        'lead'     => 'Lead',
    ],

    'status_counts' => [
        'singular' => ':count project',
        'plural'   => ':count projecten',
    ],

    'filters' => [
        'prospect' => 'Prospect',
        'contact'  => 'Contact',
        'intake'   => 'Intake',
        'dead'     => 'Dead',
        'lead'     => 'Lead',
    ],

    'list' => [
        'no_requests'          => 'Nog geen aanvragen gevonden.',
        'no_results_for_state' => 'Geen resultaten gevonden voor deze status.',
    ],

    'intake_modal' => [
        'title'          => 'Plan een intakegesprek',
        'date_label'     => 'Datum',
        'duration_label' => 'Duur',
        'duration_30'    => '30 minuten',
        'duration_45'    => '45 minuten',
        'duration_60'    => '60 minuten',
        'cancel'         => 'Annuleren',
        'confirm'        => 'Bevestig & plan intake',
        'confirming'     => 'Bevestigen…',
    ],

    'intake_panel' => [
        'duration_prefix' => 'Duur:',
        'duration_suffix' => 'min',
        'mark_done'       => 'Intake markeren als voltooid',
        'delete'          => 'Verwijderen',
        'planned_badge'   => 'Ingepland',
        'completed_badge' => 'Voltooid',
        'delete_title'    => 'Intake verwijderen',
        'delete_question' => 'Weet je zeker dat je deze intakeplanning wilt verwijderen en de status terug wilt zetten naar :status?',
        'delete_yes'      => 'Ja, verwijderen',
        'delete_cancel'   => 'Annuleren',
        'today'           => 'Vandaag',
        'tomorrow'        => 'Morgen',
    ],

    'intake_questions' => [
        'section_title' => 'Intakegesprek',
        'notes_title'   => 'Notities',
        'notes_help'    => 'Wordt opgeslagen met de knop Opslaan.',
        'notes_missing' => 'Geen notities-veld gevonden in deze intake.',
        'save'          => 'Opslaan',
        'saving'        => 'Opslaan...',
        'open_panel_tooltip' => 'Open intakegesprek',
    ],

    'calls' => [
        'section_title'    => 'Belmomenten',
        'new_call_tooltip' => 'Nieuw belmoment',
        'new_call_title'   => 'Nieuw belmoment',
        'result_label'     => 'Resultaat',
        'result_none'      => 'Geen antwoord',
        'result_spoken'    => 'Gesproken',
        'note_label'       => 'Notitie',
        'note_placeholder' => 'Schrijf een notitie...',
        'save'             => 'Opslaan',
        'saving'           => 'Opslaan...',
        'none'             => 'Nog geen belmomenten geregistreerd.',
        'called_by'        => 'Gebeld door: :name',
        'badge_none'       => 'Gebeld: Geen antwoord',
        'badge_spoken'     => 'Gebeld: Gesproken',
        'result_choose'    => 'Kies resultaat',
    ],

    'files' => [
        'section_title'   => 'Bestanden',
        'drop_text'       => 'Sleep bestanden hierheen of :click om te kiezen',
        'drop_click'      => 'klik',
        'drop_help'       => 'Ondersteunde bestanden: afbeeldingen, PDF, Word, Excel, tekst, ZIP, enz.',
        'none'            => 'Nog geen bestanden geüpload.',
        'uploading'       => 'Uploaden...',
        'uploaded_on'     => 'Geüpload op :date',
        'open'            => 'Open',
        'delete'          => 'Verwijderen',
        'delete_title'    => 'Bestand verwijderen',
        'delete_question' => 'Weet je zeker dat je dit bestand wilt verwijderen?',
        'delete_yes'      => 'Ja, verwijderen',
        'delete_cancel'   => 'Annuleren',
    ],

    'logbook' => [
        'title' => 'Logboek',
        'empty' => 'Nog geen activiteit.',
        'by'    => 'Door: :name',
        'unknown_user' => 'Onbekend',
    ],

    'choices' => [
        'new'     => 'Nieuwe website',
        'renew'   => 'Website vernieuwen',
        'default' => 'Website-aanvraag',
    ],

    'toast' => [
        'status_intake_only_from_contact'
            => "Mislukt! Status 'Intake' kan alleen vanaf de status 'Contact' (nu: :current).",
        'status_dead_only_from_contact_or_intake'
            => "Mislukt! Status 'Dead' kan alleen vanaf de status 'Contact' of 'Intake' (nu: :current).",
        'status_already'
            => 'Mislukt! Deze aanvraag heeft al de status :status.',
        'status_update_success'
            => 'Gelukt! Status van de aanvraag succesvol gewijzigd naar :status.',
        'status_update_error'
            => 'Kon status niet bijwerken.',
        'intake_planned'
            => 'Intake ingepland en status bijgewerkt naar Intake.',
        'intake_plan_error'
            => 'Kon intake niet plannen.',
        'intake_completed'
            => 'Intake gemarkeerd als voltooid.',
        'intake_complete_error'
            => 'Kon intake niet afronden.',
        'intake_removed'
            => 'Intakeplanning verwijderd en status terug naar Contact.',
        'intake_remove_error'
            => 'Kon intake niet verwijderen.',
        'answer_save_success'
            => 'Antwoorden succesvol opgeslagen.',
        'answer_save_error'
            => 'Kon antwoord niet opslaan.',
        'call_save_success'
            => 'Belmoment succesvol opgeslagen.',
        'call_save_error'
            => 'Kon belmoment niet opslaan.',
        'file_upload_success'
            => 'Gelukt! Het bestand is succesvol toegevoegd aan de aanvraag.',
        'file_upload_error'
            => 'Kon bestanden niet uploaden.',
        'file_delete_success'
            => 'Gelukt! Het bestand is succesvol verwijderd en ontkoppelt van de aanvraag.',
        'file_delete_error'
            => 'Kon bestand niet verwijderen.',
        'status_lead_requires_intake'
            => "Mislukt! Status 'Lead' kan pas wanneer het intakegesprek is gemarkeerd als voltooid.",
    ],

    'lead_modal' => [
        'title'   => 'Omzetten naar project',
        'text'    => 'Weet je zeker dat je de aanvraag wilt omzetten naar een project?',
        'confirm' => 'Ja, ik weet het zeker',
        'cancel'  => 'Annuleren',
    ],

    'errors' => [
        'intake_only_from_contact'
            => "Je kunt alleen naar 'Intake' gaan als de huidige status 'Contact' is.",
        'lead_requires_intake'
            => "Mislukt! Status 'Lead' kan pas wanneer het intakegesprek is gemarkeerd als voltooid.",
    ],
];
