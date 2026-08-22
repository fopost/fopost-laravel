<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    |
    | Create one at https://app.fopost.com/api-keys and put it in your .env
    | as FOPOST_API_KEY. Never commit it.
    |
    */

    'api_key' => env('FOPOST_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The FoPost API root. A host with no path gets the versioned API path
    | appended for you, so https://api.fopost.com is enough.
    |
    */

    'base_url' => env('FOPOST_API_URL', 'https://api.fopost.com'),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Seconds to wait for a single request before giving up.
    |
    */

    'timeout' => (float) env('FOPOST_API_TIMEOUT', 30.0),

    /*
    |--------------------------------------------------------------------------
    | Retries
    |--------------------------------------------------------------------------
    |
    | How many attempts a rate limited request gets. The client waits for the
    | interval the API asks for between attempts. Minimum 1.
    |
    */

    'max_retries' => (int) env('FOPOST_API_MAX_RETRIES', 3),
];
