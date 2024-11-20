<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Jobs;

use App\Models\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MilestoneRelease implements ShouldQueue
{
    use Queueable;

    /**
     * Sync the milestone release.
     */
    public function __construct(public readonly Repository $repository) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug("Releasing milestone for {$this->repository->name}.");
        $toPurge = Storage::directories($this->repository->snapshotDir());
        SyncRepository::dispatchSync($this->repository);
        foreach ($toPurge as $dir) {
            Log::debug("Purging directory $dir.");
            Storage::deleteDirectory($dir);
        }
        Log::debug("Milestone released for {$this->repository->name}.");
    }
}
