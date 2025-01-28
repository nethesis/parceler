<?php

use App\Models\Repository;

test('can list snapshots for a repository', function () {
    $repository = Repository::factory()->create();
    Storage::fake();
    Storage::makeDirectory($repository->snapshotDir().'/snapshot1');
    Storage::makeDirectory($repository->snapshotDir().'/snapshot2');
    Storage::makeDirectory($repository->snapshotDir().'/snapshot3');
    Storage::makeDirectory($repository->snapshotDir().'/snapshot4');
    $this->artisan('repository:snapshots', ['repository' => $repository->name])
        ->expectsTable(['Snapshot', 'Active'], [
            ['Snapshot' => 'snapshot1', 'Active' => 'No'],
            ['Snapshot' => 'snapshot2', 'Active' => 'No'],
            ['Snapshot' => 'snapshot3', 'Active' => 'No'],
            ['Snapshot' => 'snapshot4', 'Active' => 'No'],
        ])
        ->assertExitCode(0);
});
