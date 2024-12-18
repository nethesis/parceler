<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Jobs;

use App\Events\RepositorySynced;
use App\Models\Repository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;

class SyncRepository implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly Repository $repository) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::debug("Syncing repository {$this->repository->name}.");
        Process::forever()
            ->run($this->repository->command)
            ->throw();
        Log::debug("Repository {$this->repository->name} synced.");
        RepositorySynced::dispatch($this->repository);
    }
}
