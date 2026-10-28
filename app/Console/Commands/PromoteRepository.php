<?php

namespace App\Console\Commands;

use App\Jobs\PromoteSnapshot;
use App\Logic\PackageRepository;
use Illuminate\Console\Command;

class PromoteRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:promote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote the oldest snapshot within the delay to be served, does nothing when frozen.';

    /**
     * Execute the console command.
     */
    public function handle(PackageRepository $repository): int
    {
        PromoteSnapshot::dispatchSync();
        $current = $repository->current();
        if (is_null($current)) {
            $this->error('No snapshot within the delay to promote.');

            return self::FAILURE;
        }
        if ($repository->isFrozen()) {
            $this->warn("Repository is frozen, serving '$current'.");
        } else {
            $this->info("Serving '$current'.");
        }

        return self::SUCCESS;
    }
}
