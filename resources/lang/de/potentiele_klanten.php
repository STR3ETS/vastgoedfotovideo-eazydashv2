<?php

return [
    'page_title'     => 'Potenzielle Kunden',
    'statuses_title' => 'Status',

    'statuses' => [
        'prospect' => 'Interessent',
        'contact'  => 'Kontakt',
        'intake'   => 'Erstgespräch',
        'dead'     => 'Verloren',
        'lead'     => 'Lead',
    ],

    'status_counts' => [
        'singular' => ':count Projekt',
        'plural'   => ':count Projekte',
    ],

    'filters' => [
        'prospect' => 'Interessent',
        'contact'  => 'Kontakt',
        'intake'   => 'Erstgespräch',
        'dead'     => 'Verloren',
        'lead'     => 'Lead',
    ],

    'list' => [
        'no_requests'          => 'Noch keine Anfragen gefunden.',
        'no_results_for_state' => 'Keine Ergebnisse für diesen Status gefunden.',
    ],

    'intake_modal' => [
        'title'          => 'Erstgespräch planen',
        'date_label'     => 'Datum',
        'duration_label' => 'Dauer',
        'duration_30'    => '30 Minuten',
        'duration_45'    => '45 Minuten',
        'duration_60'    => '60 Minuten',
        'cancel'         => 'Abbrechen',
        'confirm'        => 'Bestätigen & Gespräch planen',
        'confirming'     => 'Bestätigen…',
    ],

    'intake_panel' => [
        'duration_prefix' => 'Dauer:',
        'duration_suffix' => 'Min',
        'mark_done'       => 'Erstgespräch als erledigt markieren',
        'delete'          => 'Löschen',
        'planned_badge'   => 'Geplant',
        'completed_badge' => 'Erledigt',
        'delete_title'    => 'Erstgespräch löschen',
        'delete_question' => 'Möchten Sie dieses Erstgespräch wirklich löschen und den Status auf :status zurücksetzen?',
        'delete_yes'      => 'Ja, löschen',
        'delete_cancel'   => 'Abbrechen',
        'today'           => 'Heute',
        'tomorrow'        => 'Morgen',
    ],

    'intake_questions' => [
        'section_title' => 'Erstgespräch',
        'notes_title'   => 'Notizen',
        'notes_help'    => 'Wird mit der Schaltfläche Speichern gespeichert.',
        'notes_missing' => 'Kein Notizfeld für dieses Erstgespräch gefunden.',
        'save'          => 'Speichern',
        'saving'        => 'Speichern...',
        'open_panel_tooltip' => 'Offenes Aufnahmegespräch',
    ],

    'calls' => [
        'section_title'    => 'Anrufprotokoll',
        'new_call_tooltip' => 'Neuer Anruf',
        'new_call_title'   => 'Neuer Anruf',
        'result_label'     => 'Ergebnis',
        'result_none'      => 'Keine Antwort',
        'result_spoken'    => 'Gespräch geführt',
        'note_label'       => 'Notiz',
        'note_placeholder' => 'Notiz schreiben...',
        'save'             => 'Speichern',
        'saving'           => 'Speichern...',
        'none'             => 'Noch keine Anrufe protokolliert.',
        'called_by'        => 'Angerufen von: :name',
        'badge_none'       => 'Anruf: Keine Antwort',
        'badge_spoken'     => 'Anruf: Gespräch geführt',
        'result_choose'    => 'Ergebnis auswählen',
    ],

    'files' => [
        'section_title'   => 'Dateien',
        'drop_text'       => 'Dateien hierher ziehen oder :click, um auszuwählen',
        'drop_click'      => 'klicken',
        'drop_help'       => 'Unterstützte Dateien: Bilder, PDF, Word, Excel, Text, ZIP usw.',
        'none'            => 'Noch keine Dateien hochgeladen.',
        'uploading'       => 'Hochladen...',
        'uploaded_on'     => 'Hochgeladen am :date',
        'open'            => 'Öffnen',
        'delete'          => 'Löschen',
        'delete_title'    => 'Datei löschen',
        'delete_question' => 'Möchten Sie diese Datei wirklich löschen?',
        'delete_yes'      => 'Ja, löschen',
        'delete_cancel'   => 'Abbrechen',
    ],

    'logbook' => [
        'title'        => 'Protokoll',
        'empty'        => 'Noch keine Aktivitäten.',
        'by'           => 'Von: :name',
        'unknown_user' => 'Unbekannt',
    ],

    'choices' => [
        'new'     => 'Neue Website',
        'renew'   => 'Website erneuern',
        'default' => 'Website-Anfrage',
    ],

    'toast' => [
        'status_intake_only_from_contact'
            => "Fehlgeschlagen! Der Status „Erstgespräch“ ist nur vom Status „Kontakt“ aus erlaubt (aktuell: :current).",
        'status_dead_only_from_contact_or_intake'
            => "Fehlgeschlagen! Der Status „Verloren“ ist nur von „Kontakt“ oder „Erstgespräch“ aus erlaubt (aktuell: :current).",
        'status_already'
            => 'Fehlgeschlagen! Diese Anfrage hat bereits den Status :status.',
        'status_update_success'
            => 'Erfolg! Status wurde auf :status aktualisiert.',
        'status_update_error'
            => 'Status konnte nicht aktualisiert werden.',
        'intake_planned'
            => 'Erstgespräch geplant und Status auf Erstgespräch gesetzt.',
        'intake_plan_error'
            => 'Erstgespräch konnte nicht geplant werden.',
        'intake_completed'
            => 'Erstgespräch als erledigt markiert.',
        'intake_complete_error'
            => 'Erstgespräch konnte nicht abgeschlossen werden.',
        'intake_removed'
            => 'Erstgespräch gelöscht und Status auf Kontakt zurückgesetzt.',
        'intake_remove_error'
            => 'Erstgespräch konnte nicht gelöscht werden.',
        'answer_save_success'
            => 'Antworten erfolgreich gespeichert.',
        'answer_save_error'
            => 'Antwort konnte nicht gespeichert werden.',
        'call_save_success'
            => 'Anruf erfolgreich gespeichert.',
        'call_save_error'
            => 'Anruf konnte nicht gespeichert werden.',
        'file_upload_success'
            => 'Datei wurde der Anfrage erfolgreich hinzugefügt.',
        'file_upload_error'
            => 'Dateien konnten nicht hochgeladen werden.',
        'file_delete_success'
            => 'Datei wurde erfolgreich entfernt.',
        'file_delete_error'
            => 'Datei konnte nicht gelöscht werden.',
        'status_lead_requires_intake'
            => "Fehlgeschlagen! Der Status 'Lead' ist erst möglich, wenn das Intake-Gespräch als abgeschlossen markiert wurde.",
    ],

    'lead_modal' => [
        'title'   => 'In Projekt umwandeln',
        'text'    => 'Sind Sie sicher, dass Sie diese Anfrage in ein Projekt umwandeln möchten?',
        'confirm' => 'Ja, ich bin sicher',
        'cancel'  => 'Abbrechen',
    ],

    'errors' => [
        'intake_only_from_contact'
            => "Sie können nur dann zum „Erstgespräch“ wechseln, wenn der aktuelle Status „Kontakt“ ist.",
        'lead_requires_intake'
            => "Fehlgeschlagen! Der Status „Lead“ ist erst möglich, wenn das Erstgespräch als erledigt markiert wurde.",
    ],
];
