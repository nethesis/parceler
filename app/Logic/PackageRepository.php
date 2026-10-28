<?php

//
// Copyright (C) 2026 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Logic;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PackageRepository
{
    public function __construct(
        public readonly string $name,
        public readonly string $sourceFolder,
        public readonly string $snapshotsFolder,
        public readonly ?string $subDir,
        public readonly int $delay,
    ) {}

    /**
     * Directory holding the snapshots, the current pointer and the frozen marker.
     */
    public function snapshotDir(): string
    {
        return $this->snapshotsFolder.'/'.$this->name;
    }

    /**
     * Directory where the sync command saves the repository files.
     */
    public function sourceDir(): string
    {
        if (is_null($this->subDir)) {
            return $this->sourceFolder.'/'.$this->name;
        }

        return $this->sourceFolder.'/'.$this->name.'/'.$this->subDir;
    }

    /**
     * Snapshot names, oldest first.
     *
     * @return Collection<int, string>
     */
    public function snapshots(): Collection
    {
        return collect(Storage::directories($this->snapshotDir()))
            ->map(fn (string $directory): string => basename($directory))
            ->filter(fn (string $snapshot): bool => Carbon::hasFormat($snapshot, DATE_ATOM))
            ->sortBy(fn (string $snapshot): Carbon => Carbon::createFromFormat(DATE_ATOM, $snapshot))
            ->values();
    }

    /**
     * Name of the snapshot being served, null if none has been promoted yet.
     */
    public function current(): ?string
    {
        return Cache::rememberForever($this->currentCacheKey(), fn (): ?string => Storage::get($this->snapshotDir().'/current'));
    }

    /**
     * Directory being served, null if none has been promoted yet.
     */
    public function servingDir(): ?string
    {
        $current = $this->current();
        if (is_null($current)) {
            return null;
        }

        return $this->snapshotDir().'/'.$current;
    }

    public function setCurrent(string $snapshot): void
    {
        Storage::put($this->snapshotDir().'/current', $snapshot);
        Cache::forget($this->currentCacheKey());
    }

    public function isFrozen(): bool
    {
        return Storage::fileExists($this->snapshotDir().'/frozen');
    }

    public function freeze(): void
    {
        Storage::put($this->snapshotDir().'/frozen', '');
    }

    public function unfreeze(): void
    {
        Storage::delete($this->snapshotDir().'/frozen');
    }

    /**
     * Serves the oldest snapshot within the delay, returns the snapshot served afterwards.
     *
     * Does nothing when frozen or when no snapshot is within the delay.
     */
    public function promote(): ?string
    {
        if ($this->isFrozen()) {
            return $this->current();
        }

        $snapshot = $this->snapshots()->first(function (string $snapshot): bool {
            return Carbon::createFromFormat(DATE_ATOM, $snapshot)->isBetween(now()->subDays($this->delay), now());
        });
        if (! is_null($snapshot)) {
            $this->setCurrent($snapshot);
        }

        return $this->current();
    }

    private function currentCacheKey(): string
    {
        return "repository:{$this->name}:current";
    }
}
