<?php

return [

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
