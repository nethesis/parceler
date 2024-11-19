<?php

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Support\Facades\Queue;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

it('can sync repositories as parameter', function () {
    $repositories = Repository::factory()->count(2)->create()->pluck('name');
    Queue::fake();
    artisan("repository:sync {$repositories->join(' ')} test")
        ->expectsOutput("Dispatching sync for '{$repositories->get(0)}'.")
        ->expectsOutput("Dispatching sync for '{$repositories->get(1)}'.")
        ->expectsOutput("Repository 'test' not found.")
        ->assertExitCode(0);
    Queue::assertPushed(SyncRepository::class, 2);
});

it('can create a repository through cli', function () {
    $repository = Repository::factory()->make();
    Queue::fake();
    artisan('repository:create')
        ->expectsQuestion('What is the name of the repository?', $repository->name)
        ->expectsQuestion('Provide the command to be ran to sync this repository.', $repository->command)
        ->expectsQuestion('Provide the folder where the data is.', $repository->source_folder)
        ->expectsQuestion('Please provide how much time the repository must be kept back from upstream.', $repository->delay)
        ->expectsConfirmation('Do you want to sync the repository now?', 'yes')
        ->assertExitCode(0);
    assertDatabaseHas('repositories', $repository->toArray());
    Queue::assertPushed(SyncRepository::class);
});

it('can list repositories', function () {
    $repositories = Repository::factory()->create([
        'name' => 'test',
        'delay' => 1,
    ]);
    artisan('repository:list')
        ->expectsTable(
            ['Name', 'Delay (in days)', 'Repo Frozen', 'Serving Directory'],
            [['test', 1, 'No', 'snapshots/test/']],
        );
});
