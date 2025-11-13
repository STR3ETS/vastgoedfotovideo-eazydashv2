<?php

return [
    'page_title'     => 'Potential customers',
    'statuses_title' => 'Statuses',

    'statuses' => [
        'prospect' => 'Prospect',
        'contact'  => 'Contact',
        'intake'   => 'Intake',
        'dead'     => 'Dead',
        'lead'     => 'Lead',
    ],

    'status_counts' => [
        'singular' => ':count project',
        'plural'   => ':count projects',
    ],

    'filters' => [
        'prospect' => 'Prospect',
        'contact'  => 'Contact',
        'intake'   => 'Intake',
        'dead'     => 'Dead',
        'lead'     => 'Lead',
    ],

    'list' => [
        'no_requests'          => 'No requests found yet.',
        'no_results_for_state' => 'No results found for this status.',
    ],

    'intake_modal' => [
        'title'          => 'Schedule an intake call',
        'date_label'     => 'Date',
        'duration_label' => 'Duration',
        'duration_30'    => '30 minutes',
        'duration_45'    => '45 minutes',
        'duration_60'    => '60 minutes',
        'cancel'         => 'Cancel',
        'confirm'        => 'Confirm & schedule intake',
        'confirming'     => 'Confirming…',
    ],

    'intake_panel' => [
        'duration_prefix' => 'Duration:',
        'duration_suffix' => 'min',
        'mark_done'       => 'Mark intake as completed',
        'delete'          => 'Delete',
        'planned_badge'   => 'Scheduled',
        'completed_badge' => 'Completed',
        'delete_title'    => 'Delete intake',
        'delete_question' => 'Are you sure you want to delete this intake and reset the status back to :status?',
        'delete_yes'      => 'Yes, delete',
        'delete_cancel'   => 'Cancel',
        'today'           => 'Today',
        'tomorrow'        => 'Tomorrow',
    ],

    'intake_questions' => [
        'section_title' => 'Intake call',
        'notes_title'   => 'Notes',
        'notes_help'    => 'Will be saved with the Save button.',
        'notes_missing' => 'No notes field found for this intake.',
        'save'          => 'Save',
        'saving'        => 'Saving...',
        'open_panel_tooltip' => 'Open intake interview',
    ],

    'calls' => [
        'section_title'    => 'Call moments',
        'new_call_tooltip' => 'New call',
        'new_call_title'   => 'New call',
        'result_label'     => 'Result',
        'result_none'      => 'No answer',
        'result_spoken'    => 'Spoke to customer',
        'note_label'       => 'Note',
        'note_placeholder' => 'Write a note...',
        'save'             => 'Save',
        'saving'           => 'Saving...',
        'none'             => 'No calls logged yet.',
        'called_by'        => 'Called by: :name',
        'badge_none'       => 'Called: No answer',
        'badge_spoken'     => 'Called: Spoke',
        'result_choose'    => 'Choose result',
    ],

    'files' => [
        'section_title'   => 'Files',
        'drop_text'       => 'Drop files here or :click to choose',
        'drop_click'      => 'click',
        'drop_help'       => 'Supported files: images, PDF, Word, Excel, text, ZIP, etc.',
        'none'            => 'No files uploaded yet.',
        'uploading'       => 'Uploading...',
        'uploaded_on'     => 'Uploaded on :date',
        'open'            => 'Open',
        'delete'          => 'Delete',
        'delete_title'    => 'Delete file',
        'delete_question' => 'Are you sure you want to delete this file?',
        'delete_yes'      => 'Yes, delete',
        'delete_cancel'   => 'Cancel',
    ],

    'logbook' => [
        'title'        => 'Logbook',
        'empty'        => 'No activity yet.',
        'by'           => 'By: :name',
        'unknown_user' => 'Unknown',
    ],

    'choices' => [
        'new'     => 'New website',
        'renew'   => 'Renew website',
        'default' => 'Website request',
    ],

    'toast' => [
        'status_intake_only_from_contact'
            => "Failed! Status 'Intake' is only allowed from status 'Contact' (current: :current).",
        'status_dead_only_from_contact_or_intake'
            => "Failed! Status 'Dead' is only allowed from status 'Contact' or 'Intake' (current: :current).",
        'status_already'
            => 'Failed! This request already has status :status.',
        'status_update_success'
            => 'Success! Status updated to :status.',
        'status_update_error'
            => 'Could not update status.',
        'intake_planned'
            => 'Intake scheduled and status updated to Intake.',
        'intake_plan_error'
            => 'Could not schedule intake.',
        'intake_completed'
            => 'Intake marked as completed.',
        'intake_complete_error'
            => 'Could not complete intake.',
        'intake_removed'
            => 'Intake removed and status reset to Contact.',
        'intake_remove_error'
            => 'Could not remove intake.',
        'answer_save_success'
            => 'Answers saved successfully.',
        'answer_save_error'
            => 'Could not save answer.',
        'call_save_success'
            => 'Call saved successfully.',
        'call_save_error'
            => 'Could not save call.',
        'file_upload_success'
            => 'Success! File has been attached to the request.',
        'file_upload_error'
            => 'Could not upload files.',
        'file_delete_success'
            => 'Success! File has been removed from the request.',
        'file_delete_error'
            => 'Could not delete file.',
        'status_lead_requires_intake'
            => "Failed! Status 'Lead' is only allowed once the intake meeting has been marked as completed.",
    ],

    'lead_modal' => [
        'title'   => 'Convert to project',
        'text'    => 'Are you sure you want to convert this request into a project?',
        'confirm' => 'Yes, I’m sure',
        'cancel'  => 'Cancel',
    ],

    'errors' => [
        'intake_only_from_contact'
            => "You can only move to 'Intake' when the current status is 'Contact'.",
        'lead_requires_intake'
            => "Failed! Status 'Lead' is only allowed after the intake has been marked as completed.",
    ],
];
