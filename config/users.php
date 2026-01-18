<?php

return [
    /*
    |--------------------------------------------------------------------------
    | User Limits Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for user management,
    | including limits and constraints for user creation.
    |
    */

    'limits' => [
        /*
        |--------------------------------------------------------------------------
        | Maximum Users
        |--------------------------------------------------------------------------
        |
        | The maximum number of users that can be created in the system.
        | Set to null or 0 to disable the limit.
        |
        */
        'max_users' => env('MAX_USERS', 96),
        'daylimit' => env('MAX_USERS', 4),
    ],
];
