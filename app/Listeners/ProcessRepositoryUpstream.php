<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Listeners;

use App\Events\RepositorySynced;
use App\Events\SnapshotCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessRepositoryUpstream implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(RepositorySynced $event): void
    {
        Log::debug("Starting snapshot of {$event->repository->name} using $event->timestamp.");
        $targetDir = $event->repository->snapshotDir().'/'.$event->timestamp;
        Storage::makeDirectory($targetDir);
        foreach (Storage::allFiles($event->repository->sourceDir()) as $file) {
            // Remove source directory from the file path
            $dest = $targetDir.'/'.substr($file, strlen($event->repository->sourceDir()) + 1);
            Storage::copy($file, $dest);
        }
        Log::debug("Snapshot created successfully at $targetDir.");
        SnapshotCreated::dispatch($event->repository);
    }
}
