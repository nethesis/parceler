<?php

use App\Jobs\DeleteSnapshot;
use Illuminate\Support\Facades\Storage;

test('it deletes a snapshot directory', function () {
    Storage::fake();

    $directory = 'snapshots/test/2026-01-14T10:30:45+00:00';
    Storage::createDirectory($directory);
    Storage::put($directory.'/file.txt', 'content');

    Storage::assertExists($directory.'/file.txt');

    $job = new DeleteSnapshot($directory);
    $job->handle();

    Storage::assertMissing($directory);
});

test('it handles non-existent directory gracefully', function () {
    Storage::fake();

    $directory = 'snapshots/test/non-existent';
    Storage::assertMissing($directory);

    $job = new DeleteSnapshot($directory);
    $job->handle();

    // Should not throw exception
    expect(true)->toBeTrue();
});

test('it deletes multiple files within directory', function () {
    Storage::fake();

    $directory = 'snapshots/test/2026-01-14T10:30:45+00:00';
    Storage::createDirectory($directory);
    Storage::put($directory.'/file1.txt', 'content1');
    Storage::put($directory.'/file2.txt', 'content2');
    Storage::createDirectory($directory.'/subdir');
    Storage::put($directory.'/subdir/file3.txt', 'content3');

    $job = new DeleteSnapshot($directory);
    $job->handle();

    Storage::assertMissing($directory);
    Storage::assertMissing($directory.'/file1.txt');
    Storage::assertMissing($directory.'/file2.txt');
    Storage::assertMissing($directory.'/subdir/file3.txt');
});
