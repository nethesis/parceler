<?php

use App\Jobs\MilestoneRelease;
use App\Models\Repository;

use function Pest\Laravel\artisan;

it('cannot release milestone without a name', function () {
    artisan('repository:milestone')
        ->assertFailed();
})->throws(RuntimeException::class);

it('cannot release milestone for a non-existing repository', function () {
    artisan('repository:milestone', ['repository' => 'non-existing-repo'])
        ->assertFailed();
});

it('ensure MilestoneRelease job is dispatched', function () {
    $repo = Repository::factory()->create();
    Queue::fake();
    artisan('repository:milestone', ['repository' => $repo->name])
        ->expectsOutput("Milestone release for $repo->name dispatched.")
        ->assertExitCode(0);
    Queue::assertPushed(function (MilestoneRelease $job) use ($repo): bool {
        return $job->repository->is($repo);
    });
});
