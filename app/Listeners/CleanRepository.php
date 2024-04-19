<?php

namespace App\Listeners;

use App\Events\SnapshotCreated;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class CleanRepository implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(SnapshotCreated $event): void
    {
        $snapshotPath = config('repositories.directory').'/'.$event->repository->name;
        foreach (Storage::directories($snapshotPath) as $directory) {
            if (basename($directory) == $event->repository->freeze) {
                continue;
            }
            $snapshotTime = Carbon::createFromFormat(DateTimeInterface::ATOM, basename($directory));
            if ($snapshotTime->lessThan(now()->subDays($event->repository->delay))) {
                Storage::deleteDirectory($directory);
            }
        }
    }
}
