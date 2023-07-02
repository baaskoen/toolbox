<?php

return [
    /**
     * https://developers.google.com/custom-search/v1/introduction
     */
    'google' => [
        'api_key' => env('GOOGLE_API_KEY'),
        'search_engine_id' => env('GOOGLE_SEARCH_ENGINE_ID')
    ],

    /**
     * https://developers.kvk.nl/documentation
     */
    'kvk' => [
        'api_key' => env('KVK_API_KEY'),
    ],

    /**
     * https://docs.brandfetch.com/reference/get-started
     */
    'brandfetch' => [
        'api_key' => env('BRANDFETCH_API_KEY')
    ]
];
