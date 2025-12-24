<?php

return [

    /*
    |--------------------------------------------------------------------------
    | World Tables Configuration
    |--------------------------------------------------------------------------
    |
    | Define database table names used by the world module.
    | You may override these via environment variables if needed.
    |
    */

    'tables' => [

        'continents' => env('WORLD_TABLE_CONTINENTS', 'world_continents'),

        'subregions' => env('WORLD_TABLE_SUBREGIONS', 'world_subregions'),

        'countries'  => env('WORLD_TABLE_COUNTRIES', 'world_countries'),

        'states'     => env('WORLD_TABLE_STATES', 'world_states'),

        'cities'     => env('WORLD_TABLE_CITIES', 'world_cities'),

        'languages'  => env('WORLD_TABLE_LANGUAGES', 'world_languages'),

        'currencies' => env('WORLD_TABLE_CURRENCIES', 'world_currencies'),

        'timezones'  => env('WORLD_TABLE_TIMEZONES', 'world_timezones'),

        /*
        | Polymorphic Pivot Table
        |
        | Used for morph relationships (e.g. countryable, stateable, etc.)
        */
        'worldables' => env('WORLD_TABLE_WORLDABLES', 'worldables'),
    ],

];
