<?php

namespace App\Console\Commands;

use App\Logic\PackageRepository;
use Illuminate\Console\Command;

class UnFreezeRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:unfreeze';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Unfreeze the repository, the next promotion will move the served snapshot forward.';

    /**
     * Execute the console command.
     */
    public function handle(PackageRepository $repository): int
    {
        if (! $repository->isFrozen()) {
            $this->error('Repository is not frozen.');

            return self::FAILURE;
        }
        $this->info('Unfreezing repository...');
        $repository->unfreeze();

        return self::SUCCESS;
    }
}
