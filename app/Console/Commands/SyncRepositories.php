<?php

namespace App\Console\Commands;

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Console\Command;

class SyncRepositories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-repositories {--Q|queued : Whether the job should be queued or ran immediately}';

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
        if ($this->option('queued')) {
            $this->info('Triggering syncing jobs.');
        } else {
            $this->info('Syncing repositories.');
        }
        foreach (Repository::cursor() as $repo) {
            if ($this->option('queued')) {
                $this->info("Queueing sync for $repo->name.");
                SyncRepository::dispatch($repo);
            } else {
                $this->info("Syncing $repo->name repository.");
                SyncRepository::dispatchSync($repo);
            }
        }
    }
}
