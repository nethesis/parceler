<?php

use App\Http\Controllers\RepositoryController;
use App\Http\Middleware\CommunityLicenceCheck;
use App\Http\Middleware\EnterpriseLicenceCheck;
use App\Http\Middleware\ForceBasicAuth;
use App\Http\Middleware\ReleaseAuth;
use App\Jobs\Release;
use App\Models\Repository;
use Illuminate\Support\Facades\Route;
use Laravel\Nightwatch\Http\Middleware\Sample;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(ReleaseAuth::class)
    ->post('/repository/{repository:name}/release', function (Repository $repository) {
        Release::dispatch($repository);

        return response()->json(['message' => 'Release job dispatched.']);
    });

Route::middleware([ForceBasicAuth::class, Sample::rate(0)])->group(function () {
    Route::get('/repository/community/{repository:name}/{path}', RepositoryController::class)
        ->where('path', '.*')
        ->middleware(CommunityLicenceCheck::class);

    Route::get('/repository/enterprise/{repository:name}/{path}', RepositoryController::class)
        ->where('path', '.*')
        ->middleware(EnterpriseLicenceCheck::class);
});
