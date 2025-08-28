<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */

    'default_filesystem_disk' => 'products',

    'assets' => [
        'enable_hot_reload' => false,
    ],

    'database' => [
        'relations' => [
            'morphMap' => [
                // Define morph map for better performance
            ],
        ],
    ],

    'global_search' => [
        'field_suffix' => '',
    ],

];
