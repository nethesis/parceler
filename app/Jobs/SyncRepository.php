<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Jobs;

use App\Logic\PackageRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

#[Timeout(7200)]
class SyncRepository implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Sync the repository, snapshot it and delete the snapshots older than the delay.
     */
    public function handle(PackageRepository $repository): void
    {
        Log::debug("Syncing repository {$repository->name}.");
        Process::forever()
            ->run(config('repositories.command'))
            ->throw();
        Log::debug("Repository {$repository->name} synced.");

        $targetDir = $repository->snapshotDir().'/'.now()->toAtomString();
        Log::debug("Starting snapshot of {$repository->name} at $targetDir.");
        Storage::makeDirectory($targetDir);
        foreach (Storage::allFiles($repository->sourceDir()) as $file) {
            // Remove source directory from the file path
            $dest = $targetDir.'/'.substr($file, strlen($repository->sourceDir()) + 1);
            Storage::copy($file, $dest);
        }
        Log::debug("Snapshot created successfully at $targetDir.");

        foreach ($repository->snapshots() as $snapshot) {
            if ($snapshot === $repository->current()) {
                continue;
            }
            if (Carbon::createFromFormat(DATE_ATOM, $snapshot)->lessThan(now()->subDays($repository->delay))) {
                Storage::deleteDirectory($repository->snapshotDir().'/'.$snapshot);
            }
        }
    }
}
