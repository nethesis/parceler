<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use function Laravel\Prompts\multiselect;

class SyncRepositories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:sync {repository*}';

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
        $repositories = $this->argument('repository');
        foreach ($repositories as $repoName) {
            try {
                $repository = Repository::where('name', $repoName)->firstOrFail();
                $this->info("Dispatching sync for '$repoName'.");
                SyncRepository::dispatch($repository);
            } catch (ModelNotFoundException) {
                $this->warn("Repository '$repoName' not found.");
            }
        }
    }
}
