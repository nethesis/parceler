<?php

return [
    /*
     * This configures the application endpoints that authenticates the requests through My.
     * Beware that the keys given here are used by the route that authenticates the machines.
     */
    'endpoints' => [
        'enterprise' => env('ENTERPRISE_ENDPOINT', 'https://my.nethesis.it/auth/product/nethsecurity'),
        'community' => env('COMMUNITY_ENDPOINT', 'https://my.nethserver.com/api/machine/info'),
    ],

    /*
     * This directory will contain to the repositories that are synced.
     * Subdirectories are expected to be named after the repository.
     */
    'source_folder' => env('REPOSITORY_SOURCE_FOLDER', 'source'),

    /*
     * This directory contains the snapshots of synced repositories.
     */
    'snapshots' => env('REPOSITORY_BASE_FOLDER', 'snapshots'),
];
