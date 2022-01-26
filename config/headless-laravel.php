<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Global search
    |--------------------------------------------------------------------------
    |
    | When using Route::headless(), the following path is used to locate
    | formations to pass to Route::seeker() to enable global search.
    | This config option can also be an array of formation.
    |
    */
    'search' => app_path('Http/Formations'),

    /*
    |--------------------------------------------------------------------------
    | Headless class paths
    |--------------------------------------------------------------------------
    |
    | When using Route::headless(), the following paths are used to determine
    | the package paths for things like formations or cards to route them.
    | You can change these paths if your application has a unique needs.
    |
    */
    'paths' => [

        'formations' => app_path('Http/Formations'),

        'cards' => app_path('Http/Cards'),
    ],
];
