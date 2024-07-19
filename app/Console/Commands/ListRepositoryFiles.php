<?php

namespace App\Console\Commands;

use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ListRepositoryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:list-files {repository} {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List files in the repository, additional path can be provided to list files in a specific directory instead of the current served directory.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $repository = Repository::where('name', $this->argument('repository'))->firstOrFail();
        $path = $this->argument('path');
        if (is_null($path)) {
            $path = $repository->getStablePath();
        }
        foreach (Storage::files($path, true) as $file) {
            $this->info($file);
        }
    }
}
