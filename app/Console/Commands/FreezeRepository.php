<?php

namespace App\Console\Commands;

use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FreezeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:freeze {repository} {directory?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Freeze specified repository';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $repoName = $this->argument('repository');
        try {
            $repository = Repository::where('name', $repoName)->firstOrFail();
        } catch (ModelNotFoundException) {
            $this->error("Repository '$repoName' not found.");

            return self::FAILURE;
        }
        if ($repository->freeze != null) {
            $this->error("Repository '$repository->name' is already frozen.");

            return self::FAILURE;
        }
        if (is_null($this->argument('directory'))) {
            $directory = basename($repository->getStablePath());
        } else {
            $directory = $this->argument('directory');
        }
        $this->info("Freezing repository '$repository->name' to '$directory'...");
        $repository->freeze = $directory;
        $repository->save();

        return self::SUCCESS;
    }
}
