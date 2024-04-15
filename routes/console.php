<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:sync-repositories -Q')->daily();
