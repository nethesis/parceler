<?php

use App\Jobs\Release;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\artisan;

it('ensure Release job is dispatched', function () {
    Queue::fake([Release::class]);
    artisan('repository:release')
        ->expectsOutput('Release dispatched.')
        ->assertExitCode(0);
    Queue::assertPushed(Release::class);
});
