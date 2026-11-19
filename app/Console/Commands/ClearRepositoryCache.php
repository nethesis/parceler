<?php

namespace App\Console\Commands;

use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClearRepositoryCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:clear-cache {repository?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear the cached stable snapshot of a repository, or of every repository when none is given.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $repoName = $this->argument('repository');
        if (is_null($repoName)) {
            foreach (Repository::all() as $repository) {
                $repository->forgetStablePath();
            }
            $this->info('Cleared the cache of every repository.');

            return self::SUCCESS;
        }
        try {
            $repository = Repository::where('name', $repoName)->firstOrFail();
        } catch (ModelNotFoundException) {
            $this->error("Repository '$repoName' not found.");

            return self::FAILURE;
        }
        $repository->forgetStablePath();
        $this->info("Cleared the cache of repository '$repository->name'.");

        return self::SUCCESS;
    }
}
