<?php

return [
    /*
     * This configures the application endpoints that authenticates the requests through My.
     * Beware that the keys given here are used by the route that authenticates the machines.
     */
    'endpoints' => [
        'enterprise' => env('ENTERPRISE_ENDPOINT', 'https://my.nethesis.it/auth'),
        'community' => env('COMMUNITY_ENDPOINT', 'https://my.nethserver.com/api/machine/info'),
    ],

    /*
     * Name of the served repository, used in the URL and in the storage paths.
     */
    'name' => env('REPOSITORY_NAME', 'nethsecurity'),

    /*
     * Command ran by the worker to sync the repository into the source folder.
     */
    'command' => env('REPOSITORY_SYNC_COMMAND'),

    /*
     * Subdirectory of the source folder that holds the repository files, null if not needed.
     */
    'sub_dir' => env('REPOSITORY_SUB_DIR') ?: null,

    /*
     * How many days the served snapshot is kept back from upstream.
     */
    'delay' => (int) env('REPOSITORY_DELAY', 7),

    /*
     * Time of the day when the served snapshot is promoted.
     */
    'promote_at' => env('REPOSITORY_PROMOTE_AT', '03:00'),

    /*
     * This directory will contain to the repositories that are synced.
     * Subdirectories are expected to be named after the repository.
     */
    'source_folder' => env('REPOSITORY_SOURCE_FOLDER', 'source'),

    /*
     * This directory contains the snapshots of synced repositories.
     */
    'snapshots' => env('REPOSITORY_BASE_FOLDER', 'snapshots'),

    /*
     * Fresh release authentication token
     */
    'release_token' => env('REPOSITORY_RELEASE_TOKEN'),
];
