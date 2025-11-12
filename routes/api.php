<?php

use App\Http\Controllers\NetifyLicenceController;
use App\Http\Middleware\CommunityLicenceCheck;
use App\Http\Middleware\EnterpriseLicenceCheck;
use App\Http\Middleware\ForceBasicAuth;
use Illuminate\Support\Facades\Route;

Route::prefix('/netifyd')->group(function () {
    Route::get('/licence', [NetifyLicenceController::class, 'community']);
    Route::middleware(ForceBasicAuth::class)->group(function () {
        Route::get('/enterprise/licence', [NetifyLicenceController::class, 'enterprise'])
            ->middleware(EnterpriseLicenceCheck::class);

        Route::get('/community/licence', [NetifyLicenceController::class, 'enterprise'])
            ->middleware(CommunityLicenceCheck::class);
    });
});
