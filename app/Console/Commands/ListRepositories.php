<?php

namespace App\Console\Commands;

use App\Models\Repository;
use Illuminate\Console\Command;

class ListRepositories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List a table of all repositories in the database.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->table(
            ['Name', 'Delay (in days)', 'Repo Frozen', 'Serving Directory'],
            Repository::all()->map(function (Repository $repository) {
                return [
                    $repository->name,
                    $repository->delay,
                    $repository->freeze ? 'Yes' : 'No',
                    $repository->getStablePath(),
                ];
            })
        );
    }
}
