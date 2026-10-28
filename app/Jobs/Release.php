<?php

//
// Copyright (C) 2026 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Jobs;

use App\Logic\PackageRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Support\Facades\Log;

#[Timeout(7200)]
class Release implements ShouldQueue
{
    use Queueable;

    /**
     * Sync the repository, serve the fresh snapshot and delete all the others.
     */
    public function handle(PackageRepository $repository): void
    {
        Log::debug("Releasing {$repository->name}.");

        SyncRepository::dispatchSync();

        $snapshots = $repository->snapshots();
        if ($snapshots->isEmpty()) {
            Log::warning("No valid snapshots found for {$repository->name}.");

            return;
        }

        $latest = $snapshots->last();
        $repository->unfreeze();
        $repository->setCurrent($latest);
        Log::info("Repository {$repository->name} serving $latest.");

        foreach ($snapshots->slice(0, -1) as $snapshot) {
            DeleteSnapshot::dispatch($repository->snapshotDir().'/'.$snapshot);
        }
    }
}
