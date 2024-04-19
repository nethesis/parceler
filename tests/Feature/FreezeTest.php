<?php

use App\Models\Repository;
use Illuminate\Support\Facades\Storage;

it('returns frozen directory', function () {
    Storage::fake();
    $repository = Repository::factory()->create([
        'delay' => 7,
    ]);
    $snapshotPath = config('repositories.directory').'/'.$repository->name;
    Storage::createDirectory($snapshotPath.'/'.now()->subDays(6)->toAtomString());
    expect($repository->getStablePath())
        ->toBe($snapshotPath.'/'.now()->subDays(6)->toAtomString());
    $repository->freeze = 'frozen';
    $repository->save();
    expect($repository->getStablePath())
        ->toBe($snapshotPath.'/frozen');
});
