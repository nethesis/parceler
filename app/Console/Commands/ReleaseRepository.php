<?php

namespace App\Console\Commands;

use App\Jobs\Release;
use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class ReleaseRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:release {repository}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a release job for the given repository.';

    /**
     * Execute the console command.
     *
     * @throws Throwable
     */
    public function handle(): void
    {
        try {
            $repository = Repository::where('name', $this->argument('repository'))->firstOrFail();
            Release::dispatch($repository);
            $this->info("Release for $repository->name dispatched.");
        } catch (ModelNotFoundException) {
            $this->fail("Repository '{$this->argument('repository')}' not found.");
        }
    }
}
