<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class CreateRepository extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a repository';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = text(
            label: 'What is the name of the repository?',
            validate: ['name' => 'required|alpha_dash|unique:App\Models\Repository,name']
        );
        $command = textarea(
            label: 'Provide the command to be ran to sync this repository.',
            required: true,
            hint: "Save the content to `./storage/app/$name`."
        );
        $source_folder = text(
            label: 'Provide the folder where the data is.',
            default: $name,
            hint: 'This path is intended to be inside `./storage/app`.'
        );
        $delay = text(
            label: 'Please provide how much time the repository must be kept back from upstream.',
            default: 7,
            hint: 'Value is expressed in days.'
        );
        $repository = Repository::create([
            'name' => $name,
            'command' => $command,
            'source_folder' => $source_folder,
            'delay' => $delay,
        ]);
        info('Repository created successfully!');
        $dispatchNow = confirm(
            label: 'Do you want to sync now?',
            default: true,
            hint: 'Otherwise sync will be scheduled to be ran in a second time.'
        );
        if ($dispatchNow) {
            SyncRepository::dispatch($repository);
            info("Sync of $repository->name queued.");
        }
    }
}
