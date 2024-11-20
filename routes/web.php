<?php

use App\Http\Controllers\RepositoryController;
use App\Http\Middleware\CommunityLicenceCheck;
use App\Http\Middleware\EnterpriseLicenceCheck;
use App\Http\Middleware\ForceBasicAuth;
use App\Http\Middleware\MilestoneAuth;
use App\Jobs\MilestoneRelease;
use App\Models\Repository;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(MilestoneAuth::class)
    ->post('/repository/{repository:name}/milestone', function (Repository $repository) {
        MilestoneRelease::dispatch($repository);

        return response()->json(['message' => 'Milestone release job dispatched.']);
    });

Route::middleware(ForceBasicAuth::class)->group(function () {
    Route::get('/repository/community/{repository:name}/{path}', RepositoryController::class)
        ->where('path', '.*')
        ->middleware(CommunityLicenceCheck::class);

    Route::get('/repository/enterprise/{repository:name}/{path}', RepositoryController::class)
        ->where('path', '.*')
        ->middleware(EnterpriseLicenceCheck::class);
});
