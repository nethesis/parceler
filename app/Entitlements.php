<?php

namespace App;

enum Entitlements: string
{
    case PROC_AGGREGATOR = 'netify-proc-aggregator';
    case PROC_FLOW_ACTIONS = 'netify-proc-flow-actions';
    case PROC_DEV_DISCOVERY = 'netify-proc-dev-discovery';

    /**
     * Get all configured entitlements.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::PROC_AGGREGATOR->value,
            self::PROC_FLOW_ACTIONS->value,
            self::PROC_DEV_DISCOVERY->value,
        ];
    }
}
