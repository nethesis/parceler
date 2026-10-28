<?php

namespace App\Console\Commands;

use App\Jobs\Release;
use Illuminate\Console\Command;

class ReleaseRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:release';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a release job for the repository.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Release::dispatch();
        $this->info('Release dispatched.');
    }
}
