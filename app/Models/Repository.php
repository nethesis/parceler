<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
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

    protected static function booted(): void
    {
        // freeze, unfreeze and release change which snapshot is served
        static::saved(function (Repository $repository): void {
            $repository->forgetStablePath();
        });
    }

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
            // Listing the snapshots is a remote call on S3, so the result is cached
            $stable = Cache::remember($this->stablePathCacheKey(), now()->addHour(), function () use ($snapshotDir): ?string {
                return collect(Storage::directories($snapshotDir))
                    // filter out unparsable directories
                    ->filter(function (string $filePath): bool {
                        return Carbon::hasFormat(basename($filePath), DATE_ATOM);
                    })
                    // create carbon instances from the directory names
                    ->map(function (string $filePath): Carbon {
                        return Carbon::createFromFormat(DATE_ATOM, basename($filePath));
                    })
                    // filter out directories older than the delay
                    ->filter(function (Carbon $date): bool {
                        return $date->isBetween(now(), now()->subDays($this->delay));
                    })
                    // sort the directories by date
                    ->sort(function (Carbon $a, Carbon $b): int {
                        return $b->diffInSeconds($a);
                    })
                    // get the first directory
                    ->map(function (Carbon $date): string {
                        return $date->toAtomString();
                    })
                    ->first();
            });

            return "$snapshotDir/$stable";
        } else {
            return "$snapshotDir/$this->freeze";
        }
    }

    /**
     * Forgets the cached stable path, must be called whenever snapshots are added or removed.
     */
    public function forgetStablePath(): void
    {
        Cache::forget($this->stablePathCacheKey());
    }

    private function stablePathCacheKey(): string
    {
        return "repository-stable-path-$this->id";
    }
}
