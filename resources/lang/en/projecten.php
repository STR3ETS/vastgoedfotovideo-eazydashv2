<?php

return [
    // Page & titles
    'page_title'     => 'Projects',
    'statuses_title' => 'Statuses',

    // Statuses
    'statuses' => [
        'preview'          => 'Preview',
        'waiting_customer' => 'Waiting for customer',
        'preview_approved' => 'Preview approved',
        'offerte'          => 'Quotation',
    ],

    'status_counts' => [
        'singular' => ':count project',
        'plural'   => ':count projects',
    ],

    // List
    'empty'           => 'There are no projects yet.',
    'unknown_company' => 'Unknown company',

    // Preview section
    'preview' => [
        'section_title'       => 'Preview URL',
        'view_preview_button' => 'View preview',
        'add_button_tooltip'  => 'Add or change preview URL',
        'current_label'       => 'Current preview',
        'none'                => 'No preview URL set yet.',
        'url_label'           => 'Preview URL',
        'url_placeholder'     => 'Sitejet preview URL',
        'save'                => 'Save',
        'saving'              => 'Saving...',
        'open_button'         => 'Open preview',
        'save_success'        => 'Success! The preview URL has been added to the project.',
        'save_error'          => 'Saving the preview URL failed.',
    ],

    // Request details (from aanvraag_websites)
    'request' => [
        'section_title'       => 'Request details',
        'id'                  => 'Request ID',
        'choice'              => 'Request type',
        'company'             => 'Company',
        'contact_name'        => 'Contact person',
        'contact_email'       => 'Email address',
        'contact_phone'       => 'Phone number',
        'created_at'          => 'Created at',
        'status'              => 'Status',
        'intake_at'           => 'Intake at',
        'intake_duration'     => 'Intake duration (minutes)',
        'intake_done'         => 'Intake completed',
        'intake_completed_at' => 'Intake completed at',
        'yes'                 => 'Yes',
        'no'                  => 'No',
    ],

    // Toasts (for JS)
    'toast' => [
        'status_update_success' => 'Success! The project status has been successfully updated.',
        'status_update_error'   => 'Failed! The project status has been changed unsuccessfully.',
    ],
];
