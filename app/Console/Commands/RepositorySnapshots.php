<?php

namespace App\Console\Commands;

use App\Logic\PackageRepository;
use Illuminate\Console\Command;

class RepositorySnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lists the snapshots of the repository.';

    /**
     * Execute the console command.
     */
    public function handle(PackageRepository $repository): void
    {
        $snapshots = $repository->snapshots()
            ->map(function (string $snapshot) use ($repository) {
                return [
                    'Snapshot' => $snapshot,
                    'Active' => $snapshot === $repository->current() ? 'Yes' : 'No',
                ];
            });
        $this->table(['Snapshot', 'Active'], $snapshots);
        if ($repository->isFrozen()) {
            $this->warn('Repository is frozen.');
        }
    }
}
