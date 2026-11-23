<?php

use App\Events\SnapshotCreated;
use App\Listeners\CleanRepository;
use App\Models\Repository;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;
use function Pest\Laravel\freezeTime;

it('keeps serving the cached snapshot when a newer one appears', function () {
    freezeTime(function () {
        $repository = Repository::factory()->create(['delay' => 7]);
        Storage::fake();
        Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
        $repository->getStablePath();
        Storage::deleteDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
        Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(5)->toAtomString());

        expect($repository->getStablePath())
            ->toBe($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
    });
});

it('serves the next snapshot after the cache is cleared', function (?string $repositoryArgument) {
    freezeTime(function () use ($repositoryArgument) {
        $repository = Repository::factory()->create(['delay' => 7, 'name' => 'nethsecurity']);
        Storage::fake();
        Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
        $repository->getStablePath();
        Storage::deleteDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
        Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(5)->toAtomString());

        artisan('repository:clear-cache', array_filter(['repository' => $repositoryArgument]))
            ->assertSuccessful();

        expect($repository->getStablePath())
            ->toBe($repository->snapshotDir().'/'.now()->subDays(5)->toAtomString());
    });
})->with([
    'one repository' => 'nethsecurity',
    'every repository' => null,
]);

it('fails when the repository does not exist')
    ->artisan('repository:clear-cache', ['repository' => 'wrong_repo'])
    ->expectsOutput("Repository 'wrong_repo' not found.")
    ->assertFailed();

it('serves the next snapshot after old snapshots are cleaned up', function () {
    freezeTime(function () {
        $repository = Repository::factory()->create(['delay' => 7]);
        Storage::fake();
        Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
        $repository->getStablePath();
        Storage::deleteDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
        Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(5)->toAtomString());

        (new CleanRepository)->handle(new SnapshotCreated($repository));

        expect($repository->getStablePath())
            ->toBe($repository->snapshotDir().'/'.now()->subDays(5)->toAtomString());
    });
});

it('serves the next snapshot after the repository is unfrozen', function () {
    freezeTime(function () {
        $repository = Repository::factory()->create(['delay' => 7]);
        Storage::fake();
        Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
        $repository->getStablePath();
        Storage::deleteDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
        Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(5)->toAtomString());

        $repository->freeze = 'frozen';
        $repository->save();
        $repository->freeze = null;
        $repository->save();

        expect($repository->getStablePath())
            ->toBe($repository->snapshotDir().'/'.now()->subDays(5)->toAtomString());
    });
});
