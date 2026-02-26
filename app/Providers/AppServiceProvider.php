<?php

namespace App\Providers;

use App\Logic\LicenceVerification;
use App\Logic\NetifydCatalogRepository;
use App\Logic\NetifydLicenseRepository;
use Illuminate\Foundation\Events\DiagnosingHealth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Facades\Nightwatch;

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
        $this->app->singleton(NetifydLicenseRepository::class, function () {
            return new NetifydLicenseRepository(config('netifyd.endpoint'), config('netifyd.api-key'));
        });
        $this->app->singleton(NetifydCatalogRepository::class, function () {
            return new NetifydCatalogRepository(config('netifyd.endpoint'), config('netifyd.api-key'));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (DiagnosingHealth $event) {
            Nightwatch::dontSample();
        });
    }
}
