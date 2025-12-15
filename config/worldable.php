<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize table names to match your naming convention.
    |
    */
    'tables' => [
        'continents' => 'world_continents',
        'subregions' => 'world_subregions',
        'countries' => 'world_countries',
        'states' => 'world_states',
        'cities' => 'world_cities',
        'languages' => 'world_languages',
        'currencies' => 'world_currencies',
        'timezones' => 'world_timezones',
        'worldables' => 'worldables',  // The polymorphic pivot table
    ],

];
