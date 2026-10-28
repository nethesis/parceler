<?php

use App\Jobs\PromoteSnapshot;
use App\Logic\PackageRepository;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\freezeTime;

beforeEach(function () {
    config(['repositories.delay' => 7]);
});

it('serves the oldest snapshot within the delay', function () {
    freezeTime();
    Storage::fake();
    $repository = app(PackageRepository::class);
    Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(8)->toAtomString());
    Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
    Storage::createDirectory($repository->snapshotDir().'/'.now()->subDay()->toAtomString());

    PromoteSnapshot::dispatchSync();

    expect($repository->current())->toBe(now()->subDays(6)->toAtomString());
});

it('does not move the served snapshot when frozen', function () {
    freezeTime();
    Storage::fake();
    $repository = app(PackageRepository::class);
    $frozen = now()->subDays(8)->toAtomString();
    Storage::createDirectory($repository->snapshotDir().'/'.$frozen);
    Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(6)->toAtomString());
    $repository->setCurrent($frozen);
    $repository->freeze();

    PromoteSnapshot::dispatchSync();

    expect($repository->current())->toBe($frozen);
});

it('keeps the served snapshot when none is within the delay', function () {
    freezeTime();
    Storage::fake();
    $repository = app(PackageRepository::class);
    $served = now()->subDays(10)->toAtomString();
    Storage::createDirectory($repository->snapshotDir().'/'.$served);
    Storage::createDirectory($repository->snapshotDir().'/'.now()->subDays(9)->toAtomString());
    $repository->setCurrent($served);

    PromoteSnapshot::dispatchSync();

    expect($repository->current())->toBe($served);
});

it('ignores directories that are not snapshots', function () {
    freezeTime();
    Storage::fake();
    $repository = app(PackageRepository::class);
    Storage::createDirectory($repository->snapshotDir().'/not-a-snapshot');
    Storage::createDirectory($repository->snapshotDir().'/'.now()->subDay()->toAtomString());

    PromoteSnapshot::dispatchSync();

    expect($repository->current())->toBe(now()->subDay()->toAtomString());
});
