<?php

use App\Http\Controllers\RepositoryController;
use App\Http\Middleware\CommunityLicenceCheck;
use App\Http\Middleware\EnterpriseLicenceCheck;
use App\Http\Middleware\ForceBasicAuth;
use App\Http\Middleware\ReleaseAuth;
use App\Jobs\Release;
use App\Models\Repository;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(ReleaseAuth::class)
    ->post('/repository/{repository:name}/release', function (Repository $repository) {
        Release::dispatch($repository);

        return response()->json(['message' => 'Release job dispatched.']);
    });

Route::middleware(ForceBasicAuth::class)->withoutMiddleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    PreventRequestForgery::class,
])->group(function () {
    Route::get('/repository/community/{repository:name}/{path}', RepositoryController::class)
        ->where('path', '.*')
        ->middleware(CommunityLicenceCheck::class);

    Route::get('/repository/enterprise/{repository:name}/{path}', RepositoryController::class)
        ->where('path', '.*')
        ->middleware(EnterpriseLicenceCheck::class);
});
