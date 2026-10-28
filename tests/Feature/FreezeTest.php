<?php

use App\Logic\PackageRepository;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

it('freezes to the served snapshot', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    $served = now()->subDays(6)->toAtomString();
    Storage::createDirectory($repository->snapshotDir().'/'.$served);
    $repository->setCurrent($served);

    artisan('repository:freeze')
        ->expectsOutput("Freezing repository to '$served'...")
        ->assertSuccessful();

    expect($repository->isFrozen())->toBeTrue()
        ->and($repository->current())->toBe($served);
});

it('freezes to the given snapshot', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    $older = now()->subDays(6)->toAtomString();
    Storage::createDirectory($repository->snapshotDir().'/'.$older);
    $repository->setCurrent(now()->subDays(4)->toAtomString());

    artisan('repository:freeze', ['snapshot' => $older])
        ->expectsOutput("Freezing repository to '$older'...")
        ->assertSuccessful();

    expect($repository->isFrozen())->toBeTrue()
        ->and($repository->current())->toBe($older);
});

it('cannot freeze to a missing snapshot', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);

    artisan('repository:freeze', ['snapshot' => 'missing'])
        ->expectsOutput("Snapshot 'missing' not found.")
        ->assertFailed();

    expect($repository->isFrozen())->toBeFalse();
});

it('cannot freeze when nothing is served', function () {
    Storage::fake();

    artisan('repository:freeze')
        ->expectsOutput('No snapshot is being served, provide one to freeze to.')
        ->assertFailed();
});

it('cannot freeze an already frozen repository', function () {
    Storage::fake();
    app(PackageRepository::class)->freeze();

    artisan('repository:freeze')
        ->expectsOutput('Repository is already frozen.')
        ->assertFailed();
});

it('unfreezes the repository', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    $repository->freeze();

    artisan('repository:unfreeze')
        ->expectsOutput('Unfreezing repository...')
        ->assertSuccessful();

    expect($repository->isFrozen())->toBeFalse();
});

it('cannot unfreeze a repository that is not frozen', function () {
    Storage::fake();

    artisan('repository:unfreeze')
        ->expectsOutput('Repository is not frozen.')
        ->assertFailed();
});
