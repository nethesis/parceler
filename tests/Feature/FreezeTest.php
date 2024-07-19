<?php

use App\Models\Repository;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

it('returns frozen directory', function () {
    Storage::fake();
    $repository = Repository::factory()->create([
        'delay' => 7,
    ]);
    $snapshotPath = config('repositories.snapshots').'/'.$repository->name;
    Storage::createDirectory($snapshotPath.'/'.now()->subDays(6)->toAtomString());
    expect($repository->getStablePath())
        ->toBe($snapshotPath.'/'.now()->subDays(6)->toAtomString());
    $repository->freeze = 'frozen';
    $repository->save();
    expect($repository->getStablePath())
        ->toBe($snapshotPath.'/frozen');
});

it('cannot freeze not existing directory')
    ->artisan('repository:freeze', ['repository' => 'wrong_repo'])
    ->expectsOutput("Repository 'wrong_repo' not found.")
    ->assertFailed();

it('cannot freeze already frozen repository', function () {
    $repository = Repository::factory()->freeze()->create();
    artisan('repository:freeze', ['repository' => $repository->name])
        ->expectsOutput("Repository '$repository->name' is already frozen.")
        ->assertFailed();
});

it('can freeze repository with custom directory', function () {
    $repository = Repository::factory()->create([
        'delay' => 7,
    ]);
    Storage::fake();
    $directories = [
        now()->subDays(8)->toAtomString(),
        now()->subDays(6)->toAtomString(),
        now()->subDays(5)->toAtomString(),
        now()->subDays(4)->toAtomString(),
        'cool_custom_directory',
    ];
    foreach ($directories as $directory) {
        Storage::createDirectory($repository->snapshotDir().'/'.$directory);
    }
    artisan('repository:freeze', ['repository' => $repository->name, 'directory' => $directories[4]])
        ->expectsOutput("Freezing repository '$repository->name' to '$directories[4]'...")
        ->assertSuccessful();
    $repository->refresh();
});

it('cannot unfroze not existing repository')
    ->artisan('repository:unfreeze', ['repository' => 'wrong_repo'])
    ->expectsOutput("Repository 'wrong_repo' not found.")
    ->assertFailed();

it('cannot unfreeze not frozen repository', function () {
    $repository = Repository::factory()->create();
    artisan('repository:unfreeze', ['repository' => $repository->name])
        ->expectsOutput("Repository '$repository->name' is not frozen.")
        ->assertFailed();
});

it('can unfreeze repository', function () {
    $repository = Repository::factory()->freeze()->create();
    artisan('repository:unfreeze', ['repository' => $repository->name])
        ->expectsOutput("Unfreezing repository '$repository->name'...")
        ->assertSuccessful();
    $repository->refresh();
    expect($repository->freeze)
        ->toBeNull();
});
