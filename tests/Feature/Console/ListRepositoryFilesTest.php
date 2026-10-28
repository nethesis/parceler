<?php

use App\Logic\PackageRepository;
use Illuminate\Support\Facades\Storage;

test('list files of the served snapshot', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    $snapshot = now()->toAtomString();
    Storage::put($repository->snapshotDir()."/$snapshot/file1.txt", 'content');
    Storage::put($repository->snapshotDir().'/other/file2.txt', 'content');
    $repository->setCurrent($snapshot);
    $this->artisan('repository:list-files')
        ->expectsOutputToContain($repository->snapshotDir()."/$snapshot/file1.txt")
        ->doesntExpectOutputToContain('file2.txt')
        ->assertExitCode(0);
});

test('list files of the given path', function () {
    Storage::fake();
    $repository = app(PackageRepository::class);
    Storage::put($repository->snapshotDir().'/other/file2.txt', 'content');
    $this->artisan('repository:list-files', ['path' => $repository->snapshotDir().'/other'])
        ->expectsOutputToContain($repository->snapshotDir().'/other/file2.txt')
        ->assertExitCode(0);
});

test('cannot list files when nothing is served', function () {
    Storage::fake();
    $this->artisan('repository:list-files')
        ->expectsOutput('No snapshot is being served, provide a path to list.')
        ->assertFailed();
});
