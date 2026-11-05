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
        'max_users' => env('MAX_USERS', 16),
        'daylimit' => env('DAILY_USER_LIMIT', 8),
    ],

    /*
    |--------------------------------------------------------------------------
    | Report Targets
    |--------------------------------------------------------------------------
    |
    | Objetivos que se muestran en el informe personal (/report/me).
    |
    */
    'report' => [
        'monthly_hours_target' => (int) env('REPORT_MONTHLY_HOURS_TARGET', 160),
        'yearly_holidays_target' => (int) env('REPORT_YEARLY_HOLIDAYS_TARGET', 22),
    ],
];
