<?php

namespace App\Listeners;

use App\Events\RepositorySynced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessRepositoryUpstream implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(RepositorySynced $event): void
    {
        Log::debug("Repository {$event->repository->name} has been synced correctly, snapshotting directory.");
        $targetDir = config('repositories.directory').'/'.$event->repository->name.'/'.now()->toAtomString();
        Storage::makeDirectory($targetDir);
        foreach (Storage::allFiles($event->repository->source_folder) as $file) {
            // Remove first directory from path
            $dest = $targetDir.'/'.substr($file, strpos($file, '/') + 1);
            Storage::copy($file, $dest);
        }
        Log::debug("Snapshot created successfully at $targetDir.");
    }
}
