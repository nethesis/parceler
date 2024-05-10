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
        'sub_dir',
        'delay',
    ];

    /**
     * Generates the snapshot directory path from the name of the repository and the configuration.
     */
    public function snapshotDir(): string
    {
        return config('repositories.snapshots').'/'.$this->name;
    }

    /**
     * Generates the source directory path from the name of the repository and the configuration.
     */
    public function sourceDir(): string
    {
        if (is_null($this->sub_dir)) {
            return config('repositories.source_folder').'/'.$this->name;
        }

        return config('repositories.source_folder').'/'.$this->name.'/'.$this->sub_dir;
    }

    public function getStablePath(): string
    {
        $snapshotDir = $this->snapshotDir();
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
