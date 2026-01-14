<?php

use App\Jobs\DeleteSnapshot;
use App\Jobs\Release;
use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\post;
use function Pest\Laravel\withToken;

it('cannot access route without auth', function () {
    $repo = Repository::factory()->create();
    post("repository/$repo->name/release")
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Bearer');
    withToken('random')->post("repository/$repo->name/release")
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Bearer');
});

it('will dispatch a Release job', function () {
    $repo = Repository::factory()->create();
    Queue::fake();
    withToken(config('repositories.release_token'))
        ->post("repository/$repo->name/release")
        ->assertOk();
    Queue::assertPushed(function (Release $job) use ($repo): bool {
        return $job->repository->is($repo);
    });
});

it('cannot dispatch a Release job for a non-existent repository', function () {
    withToken(config('repositories.release_token'))
        ->post('repository/non-existent/release')
        ->assertNotFound();
});

test('dispatch Release', function () {
    $repo = Repository::factory()->create();
    Storage::fake();

    // Create valid snapshot directories using DATE_ATOM format
    $oldSnapshot = now()->subHour()->toAtomString();
    $newSnapshot = now()->toAtomString();
    Storage::createDirectory($repo->snapshotDir().'/'.$oldSnapshot);
    Storage::createDirectory($repo->snapshotDir().'/'.$newSnapshot);

    // Create a non-snapshot directory that should be preserved
    Storage::createDirectory($repo->snapshotDir().'/other-dir');

    Bus::fake([SyncRepository::class, DeleteSnapshot::class]);

    Release::dispatch($repo);

    Bus::assertDispatched(SyncRepository::class);

    // Should freeze to the most recent snapshot
    expect($repo->fresh()->freeze)->toBe($newSnapshot);

    // Should create a batch with deletion jobs for old snapshots only
    $expectedPath = $repo->snapshotDir().'/'.$oldSnapshot;
    Bus::assertBatched(function ($batch) use ($expectedPath) {
        return $batch->jobs->count() === 1
            && $batch->jobs->first() instanceof DeleteSnapshot
            && $batch->jobs->first()->directory === $expectedPath;
    });
});
