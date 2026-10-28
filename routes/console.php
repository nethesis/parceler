<?php

use App\Jobs\PromoteSnapshot;
use App\Jobs\SyncRepository;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new SyncRepository)->name('Sync Repository')->daily();

Schedule::job(new PromoteSnapshot)->name('Promote Snapshot')->dailyAt(config('repositories.promote_at'));
