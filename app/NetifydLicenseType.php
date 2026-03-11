<?php

namespace App;

use Illuminate\Support\Str;

enum NetifydLicenseType: string
{
    case COMMUNITY = 'community';
    case ENTERPRISE = 'enterprise';

    public function label(): string
    {
        $label = match ($this) {
            self::COMMUNITY => 'NethSecurity Community Edition',
            self::ENTERPRISE => 'NethSecurity Enterprise Edition',
        };

        $suffix = config('netifyd.license-suffix');
        if ($suffix != null) {
            $label .= ' '.$suffix;
        }

        return $label;
    }

    public function cacheLabel(): string
    {
        return Str::slug($this->label());
    }

    public function durationDays(): int
    {
        return 7;
    }
}
