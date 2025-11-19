<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SERanking API key
    |--------------------------------------------------------------------------
    |
    | Te vinden in je SERanking account. Zet deze in je .env:
    | SERANKING_API_KEY=your_key_here
    |
    */

    'api_key' => env('SERANKING_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Basis URL
    |--------------------------------------------------------------------------
    */

    'base_url' => env('SERANKING_BASE_URL', 'https://api.seranking.com/v1'),

    /*
    |--------------------------------------------------------------------------
    | Default audit settings
    |--------------------------------------------------------------------------
    |
    | Dit zijn veilige defaults. Later kun je dit per klant / audit tunen
    | via $seoAudit->meta['settings'].
    |
    */

    'default_audit_settings' => [
        'source_site'      => 1,
        'source_sitemap'   => 1,
        'source_subdomain' => 0,
        'source_file'      => 0,
        'check_robots'     => 1,
        'ignore_params'    => 0,
        'custom_params'    => 'utm_source,utm_medium,utm_term,utm_content,utm_campaign',
        'ignore_noindex'   => 0,
        'ignore_nofollow'  => 0,
        'user_agent'       => 0,
        'max_pages'        => 1000,
        'max_depth'        => 10,
        'max_req'          => 100,
        'max_redirects'    => 5,
        'min_title_len'    => 20,
        'max_title_len'    => 65,
        'min_description_len' => 1,
        'max_description_len' => 158,
        'max_size'         => 3000,
        'min_words'        => 250,
        'max_h1_len'       => 100,
        'max_h2_len'       => 100,
    ],

];
