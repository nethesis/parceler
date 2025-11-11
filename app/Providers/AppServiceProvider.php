<?php

namespace App\Providers;

use App\Logic\LicenceVerification;
use App\Logic\NetifydLicenceRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LicenceVerification::class, function () {
            return new LicenceVerification(config('repositories.endpoints.enterprise'), config('repositories.endpoints.community'));
        });
        $this->app->singleton(NetifydLicenceRepository::class, function () {
            return new NetifydLicenceRepository(config('netifyd.api-key'), config('netifyd.endpoint'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
