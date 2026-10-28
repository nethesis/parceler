<?php

use App\Jobs\Release;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\post;
use function Pest\Laravel\withToken;

it('cannot access route without auth', function () {
    post('repository/nethsecurity/release')
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Bearer');
    withToken('random')->post('repository/nethsecurity/release')
        ->assertUnauthorized()
        ->assertHeader('WWW-Authenticate', 'Bearer');
});

it('will dispatch a Release job', function () {
    Queue::fake([Release::class]);
    withToken(config('repositories.release_token'))
        ->post('repository/nethsecurity/release')
        ->assertOk();
    Queue::assertPushed(Release::class);
});

it('cannot dispatch a Release job for another repository', function () {
    Queue::fake([Release::class]);
    withToken(config('repositories.release_token'))
        ->post('repository/non-existent/release')
        ->assertNotFound();
    Queue::assertNotPushed(Release::class);
});
