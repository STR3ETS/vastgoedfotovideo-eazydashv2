<?php

return [

    'page_title' => 'SEO audits',

    'filters' => [
        'company'        => 'Client / company',
        'all_companies'  => 'All companies',
        'status'         => 'Status',
        'all_statuses'   => 'All statuses',
        'apply'          => 'Apply filters',
        'reset'          => 'Reset',
    ],

    'new_audit' => [
        'title'              => 'Start a new SEO audit',
        'subtitle'           => 'Select a client and audit type. The analysis runs in the background.',
        'company'            => 'Client / company',
        'company_placeholder'=> 'Select a company',
        'domain'             => 'Domain (optional)',
        'domain_placeholder' => 'Example: example.com',
        'domain_help'        => 'Leave empty to automatically use the company domain (if available).',
        'type'               => 'Audit type',
        'country'            => 'Country',
        'locale'             => 'Language / locale',
        'start'              => 'Start SEO audit',
    ],

    'list' => [
        'title'        => 'Audit history',
        'count_suffix' => 'audits',
        'empty'        => 'No SEO audits performed yet.',
    ],

    'detail' => [
        'title'        => 'Latest audit',
        'subtitle'     => 'Summary of the most recently executed audit.',
        'overall_score'=> 'Overall score',
        'sections'     => 'Key categories',
        'issues_found' => 'issues found',
        'no_sections'  => 'Once the SE Ranking connection is active, scores will appear here.',
        'empty_title'  => 'No audits yet',
        'empty_text'   => 'Start your first audit in the left panel. A summary will appear here afterwards.',
    ],

];
