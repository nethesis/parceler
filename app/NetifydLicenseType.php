<?php

namespace App;

use Illuminate\Support\Str;

enum NetifydLicenseType: string
{
    case COMMUNITY = 'community';
    case ENTERPRISE = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::COMMUNITY => 'NethSecurity Community Edition',
            self::ENTERPRISE => 'NethSecurity Enterprise Edition',
        };
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
