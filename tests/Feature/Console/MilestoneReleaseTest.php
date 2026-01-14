<?php

use App\Jobs\Release;
use App\Models\Repository;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\artisan;

it('cannot release without a name', function () {
    artisan('repository:release')
        ->assertFailed();
})->throws(RuntimeException::class);

it('cannot release for a non-existing repository', function () {
    artisan('repository:release', ['repository' => 'non-existing-repo'])
        ->assertFailed();
});

it('ensure Release job is dispatched', function () {
    $repo = Repository::factory()->create();
    Queue::fake();
    artisan('repository:release', ['repository' => $repo->name])
        ->expectsOutput("Release for $repo->name dispatched.")
        ->assertExitCode(0);
    Queue::assertPushed(function (Release $job) use ($repo): bool {
        return $job->repository->is($repo);
    });
});
