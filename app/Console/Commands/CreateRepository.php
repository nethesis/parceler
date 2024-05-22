<?php

//
// Copyright (C) 2024 Nethesis S.r.l.
// SPDX-License-Identifier: AGPL-3.0-or-later
//

namespace App\Console\Commands;

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\textarea;

class CreateRepository extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'repository:create';

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
            hint: "Save the content to `source/$name` of the root path of the targeted storage."
        );
        $source_folder = text(
            label: 'Provide the folder where the data is.',
            hint: "This path is intended to be inside `source/$name`, leave blank if no subdirectory is needed."
        );
        if ($source_folder == '') {
            $source_folder = null;
        }
        $delay = text(
            label: 'Please provide how much time the repository must be kept back from upstream.',
            default: 7,
            hint: 'Value is expressed in days.'
        );
        $repository = Repository::create([
            'name' => $name,
            'command' => $command,
            'sub_dir' => $source_folder,
            'delay' => $delay,
        ]);
        $syncNow = confirm(
            label: 'Do you want to sync the repository now?',
            default: true
        );
        if ($syncNow) {
            info('Sync will be started now by worker.');
            SyncRepository::dispatch($repository);
        } else {
            info('Sync will be started when all repositories are synced.');
        }
    }
}
