<?php

return [
    /*
     * This configures the application endpoints that authenticates the requests through My.
     * Beware that the keys given here are used by the route that authenticates the machines.
     */
    'endpoints' => [
        'enterprise' => env('ENTERPRISE_ENDPOINT', 'https://my.nethesis.it'),
        'community' => env('COMMUNITY_ENDPOINT', 'https://my.nethserver.com'),
    ],

    /*
     * This directory contains the snapshots of synced repositories.
     */
    'directory' => env('REPOSITORY_BASE_FOLDER', 'repositories'),
];
