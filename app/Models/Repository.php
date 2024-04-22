<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Repository extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'command',
        'source_folder',
        'delay',
    ];

    public function getStablePath(): string
    {
        $snapshotDir = config('repositories.directory').'/'.$this->name;
        if (is_null($this->freeze)) {
            $stable = collect(Storage::directories($snapshotDir))
                ->map(function (string $filePath): Carbon {
                    return Carbon::createFromFormat(DATE_ATOM, basename($filePath));
                })
                ->filter(function (Carbon $date): bool {
                    return $date->isBetween(now(), now()->subDays($this->delay));
                })
                ->sort(function (Carbon $a, Carbon $b): int {
                    return $b->diffInSeconds($a);
                })
                ->map(function (Carbon $date): string {
                    return $date->toAtomString();
                })
                ->first();

            return "$snapshotDir/$stable";
        } else {
            return "$snapshotDir/$this->freeze";
        }
    }
}
