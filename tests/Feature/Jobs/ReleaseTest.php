<?php

use App\Jobs\DeleteSnapshot;
use App\Jobs\Release;
use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;

test('it syncs repository and freezes to the most recent snapshot', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    $oldSnapshot = now()->subDay()->toAtomString();
    $newSnapshot = now()->toAtomString();
    Storage::createDirectory($repo->snapshotDir().'/'.$oldSnapshot);
    Storage::createDirectory($repo->snapshotDir().'/'.$newSnapshot);

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    Bus::assertDispatched(SyncRepository::class);
    expect($repo->fresh()->freeze)->toBe($newSnapshot);
});

test('it filters out non-DATE_ATOM directories', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    $validSnapshot = now()->toAtomString();
    Storage::createDirectory($repo->snapshotDir().'/'.$validSnapshot);
    Storage::createDirectory($repo->snapshotDir().'/invalid-dir');
    Storage::createDirectory($repo->snapshotDir().'/another-invalid');

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    expect($repo->fresh()->freeze)->toBe($validSnapshot);

    // With only one valid snapshot, no batch should be created
    Bus::assertBatchCount(0);
});

test('it creates deletion jobs for old snapshots in correct order', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    $oldest = now()->subDays(3)->toAtomString();
    $middle = now()->subDays(2)->toAtomString();
    $newer = now()->subDay()->toAtomString();
    $newest = now()->toAtomString();

    Storage::createDirectory($repo->snapshotDir().'/'.$oldest);
    Storage::createDirectory($repo->snapshotDir().'/'.$middle);
    Storage::createDirectory($repo->snapshotDir().'/'.$newer);
    Storage::createDirectory($repo->snapshotDir().'/'.$newest);

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    expect($repo->fresh()->freeze)->toBe($newest);

    Bus::assertBatched(function ($batch) use ($repo, $oldest, $middle, $newer) {
        $jobs = collect($batch->jobs);

        return $jobs->count() === 3
            && $jobs->pluck('directory')->contains($repo->snapshotDir().'/'.$oldest)
            && $jobs->pluck('directory')->contains($repo->snapshotDir().'/'.$middle)
            && $jobs->pluck('directory')->contains($repo->snapshotDir().'/'.$newer);
    });
});

test('it does not create deletion batch when only one snapshot exists', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    $snapshot = now()->toAtomString();
    Storage::createDirectory($repo->snapshotDir().'/'.$snapshot);

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    expect($repo->fresh()->freeze)->toBe($snapshot);

    Bus::assertBatchCount(0);
});

test('it handles empty snapshot directory', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    Storage::createDirectory($repo->snapshotDir());

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    Bus::assertDispatched(SyncRepository::class);
    expect($repo->fresh()->freeze)->toBeNull();
    Bus::assertBatchCount(0);
});

test('it ignores existing freeze state', function () {
    $repo = Repository::factory()->create(['freeze' => 'old-frozen-snapshot']);
    Storage::fake();

    $snapshot = now()->toAtomString();
    Storage::createDirectory($repo->snapshotDir().'/'.$snapshot);

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    expect($repo->fresh()->freeze)->toBe($snapshot);
});

test('batch callbacks are configured correctly', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    $old = now()->subHour()->toAtomString();
    $new = now()->toAtomString();
    Storage::createDirectory($repo->snapshotDir().'/'.$old);
    Storage::createDirectory($repo->snapshotDir().'/'.$new);

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    Bus::assertBatched(function ($batch) use ($repo) {
        return $batch->name === "Release cleanup for {$repo->name}";
    });
});

test('repository is frozen after release dispatch', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    $old = now()->subHour()->toAtomString();
    $new = now()->toAtomString();
    Storage::createDirectory($repo->snapshotDir().'/'.$old);
    Storage::createDirectory($repo->snapshotDir().'/'.$new);

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    // Repository should be frozen to newest snapshot
    $fresh = $repo->fresh();
    expect($fresh->freeze)->toBe($new);
});

test('it creates correct number of deletion jobs', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    // Create 5 snapshots
    $snapshots = collect(range(1, 5))->map(function ($i) use ($repo) {
        $snapshot = now()->subHours($i)->toAtomString();
        Storage::createDirectory($repo->snapshotDir().'/'.$snapshot);

        return $snapshot;
    });

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    // Should create batch with 4 deletion jobs (all except the newest)
    Bus::assertBatched(function ($batch) {
        return $batch->jobs->count() === 4;
    });
});

test('it handles mixed valid and invalid directory names', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    $valid1 = now()->subHours(3)->toAtomString();
    $valid2 = now()->subHours(2)->toAtomString();
    $valid3 = now()->subHours(1)->toAtomString();

    Storage::createDirectory($repo->snapshotDir().'/'.$valid1);
    Storage::createDirectory($repo->snapshotDir().'/not-a-date');
    Storage::createDirectory($repo->snapshotDir().'/'.$valid2);
    Storage::createDirectory($repo->snapshotDir().'/temp');
    Storage::createDirectory($repo->snapshotDir().'/'.$valid3);

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    // Should freeze to newest valid snapshot
    expect($repo->fresh()->freeze)->toBe($valid3);

    // Should create deletion jobs only for the 2 older valid snapshots
    Bus::assertBatched(function ($batch) use ($repo, $valid1, $valid2) {
        $directories = collect($batch->jobs)->pluck('directory');

        return $batch->jobs->count() === 2
            && $directories->contains($repo->snapshotDir().'/'.$valid1)
            && $directories->contains($repo->snapshotDir().'/'.$valid2);
    });
});
