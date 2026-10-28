<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Jobs\SyncRepository;
use Illuminate\Console\Command;

class SyncRepositories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync the repository.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Dispatching sync.');
        SyncRepository::dispatch();
    }
}
