<?php

namespace App\Console\Commands;

use App\Jobs\MilestoneRelease;
use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class MilestoneRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:milestone {repository}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a milestone reset job for the given repository.';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        try {
            $repository = Repository::where('name', $this->argument('repository'))->firstOrFail();
            MilestoneRelease::dispatch($repository);
            $this->info("Milestone release for $repository->name dispatched.");
        } catch (ModelNotFoundException) {
            $this->fail("Repository '{$this->argument('repository')}' not found.");
        }
    }
}
