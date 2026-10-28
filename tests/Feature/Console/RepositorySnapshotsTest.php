<?php

use App\Logic\PackageRepository;
use Illuminate\Support\Facades\Storage;

test('can list snapshots of the repository', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    $older = now()->subDay()->toAtomString();
    $newer = now()->toAtomString();
    Storage::makeDirectory($repository->snapshotDir().'/'.$newer);
    Storage::makeDirectory($repository->snapshotDir().'/'.$older);
    $repository->setCurrent($older);
    $this->artisan('repository:snapshots')
        ->expectsTable(['Snapshot', 'Active'], [
            ['Snapshot' => $older, 'Active' => 'Yes'],
            ['Snapshot' => $newer, 'Active' => 'No'],
        ])
        ->assertExitCode(0);
});
