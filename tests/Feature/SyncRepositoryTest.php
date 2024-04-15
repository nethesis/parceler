<?php

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\artisan;

test('runs command correctly', function () {
    Process::fake();
    $repository = Repository::factory()->create();
    SyncRepository::dispatchSync($repository);
    Process::assertRan(function (PendingProcess $process) use ($repository): bool {
        return is_null($process->timeout)
            && $process->command == $repository->command;
    });
});

test('can sync repository from command line', function ($command) {
    $repositories = Repository::factory()->count(3)->create();
    Queue::fake();
    artisan($command)
        ->assertSuccessful();
    Queue::assertCount($repositories->count());
    Queue::assertPushed(SyncRepository::class, $repositories->count());
})->with(
    [
        'app:sync-repositories',
        'app:sync-repositories -Q',
        'app:sync-repositories --queued',
    ]
);
