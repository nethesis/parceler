<?php

use App\Events\RepositorySynced;
use App\Jobs\SyncRepository;
use App\Listeners\ProcessRepositoryUpstream;
use App\Models\Repository;
use Illuminate\Http\UploadedFile;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

dataset('command', ['app:sync-repositories', 'app:sync-repositories -Q', 'app:sync-repositories --queued']);

test('runs command correctly', function () {
    Process::fake();
    Event::fake();
    $repository = Repository::factory()->create();
    SyncRepository::dispatchSync($repository);
    Process::assertRan(function (PendingProcess $process) use ($repository): bool {
        return is_null($process->timeout)
            && $process->command == $repository->command;
    });
    Event::assertDispatched(RepositorySynced::class);
});

test('can sync repository from command line', function ($command) {
    $repositories = Repository::factory()->count(3)->create();
    Queue::fake();
    artisan($command)
        ->assertSuccessful();
    Queue::assertCount($repositories->count());
    Queue::assertPushed(SyncRepository::class, $repositories->count());
})->with('command');

test('repository upstream processing', function () {
    $repository = Repository::factory()->create(['source_folder' => 'example']);
    $listener = new ProcessRepositoryUpstream();
    Storage::fake();
    UploadedFile::fake()->create('example/file1.txt')->storeAs('example', 'file1.txt');
    UploadedFile::fake()->create('example/file2.txt')->storeAs('example', 'file2.txt');
    UploadedFile::fake()->create('example/file3.txt')->storeAs('example', 'file3.txt');
    $listener->handle(new RepositorySynced($repository));
    Storage::assertExists('repositories/'.$repository->name.'/'.now()->toAtomString().'/file1.txt');
    Storage::assertExists('repositories/'.$repository->name.'/'.now()->toAtomString().'/file2.txt');
    Storage::assertExists('repositories/'.$repository->name.'/'.now()->toAtomString().'/file3.txt');
});
