<?php

namespace App\Console\Commands;

use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RepositorySnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:snapshots {repository}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the snapshots for a repository.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $repository = Repository::where('name', $this->argument('repository'))->firstOrFail();
        $snapshots = collect(Storage::directories($repository->snapshotDir()))
            ->sort()
            ->map(function ($snapshot) use ($repository) {
                return [
                    'Snapshot' => basename($snapshot),
                    'Active' => $snapshot === $repository->getStablePath() ? 'Yes' : 'No',
                ];
            });
        $this->table(['Snapshot', 'Active'], $snapshots);
    }
}
