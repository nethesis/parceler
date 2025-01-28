<?php

use App\Models\Repository;

test('list files for repository', function () {
    $repository = Repository::factory()->create();
    Storage::fake();
    Storage::createDirectory($repository->snapshotDir().'/test');
    Storage::put($repository->snapshotDir().'/test/file1.txt', 'content');
    $this->artisan('repository:list-files', ['repository' => $repository->name])
        ->expectsOutputToContain($repository->snapshotDir().'/test/file1.txt')
        ->assertExitCode(0);
});
