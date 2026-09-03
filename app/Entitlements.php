<?php

namespace App;

enum Entitlements: string
{
    case PROC_AGGREGATOR = 'netify-proc-aggregator';
    case PROC_FLOW_ACTIONS = 'netify-proc-flow-actions';
    case APPLICATION_SIGNATURES = 'application-signatures';

    /**
     * Get the entitlements configured for a license type.
     *
     * @return array<string>
     */
    public static function for(NetifydLicenseType $licenseType): array
    {
        $entitlements = [
            self::PROC_AGGREGATOR->value,
            self::PROC_FLOW_ACTIONS->value,
        ];

        if ($licenseType === NetifydLicenseType::ENTERPRISE) {
            $entitlements[] = self::APPLICATION_SIGNATURES->value;
        }

        return $entitlements;
    }
}
