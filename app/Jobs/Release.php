<?php

//
// Copyright (C) 2026 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Jobs;

use App\Models\Repository;
use Carbon\Carbon;
use Illuminate\Bus\Batch;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class Release implements ShouldQueue
{
    use Queueable;

    /**
     * Sync the release.
     */
    public function __construct(public readonly Repository $repository) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug("Releasing {$this->repository->name}.");

        // Sync the repository to create a fresh snapshot
        SyncRepository::dispatchSync($this->repository);

        // Get all snapshot directories and filter to only valid snapshot timestamps
        $allDirectories = Storage::directories($this->repository->snapshotDir());
        $snapshots = collect($allDirectories)->filter(function ($dir) {
            $basename = basename($dir);
            try {
                Carbon::createFromFormat(DATE_ATOM, $basename);

                return true;
            } catch (\Exception $e) {
                return false;
            }
        })->sortByDesc(function ($dir) {
            return Carbon::createFromFormat(DATE_ATOM, basename($dir));
        })->values();

        if ($snapshots->isEmpty()) {
            Log::warning("No valid snapshots found for {$this->repository->name}.");

            return;
        }

        // Freeze to the most recent snapshot
        $lastSnapshot = $snapshots->first();
        $this->repository->freeze = basename($lastSnapshot);
        $this->repository->save();
        Log::info("Froze {$this->repository->name} to snapshot: {$this->repository->freeze}");

        // Prepare deletion jobs for all other snapshots
        $snapshotsToDelete = $snapshots->slice(1);

        if ($snapshotsToDelete->isEmpty()) {
            Log::info("No snapshots to delete for {$this->repository->name}.");

            return;
        }

        $deletionJobs = $snapshotsToDelete->map(fn ($dir) => new DeleteSnapshot($dir))->all();

        // Dispatch batch with unfreeze on success
        Bus::batch($deletionJobs)
            ->then(function (Batch $batch) {
                $repo = $this->repository->fresh();
                $repo->freeze = null;
                $repo->save();
                Log::info("Repository {$repo->name} unfrozen after successful cleanup.");
            })
            ->catch(function (Batch $batch, Throwable $e) {
                Log::error("Deletion batch failed for {$this->repository->name}", [
                    'batch_id' => $batch->id,
                    'error' => $e->getMessage(),
                ]);
            })
            ->name("Release cleanup for {$this->repository->name}")
            ->dispatch();

        Log::debug("Release completed for {$this->repository->name}.");
    }
}
