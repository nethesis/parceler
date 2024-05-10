<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Console\Command;
use function Laravel\Prompts\multiselect;

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
    protected $description = 'Sync of all repositories.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $repositories = multiselect(
            label: 'Select the repositories to sync',
            options: Repository::pluck('name', 'id'),
            required: true
        );
        foreach ($repositories as $repository) {
            SyncRepository::dispatch(Repository::find($repository));
        }
    }
}
