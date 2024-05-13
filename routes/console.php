<?php

use App\Jobs\SyncRepository;
use App\Models\Repository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::call(function () {
    foreach (Repository::cursor() as $repository) {
        Artisan::call(SyncRepository::class, [$repository->name]);
    }
})->daily();
