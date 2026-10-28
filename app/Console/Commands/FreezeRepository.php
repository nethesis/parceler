<?php

namespace App\Console\Commands;

use App\Logic\PackageRepository;
use Illuminate\Console\Command;

class FreezeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:freeze {snapshot?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Freeze the repository to the given snapshot, or the one currently served.';

    /**
     * Execute the console command.
     */
    public function handle(PackageRepository $repository): int
    {
        if ($repository->isFrozen()) {
            $this->error('Repository is already frozen.');

            return self::FAILURE;
        }
        $snapshot = $this->argument('snapshot') ?? $repository->current();
        if (is_null($snapshot)) {
            $this->error('No snapshot is being served, provide one to freeze to.');

            return self::FAILURE;
        }
        if (! $repository->snapshots()->contains($snapshot)) {
            $this->error("Snapshot '$snapshot' not found.");

            return self::FAILURE;
        }
        $this->info("Freezing repository to '$snapshot'...");
        $repository->setCurrent($snapshot);
        $repository->freeze();

        return self::SUCCESS;
    }
}
