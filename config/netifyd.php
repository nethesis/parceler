<?php

/**
 * Netifyd configuration
 */

return [
    'endpoint' => env('NETIFYD_API_ENDPOINT', 'https://agents.netify.ai'),
    'api-key' => env('NETIFYD_API_KEY'),
    'license-suffix' => env('NETIFYD_LICENSE_SUFFIX'),
    'rate-limit' => env('NETIFYD_RATE_LIMIT', 180),
    'rate-limit-start-hour' => env('NETIFYD_RATE_LIMIT_START_HOUR', 1),
    'rate-limit-end-hour' => env('NETIFYD_RATE_LIMIT_END_HOUR', 6),
];
