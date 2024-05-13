<?php

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    foreach (Repository::cursor() as $repository) {
        SyncRepository::dispatch($repository);
    }
})->name('Sync Repositories')->daily();
