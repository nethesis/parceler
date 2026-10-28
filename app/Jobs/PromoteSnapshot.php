<?php

//
// Copyright (C) 2026 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Jobs;

use App\Logic\PackageRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PromoteSnapshot implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(PackageRepository $repository): void
    {
        $current = $repository->promote();
        if ($repository->isFrozen()) {
            Log::info("Repository {$repository->name} is frozen, serving $current.");
        } else {
            Log::info("Repository {$repository->name} serving $current.");
        }
    }
}
