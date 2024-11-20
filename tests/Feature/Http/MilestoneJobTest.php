<?php

use App\Jobs\MilestoneRelease;
use App\Models\Repository;

use function Pest\Laravel\post;
use function Pest\Laravel\withToken;

it('cannot access milestone route without auth', function () {
    $repo = Repository::factory()->create();
    post("repository/$repo->name/milestone")
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Bearer');
    withToken('random')->post("repository/$repo->name/milestone")
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Bearer');
});

it('will dispatch a MilestoneRelease job', function () {
    $repo = Repository::factory()->create();
    Queue::fake();
    withToken(config('repositories.milestone_token'))
        ->post("repository/$repo->name/milestone")
        ->assertOk();
    Queue::assertPushed(function (MilestoneRelease $job) use ($repo): bool {
        return $job->repository->is($repo);
    });
});

it('cannot dispatch a MilestoneRelease job for a non-existent repository', function () {
    withToken(config('repositories.milestone_token'))
        ->post('repository/non-existent/milestone')
        ->assertNotFound();
});
