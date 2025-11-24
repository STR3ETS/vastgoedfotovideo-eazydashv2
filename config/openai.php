<?php

return [

    /*
    |--------------------------------------------------------------------------
    | OpenAI API key
    |--------------------------------------------------------------------------
    |
    | Zet in je .env bijvoorbeeld:
    | OPENAI_API_KEY=sk-...
    |
    */

    'api_key' => env('OPENAI_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Standaard model
    |--------------------------------------------------------------------------
    |
    | Je kunt dit aanpassen als je een ander model wilt testen.
    |
    */

    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),

];
