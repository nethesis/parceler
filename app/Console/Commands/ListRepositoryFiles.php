<?php

namespace App\Console\Commands;

use App\Logic\PackageRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ListRepositoryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:list-files {path?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List files in the repository, additional path can be provided to list files in a specific directory instead of the current served directory.';

    /**
     * Execute the console command.
     */
    public function handle(PackageRepository $repository): int
    {
        $path = $this->argument('path') ?? $repository->servingDir();
        if (is_null($path)) {
            $this->error('No snapshot is being served, provide a path to list.');

            return self::FAILURE;
        }
        foreach (Storage::files($path, true) as $file) {
            $this->info($file);
        }

        return self::SUCCESS;
    }
}
