<?php

//
// Copyright (C) 2026 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Timeout;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

#[Timeout(3600)]
class DeleteSnapshot implements ShouldQueue
{
    use Batchable, Queueable;

    /**
     * Delete a snapshot directory.
     */
    public function __construct(public readonly string $directory) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        Log::debug("Deleting snapshot directory: {$this->directory}");
        Storage::deleteDirectory($this->directory);
    }
}
