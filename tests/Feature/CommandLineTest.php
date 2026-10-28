<?php

use App\Logic\PackageRepository;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

it('can promote a snapshot', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    $snapshot = now()->subDay()->toAtomString();
    Storage::createDirectory($repository->snapshotDir().'/'.$snapshot);
    artisan('repository:promote')
        ->expectsOutput("Serving '$snapshot'.")
        ->assertSuccessful();
});

it('fails to promote when there are no snapshots', function () {
    Storage::fake();
    artisan('repository:promote')
        ->expectsOutput('No snapshot within the delay to promote.')
        ->assertFailed();
});
