<?php

use App\Http\Controllers\NetifyLicenseController;
use App\Http\Middleware\CommunityLicenceCheck;
use App\Http\Middleware\EnterpriseLicenceCheck;
use App\Http\Middleware\ForceBasicAuth;
use Illuminate\Support\Facades\Route;

Route::prefix('/netifyd')->group(function () {
    Route::get('/license', [NetifyLicenseController::class, 'community']);
    Route::middleware(ForceBasicAuth::class)->group(function () {
        Route::get('/enterprise/license', [NetifyLicenseController::class, 'enterprise'])
            ->middleware(EnterpriseLicenceCheck::class);

        Route::get('/community/license', [NetifyLicenseController::class, 'enterprise'])
            ->middleware(CommunityLicenceCheck::class);
    });
});
