<?php

use App\Jobs\SyncRepository;
use App\Logic\PackageRepository;
use Illuminate\Process\Exceptions\ProcessFailedException;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;
use function Pest\Laravel\freezeTime;

it('runs the configured command without timeout', function () {
    config(['repositories.command' => 'rclone sync upstream source/nethsecurity']);
    Process::fake();
    Storage::fake();

    SyncRepository::dispatchSync();

    Process::assertRan(function (PendingProcess $process): bool {
        return is_null($process->timeout)
            && $process->command === 'rclone sync upstream source/nethsecurity';
    });
});

it('snapshots the source sub directory', function () {
    config(['repositories.sub_dir' => 'example']);
    freezeTime();
    Process::fake();
    Storage::fake();
    $repository = app(PackageRepository::class);
    Storage::put($repository->sourceDir().'/file1.txt', 'file1');
    Storage::put($repository->sourceDir().'/nested/file2.txt', 'file2');

    SyncRepository::dispatchSync();

    $snapshot = $repository->snapshotDir().'/'.now()->toAtomString();
    Storage::assertExists($snapshot.'/file1.txt');
    Storage::assertExists($snapshot.'/nested/file2.txt');
});

it('does not snapshot when the command fails', function () {
    Process::fake(fn () => Process::result(exitCode: 1));
    Storage::fake();
    $repository = app(PackageRepository::class);
    Storage::put($repository->sourceDir().'/file1.txt', 'file1');

    expect(fn () => SyncRepository::dispatchSync())->toThrow(ProcessFailedException::class);

    expect($repository->snapshots())->toBeEmpty();
});

it('deletes snapshots older than the delay except the served one', function () {
    config(['repositories.delay' => 2]);
    freezeTime();
    Process::fake();
    Storage::fake();
    $repository = app(PackageRepository::class);
    $served = now()->subDays(5)->toAtomString();
    $old = now()->subDays(4)->toAtomString();
    $recent = now()->subDay()->toAtomString();
    foreach ([$served, $old, $recent] as $snapshot) {
        Storage::createDirectory($repository->snapshotDir().'/'.$snapshot);
    }
    $repository->setCurrent($served);

    SyncRepository::dispatchSync();

    expect($repository->snapshots()->all())->toBe([$served, $recent, now()->toAtomString()]);
});

it('dispatches the sync from the command line', function () {
    Queue::fake([SyncRepository::class]);

    artisan('repository:sync')
        ->expectsOutput('Dispatching sync.')
        ->assertSuccessful();

    Queue::assertPushed(SyncRepository::class);
});
