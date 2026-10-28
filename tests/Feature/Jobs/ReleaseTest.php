<?php

use App\Jobs\DeleteSnapshot;
use App\Jobs\Release;
use App\Jobs\SyncRepository;
use App\Logic\PackageRepository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

it('syncs, serves the newest snapshot and deletes the others', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    $oldest = now()->subDays(2)->toAtomString();
    $older = now()->subDay()->toAtomString();
    $newest = now()->toAtomString();
    foreach ([$oldest, $older, $newest, 'not-a-snapshot'] as $directory) {
        Storage::createDirectory($repository->snapshotDir().'/'.$directory);
    }
    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch();

    Bus::assertDispatchedSync(SyncRepository::class);
    expect($repository->current())->toBe($newest);
    Bus::assertDispatchedTimes(DeleteSnapshot::class, 2);
    Bus::assertDispatched(fn (DeleteSnapshot $job): bool => $job->directory === $repository->snapshotDir().'/'.$oldest);
    Bus::assertDispatched(fn (DeleteSnapshot $job): bool => $job->directory === $repository->snapshotDir().'/'.$older);
});

it('unfreezes the repository', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    Storage::createDirectory($repository->snapshotDir().'/'.now()->toAtomString());
    $repository->freeze();
    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch();

    expect($repository->isFrozen())->toBeFalse();
});

it('leaves the repository untouched when there are no snapshots', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    $repository->freeze();
    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch();

    expect($repository->current())->toBeNull()
        ->and($repository->isFrozen())->toBeTrue();
    Bus::assertNotDispatched(DeleteSnapshot::class);
});
